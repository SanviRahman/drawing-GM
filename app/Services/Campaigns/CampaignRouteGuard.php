<?php

namespace App\Services\Campaigns;

use Illuminate\Validation\ValidationException;

class CampaignRouteGuard
{
    /** @var array<int,string> */
    private array $reservedFirstSegments = [
        'admin', 'api', 'login', 'logout', 'register', 'password', 'storage',
        'vendor', 'build', 'media-management', 'campaign', 'sitemap.xml', 'robots.txt',
    ];

    public function normalize(?string $route): ?string
    {
        if ($route === null || trim($route) === '') {
            return null;
        }

        $path = '/' . ltrim(trim($route), '/');
        $path = preg_replace('#/+#', '/', $path) ?: '/';

        return $path === '/' ? null : rtrim($path, '/');
    }

    public function validate(?string $route): ?string
    {
        $route = $this->normalize($route);

        if ($route === null) {
            return null;
        }

        if (str_contains($route, '?') || str_contains($route, '#')) {
            throw ValidationException::withMessages([
                'custom_route' => 'Custom route must contain only a path, without query strings or fragments.',
            ]);
        }

        $first = strtolower(explode('/', ltrim($route, '/'))[0] ?? '');

        if (in_array($first, $this->reservedFirstSegments, true)) {
            throw ValidationException::withMessages([
                'custom_route' => 'This route prefix is reserved by the application.',
            ]);
        }

        if (! preg_match('#^/[A-Za-z0-9][A-Za-z0-9/_-]*$#', $route)) {
            throw ValidationException::withMessages([
                'custom_route' => 'Use a clean relative path such as /offers/october-paint-sale.',
            ]);
        }

        return $route;
    }
}
