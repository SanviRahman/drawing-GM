<?php

namespace App\Actions\Campaigns;

use App\Models\Campaign;
use App\Models\CampaignSection;
use App\Services\Audit\AuditLogger;
use App\Services\Campaigns\CampaignCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReorderCampaignSections
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly CampaignCache $cache,
    ) {}

    public function handle(Campaign $campaign, array $rows): void
    {
        $ids = collect($rows)->pluck('id')->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $owned = CampaignSection::query()->where('campaign_id', $campaign->id)->whereIn('id', $ids)->count();

        if ($owned !== $ids->count()) {
            throw ValidationException::withMessages([
                'order' => 'One or more sections do not belong to the selected campaign.',
            ]);
        }

        $before = CampaignSection::query()
            ->where('campaign_id', $campaign->id)
            ->whereIn('id', $ids)
            ->pluck('sort_order', 'id')
            ->all();

        DB::transaction(function () use ($rows): void {
            foreach ($rows as $row) {
                CampaignSection::query()
                    ->whereKey((int) $row['id'])
                    ->update(['sort_order' => (int) $row['sort_order']]);
            }
        });

        $this->cache->forget($campaign);
        $this->auditLogger->log('campaign.sections_reordered', $campaign, ['order' => $before], ['order' => $rows]);
    }
}
