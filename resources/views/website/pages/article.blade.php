@extends('website.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/content-media.css') }}">
@endpush

@section('content')
@php
    $featuredImage = $contentPage->featured_image_url ?: $contentPage->getFirstMediaUrl('content_images') ?: ($blogFallbackImage ?? null);
    $contentImages = $contentPage->getMedia('content_images')->filter(fn ($image) => $image->getUrl() !== $featuredImage)->values();
    // Keep author-entered rich formatting without exposing executable HTML attributes.
    $articleBody = \App\Services\Website\SafeArticleMarkup::render($contentPage->body);
@endphp
<section class="cms-content-hero cms-blog-detail-hero"><div class="container">
    <p class="cms-content-eyebrow">PAINTING & HOME SERVICES GUIDE</p>
    <h1>{{ $contentPage->title }}</h1>
    @if($contentPage->excerpt)<p>{{ trim(strip_tags((string) $contentPage->excerpt)) }}</p>@endif
    <a href="{{ url('/blog') }}" class="btn btn-outline-brand">Back to Blog</a>
</div></section>
<section class="cms-content-section cms-blog-detail-content"><div class="container cms-content-narrow">
    @if($featuredImage)
        <figure class="cms-blog-detail-featured"><img src="{{ $featuredImage }}" alt="{{ $contentPage->title }}" fetchpriority="high"></figure>
    @endif
    <article class="cms-information-card cms-article-rich">
        @if($articleBody !== '')
            {!! $articleBody !!}
        @elseif($contentPage->excerpt)
            <p>{{ trim(strip_tags((string) $contentPage->excerpt)) }}</p>
        @endif
    </article>
    @if($contentImages->isNotEmpty())
        <div class="cms-article-photo-grid" aria-label="Article photos">
            @foreach($contentImages as $image)
                <figure class="cms-article-photo"><img src="{{ $image->getUrl() }}" alt="{{ $image->name ?: $contentPage->title }}" loading="lazy"></figure>
            @endforeach
        </div>
    @endif
</div></section>
@include('website.partials.quote-form')
@endsection
