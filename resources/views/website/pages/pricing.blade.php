@extends('website.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pricing-services.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/pricing-services.js') }}" defer></script>
@endpush

@section('content')
<div class="home-page ps-page ps-pricing-page">
    {{-- CMS-backed calculator is the main visible heading, matching the Home reference layout. --}}
    {{-- Exact Home calculator Blade and existing live Admin price calculation script. --}}
    @include('website.pages.home-sections.calculator')

    @if($packages->isNotEmpty())
    <section class="hp-section ps-packages" id="pricing-packages" aria-labelledby="psPackagesHeading">
        <div class="container">
            <div class="hp-section-heading text-center">
                <span class="hp-eyebrow">PUBLISHED OPTIONS</span>
                <h2 id="psPackagesHeading">Compare Available Packages</h2>
                <p>Rates and package details come directly from active items in your CMS. Confirm quantities and site conditions before booking.</p>
            </div>
            <div class="row g-4">
                @foreach($packages as $package)
                    <div class="col-md-6 col-xl-4">
                        <article class="ps-package-card h-100">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <span class="ps-package-category">{{ $package->service?->name ?? 'General Services' }}</span>
                                @if($package->badge)<span class="ps-package-badge">{{ $package->badge }}</span>@endif
                            </div>
                            <h3>{{ $package->name }}</h3>
                            @if($package->subtitle)<p>{{ $package->subtitle }}</p>@endif
                            <ul class="ps-package-items list-unstyled">
                                @foreach($package->items as $item)
                                    <li><span>{{ $item->label }}</span><strong>{{ $item->display_price }}@if($item->unit && $item->price_type !== 'call') / {{ $item->unit }}@endif</strong></li>
                                @endforeach
                            </ul>
                            <a href="#quote-form" data-request-service="{{ $package->service_id ?: '' }}" class="btn btn-brand w-100 mt-auto">Request a Detailed Quote <i class="bi bi-arrow-right ms-1"></i></a>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @include('website.pages.home-sections.reviews')
    @include('website.pages.home-sections.videos')
    @include('website.pages.home-sections.chat-proof')
    @include('website.pages.home-sections.whatsapp-desks')
    @include('website.pages.home-sections.faqs')
    @include('website.partials.quote-form')
</div>
@endsection
