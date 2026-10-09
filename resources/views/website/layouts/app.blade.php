<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php($siteName = (string) ($settings['site.name'] ?? 'Painting Services'))
    <title>{{ ($home?->title ? $home->title.' | ' : '') . $siteName }}</title>
    @if($home?->excerpt)<meta name="description" content="{{ \Illuminate\Support\Str::limit(trim(strip_tags($home->excerpt)), 160) }}">@endif
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
    <a class="skip-link" href="#main-content">Skip to content</a>
    @if($home?->show_header ?? true)@include('website.partials.header')@endif
    <main id="main-content">@yield('content')</main>
    @if($home?->show_footer ?? true)@include('website.partials.footer')@endif
    @include('website.partials.contact-widget')
    <button class="back-top hp-back-top" id="backToTop" type="button" aria-label="Back to top" hidden><i class="bi bi-chevron-up"></i></button>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/website.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
