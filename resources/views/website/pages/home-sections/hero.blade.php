@php
    $heroPhotos = $home ? $home->getMedia('hero_desktop')->take(10) : collect();
    $mobilePhoto = $home ? $home->getFirstMediaUrl('hero_mobile') : null;
    $fallbackPhoto = data_get($gallerySlides->first(), 'single') ?: data_get($gallerySlides->first(), 'after') ?: data_get($gallerySlides->first(), 'before');
    $heroHeading = $home?->heroValue('heading') ?: ($home?->title ?: ($settings['site.name'] ?? 'House Painting & Home Services'));
    $heroDescription = trim(strip_tags((string) ($home?->excerpt ?? '')));
    $cta = $home?->heroValue('cta', []);
    if (!is_array($cta)) $cta = [];
    $ctaLabel = (string) ($cta['label'] ?? 'Get a Free Quote');
    $ctaUrl = (string) ($cta['url'] ?? '#quote-form');
    if (!str_starts_with($ctaUrl, '#') && !preg_match('~^/(?!/)~', $ctaUrl) && !(str_starts_with($ctaUrl, 'https://') && filter_var($ctaUrl, FILTER_VALIDATE_URL))) $ctaUrl = '#quote-form';
    $carouselOn = (bool) $home?->heroValue('slider_mode', false) && $heroPhotos->count() > 1;
    $heroOpacity = min(.75, max(0, (float) $home?->heroValue('overlay', .18)));
    $heroFocus = (string) ($home?->heroValue('focal_position') ?: 'center center');
    if (!preg_match('/^(left|center|right|[0-9]{1,3}%)(\s+(top|center|bottom|[0-9]{1,3}%))?$/', $heroFocus)) $heroFocus = 'center center';
    $introBenefits = $benefitItems->take(4)->values();
@endphp

<section class="hp-hero-banner" aria-label="Home page project gallery">
    <div id="hpHeroCarousel" class="carousel slide hp-hero-carousel" data-bs-ride="{{ $carouselOn ? 'carousel' : 'false' }}">
        <div class="carousel-inner">
            @forelse($heroPhotos->take($carouselOn ? 10 : 1) as $photo)
                <div class="carousel-item @if($loop->first) active @endif">
                    <picture>
                        @if($mobilePhoto && $loop->first)<source media="(max-width: 767px)" srcset="{{ $mobilePhoto }}">@endif
                        <img src="{{ $photo->getUrl() }}" alt="{{ $photo->name ?: 'Painting project' }}" style="object-position:{{ $heroFocus }}" @if(!$loop->first) loading="lazy" @else fetchpriority="high" @endif>
                    </picture>
                </div>
            @empty
                @if($fallbackPhoto)
                    <div class="carousel-item active"><img src="{{ $fallbackPhoto }}" alt="Painting project" fetchpriority="high"></div>
                @else
                    <div class="carousel-item active hp-hero-empty"><div class="hp-hero-missing"><i class="bi bi-images"></i><span>Upload a Home hero image in CMS Pages</span></div></div>
                @endif
            @endforelse
        </div>
        <div class="hp-hero-overlay" style="--hp-hero-opacity:{{ $heroOpacity }}"></div>
        <div class="container hp-hero-banner-inner">
            <div class="hp-hero-trust">
                <span class="hp-hero-trust-icon"><i class="bi bi-shield-check"></i></span>
                <span>Explore our project work<br><strong>{{ $settings['site.name'] ?? 'Painting Services' }}</strong></span>
                <a href="#hp-gallery" class="btn btn-brand btn-sm">View Our Work <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
        @if($carouselOn)
            <div class="carousel-indicators hp-hero-indicators">
                @foreach($heroPhotos as $photo)
                    <button type="button" data-bs-target="#hpHeroCarousel" data-bs-slide-to="{{ $loop->index }}" aria-label="Slide {{ $loop->iteration }}" @if($loop->first) class="active" aria-current="true" @endif></button>
                @endforeach
            </div>
        @endif
    </div>
