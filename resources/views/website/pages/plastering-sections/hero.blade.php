<section class="plaster-hero" aria-labelledby="plasterHeroTitle">
    <div class="plaster-hero-media">
        @if($plasterHeroDesktop || $plasterHeroMobile)
            <picture>
                @if($plasterHeroMobile)<source media="(max-width: 767px)" srcset="{{ $plasterHeroMobile }}">@endif
                <img src="{{ $plasterHeroDesktop ?: $plasterHeroMobile }}" alt="Wall plastering project" fetchpriority="high">
            </picture>
        @else
            <div class="plaster-hero-placeholder" role="img" aria-label="Plastering project image pending upload"><i class="bi bi-bricks" aria-hidden="true"></i></div>
        @endif
        <div class="plaster-hero-gradient" aria-hidden="true"></div>
    </div>
    <div class="container plaster-hero-content">
        <span class="plaster-kicker"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> WALL PLASTERING SERVICES</span>
        <h1 id="plasterHeroTitle">{{ $plasterHeroTitle }}</h1>
        @if($plasterHeroDescription)<p>{{ $plasterHeroDescription }}</p>@endif
        <div class="plaster-hero-actions">
            <a href="#quote-form" class="btn btn-brand">Request a Plastering Quote <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            <a href="#hp-videos" class="btn plaster-hero-outline"><i class="bi bi-play-circle" aria-hidden="true"></i> Watch Our Work</a>
        </div>
    </div>
</section>
