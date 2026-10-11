@extends('website.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/plastering.css') }}">
@endpush

@section('content')
@php
    $plasterByKey = $plasterSections->keyBy(fn ($section) => $section->sectionDefinition?->key);
    $heroSection = $plasterByKey->get('hero');
    $benefitSection = $plasterByKey->get('benefit_grid');
    $videoSection = $plasterByKey->get('video_gallery');
    $reviewSection = $plasterByKey->get('testimonials');
    $gallerySection = $plasterByKey->get('before_after') ?? $plasterByKey->get('gallery');
    $chatSection = $plasterByKey->get('whatsapp_reviews');
    $faqSection = $plasterByKey->get('faq');
    $pricingSection = $plasterByKey->get('pricing');

    // Use the plastering CMS page's own hero media; never reuse Home's banner.
    $plasterHeroDesktop = $contentPage->getFirstMediaUrl('hero_desktop')
        ?: $plasterService?->getFirstMediaUrl('hero_desktop')
        ?: collect($heroSection?->mediaItems ?? [])->first(fn ($entry) => $entry->media?->isImage() && $entry->media->isPickerSafe())?->media?->getUrl();
    $plasterHeroMobile = $contentPage->getFirstMediaUrl('hero_mobile')
        ?: $plasterService?->getFirstMediaUrl('hero_mobile');
    $plasterHeroTitle = trim((string) ($heroSection?->heading ?: $contentPage->heroValue('heading', '') ?: $contentPage->title));
    $plasterHeroDescription = trim(strip_tags((string) ($heroSection?->subheading ?: $contentPage->excerpt)));

    $plasterBenefits = collect(data_get($benefitSection?->payload, 'items', []))
        ->filter(fn ($item) => is_array($item) && filled($item['title'] ?? null))->take(6)->values();
    $mediaPresenter = app(\App\Services\Website\HomeMediaSlides::class);
    $gallerySlides = $mediaPresenter->gallery($galleries, $gallerySection);
    // The shared gallery component on Home also uses media attached to Home's
    // Before/After Page Section and approved galleries across published services.
    // If Plastering has no usable project pairs, render those SAME Home slides.
    // Keep the Plastering heading and retain any dedicated Plastering gallery
    // once it is uploaded; no media copies, fabricated projects or new seeder.
    if ($gallerySlides->isEmpty()) {
        $homeGallerySections = collect($sections ?? [])->keyBy(fn ($section) => $section->sectionDefinition?->key);
        $homeGallerySection = $homeGallerySections->get('before_after') ?? $homeGallerySections->get('gallery');
        $gallerySlides = $mediaPresenter->gallery($sharedHomeGalleries ?? collect(), $homeGallerySection);
    }
    $chatSlides = $mediaPresenter->chats($testimonials, $chatSection);
    // WhatsApp proof is a shared section: if no Plastering-specific screenshots
    // exist, reuse active Home Section Media without copying or duplicating files.
    if ($chatSlides->isEmpty()) {
        $sharedChatSection = collect($sections ?? [])->first(fn ($section) => $section->sectionDefinition?->key === 'whatsapp_reviews');
        $chatSlides = $mediaPresenter->chats($testimonials, $sharedChatSection);
    }
    $textReviews = collect($testimonials)->filter(fn ($review) => $review->type === 'text'
        && filled(trim(strip_tags((string) $review->review))))->take(18)->values();
    $whatsapps = collect($contacts)->where('type', 'whatsapp')
        ->filter(fn ($contact) => preg_match('/^[0-9]{7,15}$/', preg_replace('/\D+/', '', (string) $contact->value)) === 1)
        ->values();
@endphp

<div class="home-page plastering-page">
    @include('website.pages.plastering-sections.hero')
    @include('website.pages.plastering-sections.intro')
    @include('website.pages.home-sections.reviews')
    @include('website.pages.home-sections.videos')
    @include('website.pages.plastering-sections.pricing')
    @include('website.pages.home-sections.gallery')
    @include('website.pages.home-sections.chat-proof')
    @include('website.pages.home-sections.whatsapp-desks')
    @include('website.pages.home-sections.faqs')
    @include('website.partials.quote-form')
</div>
@endsection
