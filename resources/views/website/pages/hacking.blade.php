@extends('website.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/hacking.css') }}">
@endpush

@section('content')
@php
    $hackByKey = $hackingSections->keyBy(fn ($section) => $section->sectionDefinition?->key);
    $heroSection = $hackByKey->get('hero');
    $benefitSection = $hackByKey->get('benefit_grid');
    $scopeSection = $hackByKey->get('rich_text');
    $relatedSection = $hackByKey->get('service_carousel');
    $videoSection = $hackByKey->get('video_gallery');
    $reviewSection = $hackByKey->get('testimonials');
    $gallerySection = $hackByKey->get('before_after') ?? $hackByKey->get('gallery');
    $chatSection = $hackByKey->get('whatsapp_reviews');
    $faqSection = $hackByKey->get('faq');
    $pricingSection = $hackByKey->get('pricing');

    // This page uses ONLY its own CMS Page/Service Hero, never Home or Plastering banners.
    $hackHeroDesktop = $contentPage->getFirstMediaUrl('hero_desktop')
        ?: $hackingService?->getFirstMediaUrl('hero_desktop')
        ?: collect($heroSection?->mediaItems ?? [])->first(fn ($m) => $m->media && $m->media->isImage() && $m->media->isPickerSafe())?->media?->getUrl();
    $hackHeroMobile = $contentPage->getFirstMediaUrl('hero_mobile')
        ?: $hackingService?->getFirstMediaUrl('hero_mobile');
    $hackHeroTitle = trim((string) ($heroSection?->heading ?: $contentPage->heroValue('heading', '') ?: $contentPage->title));
    $hackHeroDescription = trim(strip_tags((string) ($heroSection?->subheading ?: $contentPage->excerpt)));
    $hackBenefits = collect(data_get($benefitSection?->payload, 'items', []))
        ->filter(fn ($v) => is_array($v) && filled($v['title'] ?? null))->take(6)->values();
    $hackGroups = collect(data_get($scopeSection?->payload, 'groups', []))
        ->filter(fn ($v) => is_array($v) && filled($v['title'] ?? null))
        ->map(fn ($v) => ['title' => $v['title'], 'items' => collect($v['items'] ?? [])
            ->filter(fn ($item) => is_string($item) && filled($item))->take(10)->values()])
        ->filter(fn ($group) => $group['items']->isNotEmpty())->take(8)->values();
    $mediaPresenter = app(\App\Services\Website\HomeMediaSlides::class);
    $gallerySlides = $mediaPresenter->gallery($galleries, $gallerySection);
    // If the Hacking hero is not assigned yet, an actual Hacking gallery image is
    // a safe page-specific fallback (never reuse Home/Plastering banners).
    if (! $hackHeroDesktop && ! $hackHeroMobile && $gallerySlides->isNotEmpty()) {
        $hackHeroDesktop = $gallerySlides->first()['before']
            ?: $gallerySlides->first()['single'] ?: $gallerySlides->first()['after'];
    }
    $chatSlides = $mediaPresenter->chats($testimonials, $chatSection);
    if ($chatSlides->isEmpty()) {
        // Customer screenshots are shared across the website and have already
        // been approved in Admin. Do not copy image files or make up reviews.
        $homeChatSection = collect($sections ?? [])->first(fn ($section) => $section->sectionDefinition?->key === 'whatsapp_reviews');
        $chatSlides = $mediaPresenter->chats($testimonials, $homeChatSection);
    }
    $textReviews = collect($testimonials)->filter(fn ($review) => $review->type === 'text'
        && filled(trim(strip_tags((string) $review->review))))->take(18)->values();
    $whatsapps = collect($contacts)->where('type', 'whatsapp')
        ->filter(fn ($contact) => preg_match('/^[0-9]{7,15}$/', preg_replace('/\D+/', '', (string) $contact->value)) === 1)
        ->values();

    // Quotation Form shares one backend, but Hacking needs accurate service copy.
    $quoteServiceIdOverride = $hackingService?->id;
    $quoteHeadingOverride = 'Request a Wall & Tile Hacking Quotation';
    $quoteDescriptionOverride = 'Describe the areas to be removed, property type, access and any permits or debris-clearing requirements. We will review the scope before confirming a quotation.';
    $quotePointsOverride = [
        'Tell us which rooms, walls, tiles or fixtures need removal',
        'Discuss necessary approvals and protection before work begins',
        'Confirm the scope, schedule and quotation with the team',
    ];
@endphp

<div class="home-page hacking-page">
    @include('website.pages.hacking-sections.hero')
    @include('website.pages.hacking-sections.scope')
    @include('website.pages.home-sections.reviews')
    @include('website.pages.hacking-sections.related')
    @if($videos->isNotEmpty())
        @include('website.pages.home-sections.videos')
    @endif
    @include('website.pages.hacking-sections.pricing')
    @if($gallerySlides->isNotEmpty())
        @include('website.pages.home-sections.gallery')
    @endif
    @include('website.pages.home-sections.chat-proof')
    @include('website.pages.home-sections.whatsapp-desks')
    @include('website.pages.home-sections.faqs')
    @include('website.partials.quote-form')
</div>
@endsection
