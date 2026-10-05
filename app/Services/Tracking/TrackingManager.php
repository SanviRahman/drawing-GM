<?php

namespace App\Services\Tracking;

use App\Models\TrackingEventRule;
use App\Models\TrackingProvider;
use Illuminate\Support\Collection;

class TrackingManager
{
    /**
     * Public-safe provider configuration.
     *
     * Secrets, encrypted raw config and stored script snippets are never exposed.
     */
    public function enabledPublicProviders(): Collection
    {
        return TrackingProvider::query()
            ->enabled()
            ->ordered()
            ->get()
            ->map(function (TrackingProvider $provider): array {
                return [
                    'provider' => $provider->provider,
                    'public_identifier' => $provider->public_identifier,
                    'test_mode' => $provider->test_mode,
                    'config' => $provider->publicConfig(),
                ];
            });
    }

    public function eventMappings(string $internalEvent): Collection
    {
        if (! array_key_exists($internalEvent, TrackingEventRule::INTERNAL_EVENTS)) {
            return collect();
        }

        return TrackingEventRule::query()
            ->enabled()
            ->where('internal_event', $internalEvent)
            ->whereHas('provider', fn ($query) => $query->enabled())
            ->with('provider')
            ->get()
            ->map(function (TrackingEventRule $rule): array {
                return [
                    'provider' => $rule->provider?->provider,
                    'provider_event' => $rule->provider_event,
                    'parameter_map' => $rule->parameter_map ?? [],
                    'requires_marketing_consent' => $rule->requires_marketing_consent,
                ];
            });
    }
}
