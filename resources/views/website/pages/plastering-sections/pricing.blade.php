<section class="hp-section plaster-pricing" id="plaster-pricing" aria-labelledby="plasterPricingTitle">
    <div class="container">
        <div class="hp-section-heading text-center">
            <span class="hp-eyebrow">Plan the work with clarity</span>
            <h2 id="plasterPricingTitle">{{ $pricingSection?->heading ?: 'Wall Plastering Pricing & Quotation' }}</h2>
            <p>{{ $pricingSection?->subheading ?: 'Explore our published pricing options, then confirm the scope and final quotation with the team.' }}</p>
        </div>
        @if($plasterPackages->isNotEmpty())
            <div class="row g-4 justify-content-center">
                @foreach($plasterPackages as $package)
                    <div class="col-md-6 col-xl-4">
                        <article class="plaster-pricing-card h-100">
                            <span class="plaster-pricing-overline">PUBLISHED PACKAGE</span>
                            <h3>{{ $package->name }}</h3>
                            @if($package->subtitle)<p>{{ $package->subtitle }}</p>@endif
                            <ul class="plaster-price-list list-unstyled">
                                @foreach($package->items as $item)
                                    <li><span>{{ $item->label }}</span><strong>{{ $item->display_price }}</strong></li>
                                @endforeach
                            </ul>
                            <a class="btn btn-brand w-100" href="#quote-form">Request a Detailed Quote <i class="bi bi-arrow-right"></i></a>
                        </article>
                    </div>
                @endforeach
            </div>
        @else
            <div class="plaster-pricing-notice"><div><i class="bi bi-clipboard2-check" aria-hidden="true"></i><h3>Request a project-specific quotation</h3><p>Plastering prices depend on wall area, surface condition, materials and the work required. Contact the team for your current rate.</p></div><a href="#quote-form" class="btn btn-brand">Request Pricing <i class="bi bi-arrow-right"></i></a></div>
        @endif
        <p class="plaster-pricing-foot">Only prices approved and published in your CMS are shown. A final quotation depends on the actual project scope.</p>
    </div>
</section>
