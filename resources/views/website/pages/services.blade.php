@extends('website.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pricing-services.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/pricing-services.js') }}" defer></script>
@endpush

@section('content')
<div class="home-page ps-page ps-services-page">
    <section class="ps-directory-hero">
        <div class="container">
            <span class="hp-eyebrow">WHAT WE OFFER</span>
            <h1>{{ $contentPage?->title ?: 'Our Painting & Home Services' }}</h1>
            <p>{{ $contentPage?->excerpt ?: 'Explore our currently published services and choose the right team for your project.' }}</p>
            <a href="#ps-services-list" class="btn btn-brand">Explore Services <i class="bi bi-arrow-down-right ms-1"></i></a>
        </div>
    </section>

    <section class="hp-section ps-directory" id="ps-services-list" aria-labelledby="psServicesHeading">
        <div class="container">
            <div class="hp-section-heading text-center">
                <span class="hp-eyebrow">ACTIVE SERVICES</span>
                <h2 id="psServicesHeading">Services for Your Space</h2>
                <p>This list updates automatically when services are published or unpublished in Admin → Services.</p>
            </div>
            @if($catalogServices->isNotEmpty())
                <div class="row g-4">
                    @foreach($catalogServices as $service)
                        @php($image = $service->getFirstMediaUrl('hero_desktop') ?: $service->getFirstMediaUrl('gallery'))
                        <div class="col-md-6 col-xl-4">
                            <article class="ps-service-card h-100">
                                <a href="{{ \App\Services\Website\ServiceDestination::url($service) }}" class="ps-service-photo" aria-label="View {{ $service->name }}">
                                    @if($image)
                                        <img src="{{ $image }}" loading="lazy" alt="{{ $service->name }} service">
                                    @else
                                        <span class="ps-service-no-photo"><i class="bi bi-house-gear" aria-hidden="true"></i></span>
                                    @endif
                                    <span class="ps-service-image-arrow"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                                </a>
                                <div class="ps-service-copy">
                                    <span class="ps-service-kicker">OUR SERVICES</span>
                                    <h3><a href="{{ \App\Services\Website\ServiceDestination::url($service) }}">{{ $service->name }}</a></h3>
                                    @if($service->summary)<p>{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $service->summary)), 180) }}</p>@endif
                                    <div class="ps-service-actions">
                                        <a href="{{ \App\Services\Website\ServiceDestination::url($service) }}" class="ps-text-link">View Details <i class="bi bi-arrow-right ms-1"></i></a>
                                        <a href="#quote-form" data-request-service="{{ $service->id }}" class="ps-text-link">Request Quote</a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="hp-editorial-empty">There are no published services yet. Publish a service from Admin → Services to display it here.</div>
            @endif
        </div>
    </section>

    <section class="ps-directory-cta hp-section">
        <div class="container text-center">
            <span class="hp-eyebrow">LET US HELP</span>
            <h2>Not Sure Which Service You Need?</h2>
            <p>Describe your project and our team can help you identify the next step.</p>
            <a class="btn btn-brand" href="#quote-form">Request a Quote <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </section>
    @include('website.pages.home-sections.reviews')
    @include('website.pages.home-sections.videos')
    @include('website.pages.home-sections.chat-proof')
    @include('website.pages.home-sections.whatsapp-desks')
    @include('website.pages.home-sections.faqs')
    @include('website.partials.quote-form')
</div>
@endsection
