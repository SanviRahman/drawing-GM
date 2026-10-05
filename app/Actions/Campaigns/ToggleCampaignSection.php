<?php

namespace App\Actions\Campaigns;

use App\Models\CampaignSection;
use App\Services\Audit\AuditLogger;
use App\Services\Campaigns\CampaignCache;

class ToggleCampaignSection
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly CampaignCache $cache,
    ) {}

    public function handle(CampaignSection $section): CampaignSection
    {
        $old = ['is_enabled' => $section->is_enabled];
        $section->update(['is_enabled' => ! $section->is_enabled]);

        $this->cache->forget($section->campaign);
        $this->auditLogger->log('campaign_section.toggled', $section, $old, ['is_enabled' => $section->fresh()->is_enabled]);

        return $section->fresh();
    }
}
