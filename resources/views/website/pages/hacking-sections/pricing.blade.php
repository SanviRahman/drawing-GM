<section class="hp-section hack-pricing" id="hacking-pricing" aria-labelledby="hackingPricingTitle">
    <div class="container">
        <div class="hp-section-heading text-center"><span class="hp-eyebrow">Plan your renovation</span><h2 id="hackingPricingTitle">{{ $pricingSection?->heading ?: 'Wall & Tile Hacking Quotation' }}</h2><p>{{ $pricingSection?->subheading ?: 'Published service prices will appear here when approved. Final pricing depends on the exact work scope.' }}</p></div>
        @if($hackingPackages->isNotEmpty())
            <div class="row g-4 justify-content-center">
                @foreach($hackingPackages as $package)
                    <div class="col-md-6 col-xl-4"><article class="hack-price-card h-100">
                        <span class="hack-price-kicker">PUBLISHED PACKAGE</span><h3>{{ $package->name }}</h3>
                        @if($package->subtitle)<p>{{ $package->subtitle }}</p>@endif
                        @if($package->items->isNotEmpty())<ul class="list-unstyled hack-price-items">@foreach($package->items as $item)<li><span>{{ $item->label }}</span><strong>{{ $item->display_price }}</strong></li>@endforeach</ul>@else<p class="hack-price-items">Pricing details available on request.</p>@endif
                        <a href="#quote-form" class="btn btn-brand w-100">Request a Quote <i class="bi bi-arrow-right"></i></a>
                    </article></div>
                @endforeach
            </div>
        @else
            <div class="hack-quote-prompt"><span class="hack-quote-icon"><i class="bi bi-clipboard2-check" aria-hidden="true"></i></span><div><h3>Get a Project-Specific Hacking Quote</h3><p>Share the work area, materials to remove and site conditions for a tailored quotation. No unapproved price estimates are displayed.</p></div><a href="#quote-form" class="btn btn-brand">Request Pricing <i class="bi bi-arrow-right"></i></a></div>
        @endif
    </div>
</section>
