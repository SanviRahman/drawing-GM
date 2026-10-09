@extends('website.layouts.app')

@section('content')
<section class="cms-content-hero"><div class="container">
    <p class="cms-content-eyebrow">PAINTING & HOME SERVICES GUIDE</p>
    <h1>{{ $contentPage->title }}</h1>
    @if($contentPage->excerpt)<p>{{ trim(strip_tags((string) $contentPage->excerpt)) }}</p>@endif
    <a href="{{ url('/blog') }}" class="btn btn-outline-brand">Back to Blog</a>
</div></section>
<section class="cms-content-section"><div class="container cms-content-narrow">
    <article class="cms-information-card cms-article-body">
        {{ trim(strip_tags((string) $contentPage->body)) }}
    </article>
</div></section>
@include('website.partials.quote-form')
@endsection
