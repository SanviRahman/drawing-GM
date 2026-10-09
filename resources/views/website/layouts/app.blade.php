<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteName = (string) ($settings['site.name'] ?? 'Painting Services');
        $trackingProviders = $trackingProviders ?? collect();
        $metaPixels = $trackingProviders->where('provider', 'meta_pixel')->flatMap(fn ($provider) => data_get($provider, 'config.meta_pixels', []))->values();
    @endphp
    <title>{{ ($contentPage?->title ?? $contentPage?->name ?? $home?->title ?? '') ? (($contentPage?->title ?? $contentPage?->name ?? $home?->title).' | '.$siteName) : $siteName }}</title>
    @if($home?->excerpt)
        <meta name="description" content="{{ \Illuminate\Support\Str::limit(trim(strip_tags($home->excerpt)), 160) }}">
    @endif
    {{-- DataLayer + Tracking Providers --}}
    <script>
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            event: 'page_view'
        });
    </script>
    @if($metaPixels->isNotEmpty())
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
            @foreach($metaPixels as $pixel)
                fbq('init', @json($pixel['pixel_id'] ?? ''));
            @endforeach
            fbq('track', 'PageView');
        </script>
    @endif
    <script>
        window.trackEvent = function(eventName, data = {}) {
            window.dataLayer.push({
                event: eventName,
                ...data
            });
        };
    </script>
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/rubik/400.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/rubik/500.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/rubik/600.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/rubik/700.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/website.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/contact-widget.css') }}">
    @stack('styles')
</head>
<body id="top" class="website-body">
    {{-- Meta Pixel Noscript --}}
    @foreach($metaPixels as $pixel)
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ rawurlencode($pixel['pixel_id'] ?? '') }}&ev=PageView&noscript=1" alt=""></noscript>
    @endforeach
    <a class="skip-link" href="#main-content">Skip to content</a>
    @if($home?->show_header ?? true)
        @include('website.partials.header')
    @endif
    <main id="main-content">@yield('content')</main>
    @if($home?->show_footer ?? true)
        @include('website.partials.footer')
    @endif
    @include('website.partials.contact-widget')
    <button class="back-top hp-back-top" id="backToTop" type="button" aria-label="Back to top" hidden><i class="bi bi-chevron-up"></i></button>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/website.js') }}" defer></script>
    @stack('scripts')
</body>
</html>