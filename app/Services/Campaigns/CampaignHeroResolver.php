<?php

namespace App\Services\Campaigns;

use App\Models\Campaign;
use App\Models\SiteSetting;

class CampaignHeroResolver
{
    public function resolve(Campaign $campaign): array
    {
        $config = $campaign->hero_config ?? [];
        $embedProvider = data_get($config, 'embed.provider');
        $embedUrl = data_get($config, 'embed.url');

        if (in_array($embedProvider, ['youtube', 'vimeo'], true) && is_string($embedUrl) && filter_var($embedUrl, FILTER_VALIDATE_URL)) {
            return [
                'type' => 'embed',
                'provider' => $embedProvider,
                'url' => $embedUrl,
                'config' => $this->playback($config),
            ];
        }

        $images = $campaign->getMedia(Campaign::HERO_IMAGES);
        if ($images->isNotEmpty()) {
            return [
                'type' => 'images',
                'items' => $images->map(fn ($media) => [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'alt' => $media->getCustomProperty('alt', $campaign->title),
                ])->values()->all(),
                'config' => $this->playback($config),
            ];
        }

        $video = $campaign->getFirstMedia(Campaign::HERO_VIDEO);
        if ($video) {
            return [
                'type' => 'video',
                'url' => $video->getUrl(),
                'poster' => $campaign->getFirstMediaUrl(Campaign::HERO_VIDEO_POSTER) ?: null,
                'config' => $this->playback($config),
            ];
        }

        $social = $campaign->getFirstMediaUrl(Campaign::SOCIAL_IMAGE);
        if ($social !== '') {
            return ['type' => 'image', 'url' => $social, 'config' => $this->playback($config)];
        }

        $fallbackOwner = SiteSetting::query()->whereHas('media', fn ($q) => $q->where('collection_name', 'default_hero'))->first();
        $fallback = $fallbackOwner?->getFirstMediaUrl('default_hero');

        return [
            'type' => $fallback ? 'image' : 'none',
            'url' => $fallback ?: null,
            'config' => $this->playback($config),
        ];
    }

    private function playback(array $config): array
    {
        return [
            'autoplay' => (bool) data_get($config, 'video.autoplay', false),
            'muted' => (bool) data_get($config, 'video.muted', true),
            'controls' => (bool) data_get($config, 'video.controls', true),
            'loop' => (bool) data_get($config, 'video.loop', false),
            'playsinline' => true,
        ];
    }
}
