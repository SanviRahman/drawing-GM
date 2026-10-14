<section class="fc-hero" aria-labelledby="fcHeroHeading">
    <div class="fc-hero-visual">
        @if($ceilingHeroDesktop || $ceilingHeroMobile)
            <picture class="mobile-hero-backdrop" aria-hidden="true">
                @if($ceilingHeroMobile)<source srcset="{{ $ceilingHeroMobile }}" media="(max-width: 767px)">@endif
                <img src="{{ $ceilingHeroDesktop ?: $ceilingHeroMobile }}" alt="" loading="lazy">
            </picture>
            <picture class="fc-hero-main">
                @if($ceilingHeroMobile)<source srcset="{{ $ceilingHeroMobile }}" media="(max-width: 767px)">@endif
                <img src="{{ $ceilingHeroDesktop ?: $ceilingHeroMobile }}" alt="False ceiling installation project" fetchpriority="high">
            </picture>
        @else
            <div class="fc-hero-placeholder" role="img" aria-label="False ceiling project photo awaiting upload in CMS"><i class="bi bi-layers" aria-hidden="true"></i></div>
        @endif
        <span class="fc-hero-shade" aria-hidden="true"></span>
    </div>
    <div class="container fc-hero-container">
        <div class="fc-hero-copy">
            <span class="fc-kicker"><i class="bi bi-patch-check-fill" aria-hidden="true"></i> CEILING & PARTITION SOLUTIONS</span>
            <h1 id="fcHeroHeading">{{ $ceilingHeroTitle ?: 'False Ceiling & Partition Wall Services' }}</h1>
            <p>{{ $ceilingHeroDescription ?: 'Explore false ceiling and partition wall installation options for HDB flats, condominiums and other interiors. Let us review the scope and provide a quotation.' }}</p>
            <div class="fc-hero-actions">
                <a href="#ceiling-pricing" class="btn btn-brand">Explore Prices <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                <a href="#quote-form" class="btn fc-outline-button">Request Your Quote <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>
</section>
