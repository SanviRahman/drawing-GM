@extends('website.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/false-ceiling.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/false-ceiling.js') }}" defer></script>
@endpush

@section('content')
@php
    $ceilingByKey = $ceilingSections->keyBy(fn ($section) => $section->sectionDefinition?->key);
    $heroSection = $ceilingByKey->get('hero');
    $benefitSection = $ceilingByKey->get('benefit_grid');
    $scopeSection = $ceilingByKey->get('rich_text');
    $pricingSection = $ceilingByKey->get('pricing');
    $videoSection = $ceilingByKey->get('video_gallery');
    $reviewSection = $ceilingByKey->get('testimonials');
    $gallerySection = $ceilingByKey->get('before_after') ?? $ceilingByKey->get('gallery');
    $chatSection = $ceilingByKey->get('whatsapp_reviews');
    $faqSection = $ceilingByKey->get('faq');

    // Never show a different service's hero image on this page.
    $ceilingHeroDesktop = $contentPage->getFirstMediaUrl('hero_desktop')
        ?: $ceilingService?->getFirstMediaUrl('hero_desktop')
        ?: collect($heroSection?->mediaItems ?? [])->first(fn ($entry) => $entry->media?->isImage() && $entry->media->isPickerSafe())?->media?->getUrl();
    $ceilingHeroMobile = $contentPage->getFirstMediaUrl('hero_mobile')
        ?: $ceilingService?->getFirstMediaUrl('hero_mobile');
    $ceilingHeroTitle = trim((string) ($heroSection?->heading ?: $contentPage->heroValue('heading', '') ?: $contentPage->title));
    $ceilingHeroDescription = trim(strip_tags((string) ($heroSection?->subheading ?: $contentPage->excerpt)));

    $ceilingBenefits = collect(data_get($benefitSection?->payload, 'items', []))
        ->filter(fn ($item) => is_array($item) && filled($item['title'] ?? null))->take(6)->values();
    $ceilingGroups = collect(data_get($scopeSection?->payload, 'groups', []))
        ->filter(fn ($group) => is_array($group) && filled($group['title'] ?? null))
        ->map(fn ($group) => ['title' => (string) $group['title'],
            'description' => (string) ($group['description'] ?? ''),
            'items' => collect($group['items'] ?? [])->filter(fn ($line) => is_string($line) && filled($line))->take(12)->values()])
        ->filter(fn ($group) => $group['items']->isNotEmpty())->take(8)->values();

    $presenter = app(\App\Services\Website\HomeMediaSlides::class);
    $gallerySlides = $presenter->gallery($galleries, $gallerySection);
    // Customer review sections are shared; reuse already approved screenshots on Home.
    $chatSlides = $presenter->chats($testimonials, $chatSection);
    if ($chatSlides->isEmpty()) {
        $homeChatSection = collect($sections ?? [])->first(fn ($section) => $section->sectionDefinition?->key === 'whatsapp_reviews');
        $chatSlides = $presenter->chats($testimonials, $homeChatSection);
    }
    $textReviews = collect($testimonials)->filter(fn ($review) => $review->type === 'text'
        && filled(trim(strip_tags((string) $review->review))))->take(18)->values();
    $whatsapps = collect($contacts)->where('type', 'whatsapp')
        ->filter(fn ($contact) => preg_match('/^[0-9]{7,15}$/', preg_replace('/\D+/', '', (string) $contact->value)) === 1)
        ->values();

    $quoteServiceIdOverride = $ceilingService?->id;
    $quoteHeadingOverride = 'Book Your False Ceiling & Partition Wall Quotation';
    $quoteDescriptionOverride = 'Share your ceiling size, room type, partition requirements, lighting plans and access needs. Our team can review the details and confirm a project-specific quotation.';
    $quotePointsOverride = [
        'Describe the room dimensions and preferred design',
        'Confirm the ceiling or partition materials and finishing scope',
        'Review electrical coordination, permissions and installation timing',
    ];
@endphp

<div class="home-page false-ceiling-page">
    @include('website.pages.false-ceiling-sections.hero')
    @include('website.pages.false-ceiling-sections.overview')
    @include('website.pages.home-sections.reviews')
    @include('website.pages.false-ceiling-sections.pricing')
    @include('website.pages.home-sections.videos')
    @if($gallerySlides->isNotEmpty())
        @include('website.pages.home-sections.gallery')
    @endif
    @include('website.pages.home-sections.chat-proof')
    @include('website.pages.home-sections.whatsapp-desks')
    @include('website.pages.home-sections.faqs')
    @include('website.partials.quote-form')
</div>
@endsection
