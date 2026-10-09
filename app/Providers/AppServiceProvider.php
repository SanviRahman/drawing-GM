<?php

namespace App\Providers;

use App\Services\Tracking\TrackingManager;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Keep public tracking configuration available on every website page
        // without exposing encrypted secrets or raw admin script snippets.
        View::composer('website.layouts.app', function ($view): void {
            $tracking = app(TrackingManager::class);

            $view->with([
                'trackingProviders' => $tracking->enabledPublicProviders(),
                'trackingEventRules' => $tracking->publicEventRules(),
            ]);
        });
    }
}
