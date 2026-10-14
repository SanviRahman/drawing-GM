<section class="hp-section fc-pricing" id="ceiling-pricing" aria-labelledby="fcPricingHeading">
    <div class="container">
        <div class="hp-section-heading text-center"><span class="hp-eyebrow">Clear options, flexible scope</span><h2 id="fcPricingHeading">{{ $pricingSection?->heading ?: 'False Ceiling & Partition Wall Pricing' }}</h2><p>{{ $pricingSection?->subheading ?: 'Compare active package prices saved in the CMS. Actual prices depend on area, design, materials and site conditions.' }}</p></div>
        <div class="row g-4 justify-content-center">
            @forelse($ceilingPackages as $package)
                @php($isPartition = str_contains(strtolower($package->name), 'partition'))
                <div class="col-md-6 col-xl-{{ $ceilingPackages->count() === 2 ? '6' : '4' }}"><article class="fc-price-card h-100">
                    <div class="fc-price-top {{ $isPartition ? 'fc-price-partition' : '' }}"><span>{{ $package->badge ?: 'PUBLISHED PACKAGE' }}</span><h3>{{ $package->name }}</h3>@if($package->subtitle)<p>{{ $package->subtitle }}</p>@endif</div>
                    <div class="fc-price-content">
                        @if($package->items->isNotEmpty())
                            <ul class="list-unstyled fc-price-list">
                                @foreach($package->items as $item)<li><span>{{ $item->label }}</span><strong>{{ $item->display_price }}</strong></li>@endforeach
                            </ul>
                        @else
                            <p class="fc-price-empty">Rates are available on request. Please confirm measurements and the installation scope.</p>
                        @endif
                        <a href="#quote-form" class="btn btn-brand fc-price-cta">Request a Detailed Quote <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </article></div>
            @empty
                <div class="col-md-6"><article class="fc-price-card h-100"><div class="fc-price-top"><span>INSTALLATION ENQUIRY</span><h3>False Ceiling</h3><p>Discuss ceiling area, layout, material and lighting provision.</p></div><div class="fc-price-content"><p class="fc-price-empty">Published pricing has not been configured yet. Ask our team for the current quotation.</p><a class="btn btn-brand fc-price-cta" href="#quote-form">Request Ceiling Pricing <i class="bi bi-arrow-right"></i></a></div></article></div>
                <div class="col-md-6"><article class="fc-price-card h-100"><div class="fc-price-top fc-price-partition"><span>INSTALLATION ENQUIRY</span><h3>Partition Wall</h3><p>Discuss wall measurements, partition type and finish.</p></div><div class="fc-price-content"><p class="fc-price-empty">Published pricing has not been configured yet. Ask our team for the current quotation.</p><a class="btn btn-brand fc-price-cta" href="#quote-form">Request Partition Pricing <i class="bi bi-arrow-right"></i></a></div></article></div>
            @endforelse
        </div>
        <p class="fc-pricing-note"><i class="bi bi-info-circle" aria-hidden="true"></i> Prices shown are from active Admin Pricing Items, not competitor price examples. Confirm units and the final work scope before booking.</p>
    </div>
</section>
