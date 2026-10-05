<?php

namespace App\Actions\Campaigns;

use App\Models\Campaign;
use App\Services\Audit\AuditLogger;
use App\Services\Campaigns\CampaignCache;
use Illuminate\Validation\ValidationException;

class PublishCampaign
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly CampaignCache $cache,
    ) {}

    public function handle(Campaign $campaign): Campaign
    {
        if ($campaign->starts_at && $campaign->ends_at && $campaign->starts_at->gte($campaign->ends_at)) {
            throw ValidationException::withMessages([
                'starts_at' => 'Campaign start time must be before the end time.',
            ]);
        }

        $old = $campaign->only(['status', 'published_at']);

        $campaign->forceFill([
            'status' => 'published',
            'published_at' => $campaign->published_at ?? now(),
            'updated_by' => auth('admin')->id(),
        ])->save();

        $this->cache->forget($campaign);
        $this->auditLogger->log('campaign.published', $campaign, $old, $campaign->only(['status', 'published_at']));

        return $campaign->fresh();
    }
}
