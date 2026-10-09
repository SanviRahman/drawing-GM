<section class="hp-section hp-calculator-section" id="hp-calculator"><div class="container"><div class="hp-calculator-panel">
    <div class="hp-section-heading text-center"><span class="hp-eyebrow">Published price explorer</span><h2>{{ $calculatorSection?->heading ?: 'Instant Painting Cost Preview' }}</h2><p>{{ $calculatorSection?->subheading ?: 'Select a published package and price item to see a guide rate. This is not a final quote.' }}</p></div>
    @if($packages->isNotEmpty())
    <div class="row g-4 align-items-stretch">
        <div class="col-lg-7"><div class="hp-calc-form">
            <div class="hp-form-step"><div class="hp-form-step-label">1. SELECT A PACKAGE</div><div class="hp-option-grid hp-package-options">
                @foreach($packages as $package)
                    <label class="hp-select-pill" data-package-pill><input type="radio" name="hp_package" value="{{ $package->id }}" @checked($loop->first)><span>{{ $package->name }}</span></label>
                @endforeach
            </div></div>
            <div class="hp-form-step"><label class="hp-form-step-label" for="hpCalcItem">2. SELECT YOUR PRICING ITEM</label>
                <select class="form-select hp-calc-select" id="hpCalcItem" data-price-select>
                    <option value="">Select an available option</option>
                    @foreach($packages as $package)
                        @foreach($package->items as $item)
                            <option value="{{ $item->id }}" data-package="{{ $package->id }}" data-price-type="{{ $item->price_type }}" data-amount="{{ $item->amount }}" data-max="{{ $item->amount_max }}" data-currency="{{ $package->currency }}" data-display="{{ $item->display_price }}">{{ $item->label }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div class="hp-form-step"><div class="hp-form-step-label">3. ADDITIONAL SERVICES (OPTIONAL)</div><div class="hp-option-grid hp-addons">
                @foreach($packages as $package)
                    @foreach($package->pricingAddons as $addon)
                        <label class="hp-addon-pill" data-addon-row data-package="{{ $package->id }}"><input type="checkbox" data-calc-addon data-package="{{ $package->id }}" data-price-type="{{ $addon->price_type }}" data-amount="{{ $addon->amount }}" data-unit="{{ $addon->unit }}"><span>{{ $addon->name }}<small>{{ $addon->display_price }}</small></span></label>
                    @endforeach
                @endforeach
            </div><p class="hp-addon-empty" data-addon-empty hidden>No package add-ons are published for this option.</p></div>
            <p class="hp-fineprint">Prices are managed under Admin → Pricing Management. Some price types and unit-based add-ons require a custom quotation.</p>
        </div></div>
        <div class="col-lg-5"><div class="hp-calc-side">
            <div class="hp-calc-result" aria-live="polite"><span>ESTIMATED PUBLISHED PRICE</span><strong data-estimate-amount>Choose an option</strong><p data-estimate-explainer>Published price items appear after you select an option.</p><a class="btn btn-brand" href="#quote-form"><i class="bi bi-calendar-check"></i> Get a Detailed Quote</a></div>
            <div class="hp-calc-includes"><strong>Good to know:</strong><ul class="list-unstyled mb-0"><li><i class="bi bi-check2"></i> Guide prices come from active packages.</li><li><i class="bi bi-check2"></i> Exact scope and material choices may change the quote.</li><li><i class="bi bi-check2"></i> Confirm any unit-priced add-ons separately.</li></ul></div>
        </div></div>
    </div>
    @else
        <div class="hp-editorial-empty">Activate pricing packages and their price items in the Admin Panel to enable this calculator.</div>
    @endif
</div></div></section>
