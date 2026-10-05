<?php

namespace App\Services\Campaigns;

use App\Models\Campaign;
use App\Models\CampaignSetting;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;

class ResolveDefaultCampaign
{
    public function resolve(): array
    {
        return Cache::remember('campaign:default:resolved', now()->addMinutes(10), function (): array {
            $settings = CampaignSetting::singleton();

            $campaign = $settings->default_campaign_id
                ? Campaign::publicEligible()->find($settings->default_campaign_id)
                : null;

            if ($campaign) {
                return ['mode' => 'campaign', 'campaign' => $campaign, 'page' => null];
            }

            if ($settings->fallback_mode === 'page' && $settings->fallback_page_id) {
                return [
                    'mode' => 'page',
                    'campaign' => null,
                    'page' => Page::published()->find($settings->fallback_page_id),
                ];
            }

            if ($settings->fallback_mode === 'homepage') {
                return [
                    'mode' => 'homepage',
                    'campaign' => null,
                    'page' => Page::published()->homepage()->first(),
                ];
            }

            return ['mode' => 'none', 'campaign' => null, 'page' => null];
        });
    }

    public function forget(): void
    {
        Cache::forget('campaign:default:resolved');
    }
}
