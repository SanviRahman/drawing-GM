<section class="hp-section hp-guarantee" id="hp-guarantees"><div class="container"><div class="row g-5 align-items-center">
    <div class="col-lg-6">
        <h2 class="hp-heading-left">{{ $guaranteeSection?->heading ?: 'Our House Painting Services Guarantees to You!' }}</h2>
        <p class="hp-muted hp-guarantee-lead">{{ $guaranteeSection?->subheading ?: 'What you can expect from our published service information.' }}</p>
        <div class="hp-guarantee-list">
            @forelse($promiseItems as $promise)
                <div class="hp-guarantee-item"><div class="hp-check-square"><i class="bi bi-check-lg"></i></div><div><strong>{{ $promise['title'] }}</strong>@if($promise['description'])<p>{{ $promise['description'] }}</p>@endif</div></div>
            @empty
                <p class="hp-muted">Add approved service benefits in Page Sections to populate this area.</p>
            @endforelse
        </div>
        <a href="#quote-form" class="btn btn-brand">Request a Quotation <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="col-lg-6">
        @php($guaranteeMedia = ($guaranteeSection?->mediaItems?->first()?->media?->getUrl() ?: $benefitSection?->mediaItems?->firstWhere('role', 'image')?->media?->getUrl()) ?: $home?->getFirstMediaUrl('hero_desktop'))
        <div class="hp-guarantee-photo">
            @if($guaranteeMedia)<img src="{{ $guaranteeMedia }}" alt="Painting work" loading="lazy">@else<div class="hp-media-placeholder"><i class="bi bi-paint-bucket"></i></div>@endif
            <div class="hp-guarantee-photo-pill"><i class="bi bi-check-circle-fill"></i> Project image from your CMS</div>
        </div>
    </div>
</div></div></section>
