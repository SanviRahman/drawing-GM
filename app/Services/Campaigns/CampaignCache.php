<?php

namespace App\Services\Campaigns;

use App\Models\Campaign;
use Illuminate\Support\Facades\Cache;

class CampaignCache
{
    public function forget(?Campaign $campaign = null): void
    {
        Cache::forget('campaign:default:resolved');

        if ($campaign) {
            Cache::forget('campaign:' . $campaign->getKey());
            Cache::forget('campaign:slug:' . $campaign->slug);
        }
    }
}
