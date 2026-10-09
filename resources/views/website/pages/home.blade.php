@extends('website.layouts.app')

@section('content')
@php
    // Page Sections are the only source of independently editable block copy.
    // No inactive/draft content is imported here: HomeData filters it.
    $sectionByKey = $sections->keyBy(fn ($section) => $section->sectionDefinition?->key);
    $benefitSection = $sectionByKey->get('benefit_grid');
    $richSection = $sectionByKey->get('rich_text');
    $serviceSection = $sectionByKey->get('service_carousel');
    $videoSection = $sectionByKey->get('video_gallery');
    $reviewSection = $sectionByKey->get('testimonials');
    $calculatorSection = $sectionByKey->get('paint_calculator');
    $gallerySection = $sectionByKey->get('before_after') ?? $sectionByKey->get('gallery');
    $chatSection = $sectionByKey->get('whatsapp_reviews');
    $faqSection = $sectionByKey->get('faq');
    $ctaSection = $sectionByKey->get('cta');

    $benefitItems = collect(data_get($benefitSection?->payload, 'items', []))
        ->filter(fn ($v) => is_array($v) && filled($v['title'] ?? null))
        ->values();

    $benefitCards = $benefitItems->take(6)->map(fn ($v) => [
        'title' => trim((string) ($v['title'] ?? '')),
        'description' => trim(strip_tags((string) ($v['description'] ?? $v['text'] ?? ''))),
        'image' => is_string($v['image'] ?? null) && preg_match('~^https?://~i', $v['image']) && filter_var($v['image'], FILTER_VALIDATE_URL) ? $v['image'] : null,
    ])->values();

    // If a dedicated benefit grid is not set up, use actual published services,
    // not invented 'years of experience', ratings or guarantees.
    if ($benefitCards->isEmpty()) {
        $benefitCards = $services->take(6)->map(fn ($service) => [
            'title' => (string) $service->name,
            'description' => trim(strip_tags((string) ($service->summary ?: $service->content ?: ''))),
            'image' => $service->getFirstMediaUrl('hero_desktop') ?: $service->getFirstMediaUrl('gallery') ?: null,
        ])->values();
    }

    $promiseItems = collect(data_get($benefitSection?->payload, 'guarantees', []))
        ->filter(fn ($v) => is_array($v) ? filled($v['title'] ?? null) : filled($v))
        ->map(fn ($v) => is_array($v) ? ['title' => $v['title'], 'description' => $v['description'] ?? ''] : ['title' => $v, 'description' => ''])
        ->take(4)->values();
    if ($promiseItems->isEmpty()) {
        $promiseItems = $benefitItems->take(4)->map(fn ($v) => ['title' => $v['title'], 'description' => $v['description'] ?? ''])->values();
    }
    // Neutral editorial checklist rather than unverified warranties.
    $warningHtml = (string) data_get($richSection?->payload, 'text', '');
    $warningText = trim(strip_tags(preg_replace('~</?(p|br|li|div)[^>]*>~i', "\n", $warningHtml) ?? $warningHtml));
    $warningLines = collect(preg_split('/\R+/', $warningText) ?: [])->map(fn ($v) => trim($v))->filter()->take(6)->values();
    if ($warningLines->isEmpty()) {
        $warningLines = collect([
            'Confirm the work scope and the paint/finish choices in writing.',
            'Ask whether preparation, protection and cleanup are included.',
            'Check how scheduling and access to the property will be arranged.',
            'Discuss any repairs before approving the final price.',
        ]);
    }

    $processItems = collect(data_get($ctaSection?->payload, 'steps', []))
        ->filter(fn ($v) => is_array($v) && filled($v['title'] ?? null))->take(5)->values();
    if ($processItems->isEmpty()) {
        $processItems = collect([
            ['title' => 'REQUEST A QUOTE', 'description' => 'Share your painting or renovation requirements.', 'button' => 'Get a Free Quote'],
            ['title' => 'REVIEW THE SCOPE', 'description' => 'Clarify the work area, materials and any preparation needed.', 'button' => 'Ask a Question'],
            ['title' => 'CONFIRM THE DETAILS', 'description' => 'Agree on the quotation and the work plan.', 'button' => 'Request Details'],
            ['title' => 'ARRANGE THE WORK', 'description' => 'Coordinate a suitable schedule with the team.', 'button' => 'Contact Us'],
            ['title' => 'PROJECT HANDOVER', 'description' => 'Review completed work and raise any questions.', 'button' => 'View Gallery'],
        ]);
    }

    $recapItems = collect(data_get($ctaSection?->payload, 'items', []))
        ->filter(fn ($v) => is_array($v) ? filled($v['title'] ?? null) : filled($v))
        ->map(fn ($v) => is_array($v) ? (string) $v['title'] : (string) $v)
        ->take(10)->values();
    if ($recapItems->isEmpty()) {
        $recapItems = collect([
            'Review the available services and suitable package options.',
            'Request information about painting preparation and materials.',
            'See real project photographs and videos when available.',
            'Contact the team using an active WhatsApp number or enquiry form.',
            'Read published FAQs before arranging your project.',
        ]);
    }

    $galleriesItems = $galleries->flatMap(fn ($g) => $g->items)->filter(fn ($item) =>
        $item->getFirstMediaUrl('before') || $item->getFirstMediaUrl('after') || $item->getFirstMediaUrl('image')
    )->take(16)->values();
    $textReviews = $testimonials->filter(fn ($t) => $t->type !== 'whatsapp_screenshot' && filled(trim(strip_tags((string) $t->review))))->take(18)->values();
    $chatReviews = $testimonials->filter(fn ($t) => $t->type === 'whatsapp_screenshot' && $t->screenshot_url)->take(18)->values();
    $whatsapps = $contacts->where('type', 'whatsapp')->filter(fn ($c) => preg_replace('/\D+/', '', (string) $c->value) !== '')->values();
@endphp

@include('website.pages.home-sections.hero')
@include('website.pages.home-sections.benefits')
@include('website.pages.home-sections.guarantees')
@include('website.pages.home-sections.warning')
@include('website.pages.home-sections.videos')
@include('website.pages.home-sections.reviews')
@include('website.pages.home-sections.calculator')
@include('website.pages.home-sections.process')
@include('website.pages.home-sections.whatsapp-desks')
@include('website.pages.home-sections.gallery')
@include('website.pages.home-sections.chat-proof')
@include('website.pages.home-sections.recap')
@include('website.pages.home-sections.faqs')
@include('website.partials.quote-form')
@endsection
