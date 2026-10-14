<?php

namespace App\Services\Website;

use App\Models\Service;

/** Route known service landing pages to their already-approved CMS destinations. */
final class ServiceDestination
{
    public static function url(Service $service): string
    {
        return match ($service->slug) {
            'wall-plastering' => url('/plastering'),
            'wall-hacking' => url('/hacking'),
            'false-ceiling' => url('/false-ceiling'),
            default => route('website.service', ['slug' => $service->slug]),
        };
    }
}
