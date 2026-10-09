<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteName = (string) ($settings['site.name'] ?? 'Painting Services');
        $trackingProviders = collect($trackingProviders ?? []);
        $trackingEventRules = collect($trackingEventRules ?? []);

        $metaProvider = $trackingProviders->firstWhere('provider', 'meta_pixel');
        $metaPixels = collect(data_get($metaProvider, 'config.meta_pixels', []))
            ->filter(fn ($pixel) => (bool) ($pixel['is_active'] ?? true) && preg_match('/^[0-9]{5,30}$/', (string) ($pixel['pixel_id'] ?? '')))
            ->map(fn ($pixel) => (string) $pixel['pixel_id'])
            ->values();
        if ($metaPixels->isEmpty() && preg_match('/^[0-9]{5,30}$/', (string) data_get($metaProvider, 'public_identifier', ''))) {
            $metaPixels = collect([(string) data_get($metaProvider, 'public_identifier')]);
        }
        $metaPixels = $metaPixels->unique()->values();

        $gtmIds = $trackingProviders->where('provider', 'gtm')
            ->pluck('public_identifier')->filter(fn ($id) => is_string($id) && preg_match('/^GTM-[A-Z0-9]{4,20}$/i', $id))->unique()->values();
        $ga4Ids = $trackingProviders->where('provider', 'ga4')
            ->pluck('public_identifier')->filter(fn ($id) => is_string($id) && preg_match('/^G-[A-Z0-9]{4,20}$/i', $id))->unique()->values();

        $marketingConsentDefault = filter_var($settings['consent.marketing_default'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $localTrackingTestOverride = app()->environment(['local', 'testing'])
            && $trackingProviders->contains(fn ($provider) => (bool) data_get($provider, 'test_mode', false));
        $loadMarketingTracking = $marketingConsentDefault || $localTrackingTestOverride;

        $trackingEntity = $contentPage ?? $home ?? null;
        $trackingPageType = isset($contentPage) ? strtolower(class_basename($contentPage)) : 'home';
        $trackingPageName = (string) ($contentPage?->title ?? $contentPage?->name ?? $home?->title ?? $siteName);
        $trackingPageId = $trackingEntity?->id;
    @endphp
    <title>{{ ($contentPage?->title ?? $contentPage?->name ?? $home?->title ?? '') ? (($contentPage?->title ?? $contentPage?->name ?? $home?->title).' | '.$siteName) : $siteName }}</title>
    @if($home?->excerpt)<meta name="description" content="{{ \Illuminate\Support\Str::limit(trim(strip_tags($home->excerpt)), 160) }}">@endif

    {{-- Public-safe tracking bootstrap. Raw admin JavaScript snippets are never executed. --}}
    <script>
        window.dataLayer = window.dataLayer || [];
        window.__websiteTracking = {
            providers: @json($trackingProviders->values()->all()),
            rules: @json($trackingEventRules->values()->all()),
            marketingConsent: @json((bool) $marketingConsentDefault),
            localTestOverride: @json((bool) $localTrackingTestOverride),
            providerScriptsAllowed: @json((bool) $loadMarketingTracking),
            hasGtmContainer: @json((bool) ($loadMarketingTracking && $gtmIds->isNotEmpty())),
            metaPageViewFired: false,
            page: {
                page_type: @json($trackingPageType),
                page_id: @json($trackingPageId),
                page_name: @json($trackingPageName),
                page_url: @json(request()->url())
            }
        };
    </script>

    {{-- Google Tag Manager: a real enabled GTM ID produces gtm.js, gtm.dom and gtm.load lifecycle events. --}}
    @if($loadMarketingTracking && $gtmIds->isNotEmpty())
        @foreach($gtmIds as $gtmId)
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',@json($gtmId));</script>
        @endforeach
    @else
        {{-- DataLayer diagnostics fallback when no real GTM container is configured. --}}
        <script>window.dataLayer.push({'gtm.start': Date.now(), event: 'gtm.js', 'gtm.synthetic': true});</script>
    @endif

    <script>window.dataLayer.push({event: 'page_view', ...window.__websiteTracking.page});</script>

    {{-- Meta Pixel: generated from validated numeric IDs only. --}}
    @if($loadMarketingTracking && $metaPixels->isNotEmpty())
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
            @foreach($metaPixels as $pixelId)fbq('init', @json($pixelId));@endforeach
            fbq('track', 'PageView');
            window.__websiteTracking.metaPageViewFired = true;
        </script>
    @endif

    {{-- GA4: validated Measurement IDs only; no secrets are exposed. --}}
    @if($loadMarketingTracking && $ga4Ids->isNotEmpty())
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ rawurlencode((string) $ga4Ids->first()) }}"></script>
        <script>
            window.gtag = window.gtag || function(){window.dataLayer.push(arguments);};
            gtag('js', new Date());
            @foreach($ga4Ids as $ga4Id)gtag('config', @json($ga4Id));@endforeach
        </script>
    @endif

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
    @if($loadMarketingTracking)
        @foreach($gtmIds as $gtmId)<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ rawurlencode((string) $gtmId) }}" height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>@endforeach
        @foreach($metaPixels as $pixelId)<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ rawurlencode((string) $pixelId) }}&ev=PageView&noscript=1" alt=""></noscript>@endforeach
    @endif
    <a class="skip-link" href="#main-content">Skip to content</a>
    @if($home?->show_header ?? true)@include('website.partials.header')@endif
    <main id="main-content">@yield('content')</main>
    @if($home?->show_footer ?? true)@include('website.partials.footer')@endif
    @include('website.partials.contact-widget')
    <button class="back-top hp-back-top" id="backToTop" type="button" aria-label="Back to top" hidden><i class="bi bi-chevron-up"></i></button>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/tracking.js') }}" defer></script>
    <script src="{{ asset('assets/js/website.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