</section>

@if($services->isNotEmpty())
<section class="hp-service-strip" aria-label="Available services"><div class="container hp-service-strip-inner">
    @foreach($services->take(6) as $service)
        <a href="#hp-services" class="hp-service-chip"><i class="bi bi-check-circle-fill"></i><span>{{ $service->name }}</span></a>
    @endforeach
</div></section>
@endif

<section class="hp-intro" id="hp-intro"><div class="container"><div class="row g-5 align-items-center">
    <div class="col-lg-7">
        <span class="hp-eyebrow">Home Painting & Home Services</span>
        <h1 class="hp-intro-title">{{ $heroHeading }}</h1>
        @if($heroDescription)<p class="hp-intro-lead">{{ $heroDescription }}</p>@endif
        @if($introBenefits->isNotEmpty())
            <div class="row g-3 hp-intro-benefits">
                @foreach($introBenefits as $benefit)
                    <div class="col-sm-6"><div class="hp-intro-benefit"><i class="bi bi-shield-check"></i><div><strong>{{ $benefit['title'] }}</strong>@if(!empty($benefit['description']))<small>{{ $benefit['description'] }}</small>@endif</div></div></div>
                @endforeach
            </div>
        @endif
        <div class="hp-intro-actions"><a href="{{ $ctaUrl }}" class="btn btn-brand">{{ $ctaLabel }} <i class="bi bi-arrow-right"></i></a><a href="#hp-videos" class="btn btn-soft"><i class="bi bi-play-circle"></i> Watch Our Process</a></div>
    </div>
    <div class="col-lg-5">
        <aside class="hp-quote-path">
            <h2>Get Your Painting Quote</h2><p>Tell us what you need and choose your preferred contact method.</p>
            <div class="hp-quote-links">
                <a class="hp-quote-link hp-quote-green" href="#quote-form"><i class="bi bi-clipboard2-check"></i><span><strong>Painting Quotation</strong><small>Send a detailed enquiry</small></span><i class="bi bi-chevron-right"></i></a>
                @foreach($whatsapps as $contact)
                    @php
                        $phoneDigits = preg_replace('/\D+/', '', (string) $contact->value);
                        $message = $contact->message_template_text;
                        $whatsappLink = 'https://wa.me/'.$phoneDigits.($message ? '?text='.rawurlencode($message) : '');
                    @endphp
                    @if(preg_match('/^[0-9]{7,15}$/', $phoneDigits))
                        <a class="hp-quote-link {{ $loop->first ? 'hp-quote-green' : 'hp-quote-orange' }}" href="{{ $whatsappLink }}" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-whatsapp" aria-hidden="true"></i>
                            <span><strong>{{ $contact->label ?: ('WhatsApp '.($loop->iteration)) }}</strong><small>{{ $contact->region ? $contact->region.' · ' : '' }}{{ $contact->display_value ?: $contact->value }}</small></span>
                            <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                        </a>
                    @endif
                @endforeach
                <a class="hp-quote-link hp-quote-coral" href="#quote-form"><i class="bi bi-house-check"></i><span><strong>Site Inspection Request</strong><small>Ask about an on-site assessment</small></span><i class="bi bi-chevron-right"></i></a>
                <a class="hp-quote-link hp-quote-peach" href="#quote-form"><i class="bi bi-calendar-check"></i><span><strong>Confirm & Schedule</strong><small>See how the process works</small></span><i class="bi bi-chevron-right"></i></a>
                <a class="hp-quote-link hp-quote-navy" href="#hp-gallery"><i class="bi bi-brush"></i><span><strong>Explore Our Work</strong><small>Browse project results</small></span><i class="bi bi-chevron-right"></i></a>
            </div>
            <div class="hp-quote-path-foot"><i class="bi bi-shield-check"></i> Quotations depend on project scope and site conditions.</div>
        </aside>
    </div>
</div></div></section>
