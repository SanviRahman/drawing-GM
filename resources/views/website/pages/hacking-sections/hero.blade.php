<section class="hack-hero" aria-labelledby="hackingHeroHeading">
    <div class="hack-hero-picture">
        @if($hackHeroDesktop || $hackHeroMobile)
            <picture class="mobile-hero-backdrop" aria-hidden="true">
                @if($hackHeroMobile)<source srcset="{{ $hackHeroMobile }}" media="(max-width: 767px)">@endif
                <img src="{{ $hackHeroDesktop ?: $hackHeroMobile }}" alt="" loading="lazy">
            </picture>
            <picture class="hack-hero-main">
                @if($hackHeroMobile)<source srcset="{{ $hackHeroMobile }}" media="(max-width: 767px)">@endif
                <img src="{{ $hackHeroDesktop ?: $hackHeroMobile }}" alt="Wall and tile hacking project" fetchpriority="high">
            </picture>
        @else
            <div class="hack-hero-placeholder" role="img" aria-label="Upload a wall hacking hero image in Admin CMS Pages"><i class="bi bi-bricks" aria-hidden="true"></i></div>
        @endif
    </div>
    <div class="hack-hero-overlay" aria-hidden="true"></div>
    <div class="container hack-hero-inner">
        <div class="hack-hero-copy">
            <span class="hack-label"><i class="bi bi-tools" aria-hidden="true"></i> WALL & TILE HACKING</span>
            <h1 id="hackingHeroHeading">{{ $hackHeroTitle }}</h1>
            @if($hackHeroDescription)<p>{{ $hackHeroDescription }}</p>@endif
            <div class="hack-hero-actions">
                <a href="#quote-form" class="btn btn-brand">Request a Hacking Quote <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                <a href="#hacking-scope" class="btn hack-outline-btn">Explore Work Scope <i class="bi bi-arrow-down" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>
</section>
