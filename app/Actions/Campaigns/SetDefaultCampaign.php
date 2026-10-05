<?php

namespace App\Actions\Campaigns;

use App\Models\Campaign;
use App\Models\CampaignSetting;
use App\Services\Audit\AuditLogger;
use App\Services\Campaigns\CampaignCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SetDefaultCampaign
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly CampaignCache $cache,
    ) {}

    public function handle(?Campaign $campaign): CampaignSetting
    {
        if ($campaign && ! $campaign->isPublicEligible()) {
            throw ValidationException::withMessages([
                'default_campaign_id' => 'Only a published, started and non-expired campaign can be the default.',
            ]);
        }

        return DB::transaction(function () use ($campaign): CampaignSetting {
            $settings = CampaignSetting::withTrashed()
                ->whereKey(CampaignSetting::SINGLETON_ID)
                ->lockForUpdate()
                ->first();

            if (! $settings) {
                $settings = new CampaignSetting(['fallback_mode' => 'homepage']);
                $settings->id = CampaignSetting::SINGLETON_ID;
                $settings->save();
            } elseif ($settings->trashed()) {
                $settings->restore();
            }

            $old = $settings->only(['default_campaign_id', 'fallback_mode', 'fallback_page_id']);

            $settings->forceFill([
                'default_campaign_id' => $campaign?->getKey(),
                'updated_by' => auth('admin')->id(),
            ])->save();

            $this->cache->forget($campaign);
            $this->auditLogger->log(
                $campaign ? 'campaign.default_set' : 'campaign.default_cleared',
                $settings,
                $old,
                $settings->fresh()->only(['default_campaign_id', 'fallback_mode', 'fallback_page_id'])
            );

            return $settings->fresh();
        });
    }
}
