@extends('website.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/content-media.css') }}">
@endpush

@section('content')
    @php
        $title = $contentPage->title ?? $contentPage->name;
        $description = trim(strip_tags((string) ($contentPage->excerpt ?? $contentPage->summary ?? '')));
        $bodyText = trim(strip_tags((string) ($contentPage->content ?? '')));
        $currentSlug = $contentPage->slug;
        $isServiceDetail = $contentPage instanceof \App\Models\Service;
        $serviceHeroImage = $isServiceDetail ? ($contentPage->getFirstMediaUrl('hero_desktop') ?: $contentPage->getFirstMediaUrl('gallery')) : null;
        $serviceMobileImage = $isServiceDetail ? $contentPage->getFirstMediaUrl('hero_mobile') : null;
        $serviceGallery = $isServiceDetail ? $contentPage->getMedia('gallery')->filter(fn ($media) => $media->getUrl() !== $serviceHeroImage)->values() : collect();
        $contactWhatsapps = $currentSlug === 'contact' ? collect($contacts ?? [])->where('type', 'whatsapp')->values() : collect();
    @endphp
    <section class="cms-content-hero {{ $isServiceDetail ? 'cms-service-hero' : '' }}">
        <div class="container {{ $isServiceDetail && ($serviceHeroImage || $serviceMobileImage) ? 'cms-service-hero-grid' : '' }}">
            <div class="cms-hero-copy">
            <p class="cms-content-eyebrow">PAINTING & HOME SERVICES</p>
            <h1>{{ $title }}</h1>
            @if($description !== '')<p>{{ $description }}</p>@endif
            @if($currentSlug === 'contact')
                <a href="#hp-whatsapp-desks" class="btn btn-brand">Contact on WhatsApp <i class="bi bi-whatsapp ms-1"></i></a>
                <a href="#quote-form" class="btn btn-outline-brand ms-2">Send an Enquiry</a>
            @else
                <a href="#quote-form" class="btn btn-brand">Request a Quote <i class="bi bi-arrow-up-right ms-1"></i></a>
            @endif
            </div>
            @if($isServiceDetail && ($serviceHeroImage || $serviceMobileImage))
                <figure class="cms-service-hero-media">
                    <picture>
                        @if($serviceMobileImage)<source media="(max-width: 767px)" srcset="{{ $serviceMobileImage }}">@endif
                        <img src="{{ $serviceHeroImage ?: $serviceMobileImage }}" alt="{{ $title }} service" fetchpriority="high">
                    </picture>
                </figure>
            @endif
        </div>
    </section>

    @if($bodyText !== '')
        <section class="cms-content-section"><div class="container cms-content-narrow">
            <h2>Service overview</h2><p>{{ $bodyText }}</p>
        </div></section>
    @endif

    @if($isServiceDetail && $serviceGallery->isNotEmpty())
        <section class="cms-content-section cms-service-gallery-section"><div class="container">
            <h2 class="mb-4">{{ $title }} Gallery</h2>
            <div class="cms-service-gallery-grid">
                @foreach($serviceGallery as $photo)
                    <figure class="cms-service-gallery-item"><img src="{{ $photo->getUrl() }}" alt="{{ $photo->name ?: $title.' project image' }}" loading="lazy"></figure>
                @endforeach
            </div>
        </div></section>
    @endif

    @foreach(($customPageSections ?? collect()) as $customSection)
        @include('website.pages.home-sections.custom', ['section' => $customSection])
    @endforeach

    @if($currentSlug === 'blog' && $articles->isNotEmpty())
        <section class="cms-content-section"><div class="container">
            <h2 class="mb-4">Latest planning guides</h2>
            <div class="row g-4">
                @foreach($articles as $article)
                    @php
                        $articleImage = $article->featured_image_url ?: $article->getFirstMediaUrl('content_images') ?: ($blogFallbackImage ?? null);
                    @endphp
                    <div class="col-md-6 col-lg-4"><article class="cms-information-card cms-blog-card h-100">
                        <a class="cms-blog-card-image" href="{{ route('website.article', $article->slug) }}" aria-label="Read {{ $article->title }}">
                            @if($articleImage)
                                <img src="{{ $articleImage }}" alt="{{ $article->title }}" loading="lazy">
                            @else
                                <span class="cms-blog-image-placeholder"><i class="bi bi-image" aria-hidden="true"></i><span>Painting & Home Services Guide</span></span>
                            @endif
                        </a>
                        <div class="cms-blog-card-copy">
                            <h3><a href="{{ route('website.article', $article->slug) }}">{{ $article->title }}</a></h3>
                            @if($article->excerpt)<p>{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $article->excerpt)), 180) }}</p>@endif
                            <a class="cms-blog-read-more" href="{{ route('website.article', $article->slug) }}">Read guide <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </article></div>
                @endforeach
            </div>
        </div></section>
    @endif

    @if(in_array($currentSlug, ['pricing', 'cost-calculator'], true) && $packages->isNotEmpty())
        <section class="cms-content-section"><div class="container">
            <h2 class="mb-4">Available quotation options</h2>
            <div class="row g-4">
                @foreach($packages as $package)
                    <div class="col-md-6 col-lg-4"><article class="cms-information-card h-100">
                        <h3>{{ $package->name }}</h3>
                        @if($package->subtitle)<p>{{ $package->subtitle }}</p>@endif
                        <ul class="list-unstyled">
                            @foreach($package->items as $item)
                                <li class="d-flex justify-content-between gap-2 border-top py-2">
                                    <span>{{ $item->label }}</span><strong>{{ $item->display_price }}</strong>
                                </li>
                            @endforeach
                        </ul>
                    </article></div>
                @endforeach
            </div>
            <p class="mt-3 text-muted">Published guide prices may differ from a final quotation based on site conditions and scope.</p>
        </div></section>
    @endif

    @if($currentSlug === 'gallery' && $galleries->isNotEmpty())
        <section class="cms-content-section"><div class="container">
            <h2 class="mb-4">Project gallery</h2><div class="row g-4">
                @foreach($galleries as $gallery)
                    @foreach($gallery->items as $galleryItem)
                        @php($image = $galleryItem->getFirstMediaUrl('image') ?: $galleryItem->getFirstMediaUrl('after'))
                        @if($image)
                            <div class="col-md-6 col-lg-4"><figure class="cms-information-card h-100">
                                <img src="{{ $image }}" alt="{{ $galleryItem->title ?: 'Project media' }}" loading="lazy" class="img-fluid rounded-3 mb-3">
                                <figcaption><strong>{{ $galleryItem->title ?: 'Project image' }}</strong></figcaption>
                            </figure></div>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div></section>
    @endif

    @if($currentSlug === 'contact')
        {{-- Reuse the exact Home WhatsApp component with every active WhatsApp contact. --}}
        <div class="home-page cms-contact-components">
            @include('website.pages.home-sections.whatsapp-desks', ['whatsapps' => $contactWhatsapps])
        </div>
    @endif

    @if($faqs->isNotEmpty())
        <section class="cms-content-section cms-content-faq"><div class="container cms-content-narrow">
            <h2 class="mb-4">Frequently Asked Questions</h2>
            <div class="accordion" id="contentPageFaqs">
                @foreach($faqs as $faq)
                    <div class="accordion-item mb-2">
                        <h3 class="accordion-header"><button class="accordion-button collapsed" type="button"
                            data-bs-toggle="collapse" data-bs-target="#contentFaq{{ $faq->id }}"
                            aria-expanded="false" aria-controls="contentFaq{{ $faq->id }}">{{ $faq->question }}</button></h3>
                        <div id="contentFaq{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#contentPageFaqs">
                            <div class="accordion-body">{{ trim(strip_tags((string) $faq->answer)) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div></section>
    @endif

    {{-- Shared enquiry form; the Contact page reuses the same form and validation. --}}
    @include('website.partials.quote-form')
@endsection
