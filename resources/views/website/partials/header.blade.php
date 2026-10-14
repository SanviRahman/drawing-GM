<?php
    $siteName = (string) ($settings['site.name'] ?? 'Painting Services');
    $primaryMenu = $menus['header-primary'] ?? collect();
    $topMenu = $menus['header-top'] ?? collect();
    $announcement = trim(strip_tags((string) ($settings['header.announcement'] ?? '')));
    $phoneChannel = $contacts->firstWhere('type', 'phone');
    $reviewRatings = $testimonials->filter(fn ($t) => $t->rating !== null && $t->type !== 'whatsapp_screenshot');
    $averageRating = $reviewRatings->isNotEmpty() ? round($reviewRatings->avg('rating'), 1) : null;
    $stickyHeader = (bool) ($settings['header.sticky'] ?? false);
    $publishedServiceMenu = collect($navServices ?? [])->values();
    $navigationEntries = [];
    $servicesAdded = false;
    $pricingAdded = false;
    $primaryEntries = collect($primaryMenu);
    if ($primaryEntries->isEmpty()) {
        $navigationEntries[] = ['kind' => 'home'];
        $navigationEntries[] = ['kind' => 'services'];
        $servicesAdded = true;
    }
    foreach ($primaryEntries as $menuItem) {
        $menuLabel = strtolower(trim((string) $menuItem->label));
        if (in_array($menuLabel, ['services', 'cost calculator', 'cost calculation'], true)) {
            continue;
        }
        if ($menuLabel === 'pricing') {
            if (! $pricingAdded) {
                $navigationEntries[] = ['kind' => 'pricing', 'item' => $menuItem];
                $pricingAdded = true;
            }
            continue;
        }
        $navigationEntries[] = ['kind' => 'cms', 'item' => $menuItem];
        if ($menuLabel === 'home' && ! $servicesAdded) {
            $navigationEntries[] = ['kind' => 'services'];
            $servicesAdded = true;
        }
    }
    if (! $servicesAdded) {
        array_unshift($navigationEntries, ['kind' => 'services']);
    }
    if (! $pricingAdded) {
        $navigationEntries[] = ['kind' => 'pricing'];
    }
?>
<header class="site-header {{ $stickyHeader ? 'site-header-sticky' : '' }}">
    <?php if ($announcement !== '' || $topMenu->isNotEmpty() || $phoneChannel || $averageRating): ?>
        <div class="site-top-strip">
            <div class="container d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="site-top-intro"><i class="bi bi-shield-check"></i><span>{{ $announcement ?: $siteName }}</span></div>
                <div class="site-top-detail">
                    <?php if ($averageRating): ?>
                        <span class="site-top-rating"><i class="bi bi-star-fill"></i> {{ number_format($averageRating, 1) }}/5 from {{ $reviewRatings->count() }} published {{ \Illuminate\Support\Str::plural('review', $reviewRatings->count()) }}</span>
                    <?php endif; ?>
                    <?php if ($phoneChannel): ?>
                        <?php $phoneDigits = preg_replace('/[^0-9+]/', '', (string) $phoneChannel->value); ?>
                        <a href="tel:{{ $phoneDigits }}" class="site-top-link"><i class="bi bi-telephone-fill"></i> {{ $phoneChannel->display_value ?: $phoneChannel->value }}</a>
                    <?php endif; ?>
                    <?php foreach ($topMenu->take(3) as $topItem): ?>
                        <a class="site-top-link" href="{{ $topItem->url }}" target="{{ $topItem->target }}" rel="{{ $topItem->target === '_blank' ? 'noopener noreferrer' : '' }}">{{ $topItem->label }}</a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <nav class="navbar navbar-expand-xl site-navbar" aria-label="Main navigation">
        <div class="container">
            <a class="navbar-brand site-brand" href="{{ route('website.home') }}">
                <?php if ($logo): ?>
                    <img src="{{ $logo }}" alt="{{ $siteName }}" class="site-logo">
                <?php else: ?>
                    <span class="site-brand-icon"><i class="bi bi-house-fill"></i></span><span class="site-brand-name">{{ $siteName }}</span>
                <?php endif; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle menu"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto align-items-xl-center gap-xl-1">
                    <?php foreach ($navigationEntries as $navEntry): ?>
                        <?php if ($navEntry['kind'] === 'home'): ?>
                            <li class="nav-item"><a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ route('website.home') }}">Home</a></li>
                        <?php elseif ($navEntry['kind'] === 'services'): ?>
                            <li class="nav-item dropdown ps-nav-dropdown">
                                <button type="button" class="nav-link dropdown-toggle {{ request()->is('services', 'services/*') ? 'active' : '' }}" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Services menu">Services</button>
                                <ul class="dropdown-menu ps-services-menu">
                                    <li><a class="dropdown-item fw-semibold" href="{{ route('website.services') }}">View All Services <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a></li>
                                    <?php foreach ($publishedServiceMenu as $navService): ?>
                                        <li><a class="dropdown-item" href="{{ \App\Services\Website\ServiceDestination::url($navService) }}">{{ $navService->name }}</a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php elseif ($navEntry['kind'] === 'pricing'): ?>
                            <li class="nav-item dropdown ps-nav-dropdown">
                                <button type="button" class="nav-link dropdown-toggle {{ request()->is('pricing', 'cost-calculator') ? 'active' : '' }}" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Pricing menu">Pricing</button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('website.pricing') }}"><i class="bi bi-tags me-2" aria-hidden="true"></i>Pricing Overview</a></li>
                                    <li><a class="dropdown-item" href="{{ route('website.pricing') }}#hp-calculator"><i class="bi bi-calculator me-2" aria-hidden="true"></i>Cost Calculator</a></li>
                                    <?php if (isset($navEntry['item'])): ?>
                                        <?php foreach (collect($navEntry['item']->children ?? []) as $child): ?>
                                            <?php
                                                $childLabel = strtolower(trim((string) $child->label));
                                                $isDefaultPricingLink = in_array($childLabel, ['pricing', 'pricing overview', 'cost calculator', 'cost calculation'], true);
                                            ?>
                                            <?php if (! $isDefaultPricingLink): ?>
                                                <li><a class="dropdown-item" href="{{ $child->url }}" target="{{ $child->target }}" rel="{{ $child->target === '_blank' ? 'noopener noreferrer' : '' }}">{{ $child->label }}</a></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php else: ?>
                            <?php
                                $item = $navEntry['item'];
                                $menuChildren = collect($item->children ?? []);
                            ?>
                            <?php if ($menuChildren->isNotEmpty()): ?>
                                <li class="nav-item dropdown">
                                    <button type="button" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">{{ $item->label }}</button>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($menuChildren as $child): ?>
                                            <li><a class="dropdown-item" href="{{ $child->url }}" target="{{ $child->target }}" rel="{{ $child->target === '_blank' ? 'noopener noreferrer' : '' }}">{{ $child->label }}</a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php else: ?>
                                <li class="nav-item"><a class="nav-link {{ request()->url() === $item->url ? 'active' : '' }}" href="{{ $item->url }}" target="{{ $item->target }}" rel="{{ $item->target === '_blank' ? 'noopener noreferrer' : '' }}">{{ $item->label }}</a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <a href="#quote-form" class="btn btn-brand site-nav-cta ms-xl-3 mt-3 mt-xl-0">Get Free Quote <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </nav>
</header>
