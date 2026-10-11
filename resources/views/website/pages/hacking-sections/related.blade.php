@if($hackingRelatedServices->isNotEmpty())
<section class="hp-section hack-related" id="hacking-related" aria-labelledby="hackingRelatedTitle">
    <div class="container">
        <div class="hp-section-heading text-center"><span class="hp-eyebrow">Explore related work</span><h2 id="hackingRelatedTitle">{{ $relatedSection?->heading ?: 'Services We Can Help With' }}</h2><p>{{ $relatedSection?->subheading ?: 'Explore our published home-service offerings and discuss a combined project scope.' }}</p></div>
        <div class="row g-4 justify-content-center">
            @foreach($hackingRelatedServices as $service)
                @php
                    $relatedImage = $service->getFirstMediaUrl('hero_desktop') ?: $service->getFirstMediaUrl('gallery');
                    $relatedHref = match ($service->slug) {
                        'wall-hacking' => url('/hacking'),
                        'wall-plastering' => url('/plastering'),
                        default => url('/services/'.$service->slug),
                    };
                @endphp
                <div class="col-md-6 col-lg-4"><a href="{{ $relatedHref }}" class="hack-related-card">
                    <div class="hack-related-photo">
                        @if($relatedImage)<img src="{{ $relatedImage }}" alt="{{ $service->name }} project" loading="lazy">@else<div class="hack-related-placeholder"><i class="bi bi-house-gear" aria-hidden="true"></i></div>@endif
                        <span class="hack-related-arrow"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                    </div>
                    <div class="hack-related-body"><h3>{{ $service->name }}</h3>@if($service->summary)<p>{{ \Illuminate\Support\Str::limit(strip_tags($service->summary), 125) }}</p>@endif</div>
                </a></div>
            @endforeach
        </div>
    </div>
</section>
@endif
