@php
    $siteName = (string) ($settings['site.name'] ?? 'Painting Services');
    $primaryMenu = $menus['header-primary'] ?? collect();
    $topMenu = $menus['header-top'] ?? collect();
    $announcement = trim(strip_tags((string) ($settings['header.announcement'] ?? '')));
    $phoneChannel = $contacts->firstWhere('type', 'phone');
    $reviewRatings = $testimonials->filter(fn ($t) => $t->rating !== null && $t->type !== 'whatsapp_screenshot');
    $averageRating = $reviewRatings->isNotEmpty() ? round($reviewRatings->avg('rating'), 1) : null;
    $stickyHeader = (bool) ($settings['header.sticky'] ?? false);
@endphp
<header class="site-header {{ $stickyHeader ? 'site-header-sticky' : '' }}">
    @if($announcement !== '' || $topMenu->isNotEmpty() || $phoneChannel || $averageRating)
        <div class="site-top-strip"><div class="container d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="site-top-intro"><i class="bi bi-shield-check"></i><span>{{ $announcement ?: $siteName }}</span></div>
            <div class="site-top-detail">
                @if($averageRating)
                    <span class="site-top-rating"><i class="bi bi-star-fill"></i> {{ number_format($averageRating, 1) }}/5 from {{ $reviewRatings->count() }} published {{ \Illuminate\Support\Str::plural('review', $reviewRatings->count()) }}</span>
                @endif
                @if($phoneChannel)
                    @php($phoneDigits = preg_replace('/[^0-9+]/', '', (string) $phoneChannel->value))
                    <a href="tel:{{ $phoneDigits }}" class="site-top-link"><i class="bi bi-telephone-fill"></i> {{ $phoneChannel->display_value ?: $phoneChannel->value }}</a>
                @endif
                @foreach($topMenu->take(3) as $topItem)
                    <a class="site-top-link" href="{{ $topItem->url }}" target="{{ $topItem->target }}" @if($topItem->target === '_blank') rel="noopener noreferrer" @endif>{{ $topItem->label }}</a>
                @endforeach
            </div>
        </div></div>
    @endif
    <nav class="navbar navbar-expand-xl site-navbar" aria-label="Main navigation"><div class="container">
        <a class="navbar-brand site-brand" href="{{ route('website.home') }}">
            @if($logo)<img src="{{ $logo }}" alt="{{ $siteName }}" class="site-logo">
            @else<span class="site-brand-icon"><i class="bi bi-house-fill"></i></span><span class="site-brand-name">{{ $siteName }}</span>@endif
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle menu"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="mainNavbar"><ul class="navbar-nav ms-auto align-items-xl-center gap-xl-2">
            @forelse($primaryMenu as $item)
                <li class="nav-item {{ $item->children->isNotEmpty() ? 'dropdown' : '' }}">
                    @if($item->children->isNotEmpty())
                        <a href="#" class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ $item->label }}</a>
                        <ul class="dropdown-menu">@foreach($item->children as $child)<li><a class="dropdown-item" href="{{ $child->url }}" target="{{ $child->target }}" @if($child->target === '_blank') rel="noopener noreferrer" @endif>{{ $child->label }}</a></li>@endforeach</ul>
                    @else
                        <a class="nav-link @if(request()->url() === $item->url) active @endif" href="{{ $item->url }}" target="{{ $item->target }}" @if($item->target === '_blank') rel="noopener noreferrer" @endif>{{ $item->label }}</a>
                    @endif
                </li>
            @empty
                <li class="nav-item"><a class="nav-link active" href="{{ route('website.home') }}">Home</a></li>
            @endforelse
        </ul><a href="#quote-form" class="btn btn-brand site-nav-cta ms-xl-4 mt-3 mt-xl-0">Get Free Quote <i class="bi bi-arrow-right"></i></a></div>
    </div></nav>
</header>
