@extends('website.layouts.app')

@section('content')
    @php
        $title = $contentPage->title ?? $contentPage->name;
        $description = trim(strip_tags((string) ($contentPage->excerpt ?? $contentPage->summary ?? '')));
        $bodyText = trim(strip_tags((string) ($contentPage->content ?? '')));
        $currentSlug = $contentPage->slug;
    @endphp
    <section class="cms-content-hero">
        <div class="container">
            <p class="cms-content-eyebrow">PAINTING & HOME SERVICES</p>
            <h1>{{ $title }}</h1>
            @if($description !== '')<p>{{ $description }}</p>@endif
            <a href="#quote-form" class="btn btn-brand">Request a Quote <i class="bi bi-arrow-up-right ms-1"></i></a>
        </div>
    </section>

    @if($bodyText !== '')
        <section class="cms-content-section"><div class="container cms-content-narrow">
            <h2>Service overview</h2><p>{{ $bodyText }}</p>
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
                    <div class="col-md-6 col-lg-4"><article class="cms-information-card h-100">
                        <h3><a href="{{ route('website.article', $article->slug) }}">{{ $article->title }}</a></h3>
                        <p>{{ trim(strip_tags((string) $article->excerpt)) }}</p>
                        <a href="{{ route('website.article', $article->slug) }}">Read guide <i class="bi bi-arrow-right"></i></a>
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

    @include('website.partials.quote-form')
@endsection
