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
            })
            ->values();
    }

    /**
     * Return all rules for enabled providers so the browser can distinguish
     * an explicitly-disabled mapping from a missing mapping. This allows
     * safe built-in defaults only when the admin has not configured a rule.
     */
    public function publicEventRules(): Collection
    {
        return TrackingEventRule::query()
            ->whereHas('provider', fn ($query) => $query->enabled())
            ->with('provider:id,provider,is_enabled')
            ->ordered()
            ->get()
            ->filter(fn (TrackingEventRule $rule) => array_key_exists($rule->internal_event, TrackingEventRule::INTERNAL_EVENTS))
            ->map(function (TrackingEventRule $rule): array {
                return [
                    'provider' => $rule->provider?->provider,
                    'internal_event' => $rule->internal_event,
                    'provider_event' => $rule->provider_event,
                    'parameter_map' => $rule->parameter_map ?? [],
                    'requires_marketing_consent' => $rule->requires_marketing_consent,
                    'is_enabled' => $rule->is_enabled,
                ];
            })
            ->filter(fn (array $rule) => is_string($rule['provider']) && $rule['provider'] !== '')
            ->values();
    }

    public function eventMappings(string $internalEvent): Collection
    {
        if (! array_key_exists($internalEvent, TrackingEventRule::INTERNAL_EVENTS)) {
            return collect();
        }

        return $this->publicEventRules()
            ->where('internal_event', $internalEvent)
            ->where('is_enabled', true)
            ->values();
    }
}
