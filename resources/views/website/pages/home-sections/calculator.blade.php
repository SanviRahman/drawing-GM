<section class="hp-section hp-calculator-section" id="hp-calculator" aria-labelledby="hpCalcHeading">
    <div class="container">
        <div class="hp-calculator-panel">
            <div class="hp-section-heading text-center">
                <span class="hp-eyebrow hp-calc-eyebrow">Transparent Pricing Calculator</span>
                <h2 id="hpCalcHeading">{{ $calculatorSection?->heading ?: 'Instant Singapore Painting Cost Calculator' }}</h2>
                <p>{{ $calculatorSection?->subheading ?: 'Choose an active package, paint grade and optional services. Only admin-approved prices are displayed.' }}</p>
            </div>
            @if($packages->isNotEmpty())
                <div class="row g-4 align-items-stretch">
                    <div class="col-lg-7">
                        <div class="hp-calc-form">
                            <div class="hp-form-step">
                                <label class="hp-form-step-label" for="hpCalcPackage">1. SELECT HOME TYPE & SIZE</label>
                                <select id="hpCalcPackage" class="form-select hp-calc-select" data-calc-package>
                                    @foreach($packages as $package)
                                        <option value="{{ $package->id }}" data-currency="{{ $package->currency }}">{{ $package->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="hp-form-step">
                                <div class="hp-form-step-label">2. CHOOSE PAINT GRADE / PRICING ITEM</div>
                                <div class="hp-calc-grade-grid" role="group" aria-label="Paint grade">
                                    @foreach($packages as $package)
                                        @foreach($package->items as $item)
                                            <label class="hp-calc-grade" data-grade-row data-package="{{ $package->id }}">
                                                <input type="radio" name="hp_calc_grade" value="{{ $item->id }}" data-calc-grade
                                                    data-package="{{ $package->id }}" data-price-type="{{ $item->price_type }}"
                                                    data-amount="{{ $item->amount }}" data-max="{{ $item->amount_max }}"
                                                    data-currency="{{ $package->currency }}" data-unit="{{ $item->unit }}" data-display="{{ $item->display_price }}">
                                                <span><strong>{{ $item->label }}</strong><small>{{ $item->price_type === 'call' ? 'Price on request' : $item->display_price }}</small></span>
                                                <i class="bi bi-circle" aria-hidden="true"></i>
                                            </label>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                            <div class="hp-form-step">
                                <div class="hp-form-step-label">3. OPTIONAL ADD-ONS & REMEDIATION</div>
                                <div class="hp-calc-addons-grid">
                                    @foreach($packages as $package)
                                        @foreach($pricingAddons as $addon)
                                            @php
                                                $mappedAddon = $package->pricingAddons->firstWhere('id', $addon->id);
                                                $override = is_array($mappedAddon?->pivot?->override_data) ? $mappedAddon->pivot->override_data : [];
                                                $addonPriceType = (string) ($override['price_type'] ?? $addon->price_type);
                                                $addonAmount = array_key_exists('amount', $override) ? $override['amount'] : $addon->amount;
                                                $addonAmountMax = array_key_exists('amount_max', $override) ? $override['amount_max'] : $addon->amount_max;
                                                $addonUnit = (string) ($override['unit'] ?? $addon->unit ?? '');
                                                $addonImage = $addon->getFirstMediaUrl(\App\Models\PricingAddon::MEDIA_COLLECTION);
                                                $money = fn ($value) => $value === null || $value === '' ? null : number_format((float) $value, 2);
                                                $addonDisplay = match ($addonPriceType) {
                                                    'fixed' => ($money($addonAmount) ?? '—') . ($addonUnit ? ' / ' . $addonUnit : ''),
                                                    'from' => 'From ' . ($money($addonAmount) ?? '—') . ($addonUnit ? ' / ' . $addonUnit : ''),
                                                    'range' => ($money($addonAmount) ?? '—') . ' - ' . ($money($addonAmountMax) ?? '—') . ($addonUnit ? ' / ' . $addonUnit : ''),
                                                    default => 'Call for Price',
                                                };
                                            @endphp
                                            <label class="hp-calc-addon" data-addon-row data-package="{{ $package->id }}">
                                                <input type="checkbox" data-calc-addon data-package="{{ $package->id }}"
                                                    data-name="{{ $addon->name }}"
                                                    data-price-type="{{ $addonPriceType }}"
                                                    data-amount="{{ $addonAmount }}" data-max="{{ $addonAmountMax }}"
                                                    data-unit="{{ $addonUnit }}" data-display="{{ $addonDisplay }}">
                                                @if($addonImage)
                                                    <img class="hp-calc-addon-thumb" src="{{ $addonImage }}" alt="" loading="lazy">
                                                @endif
                                                <span><strong>{{ $addon->name }}</strong><small>{{ $addonDisplay }}</small></span>
                                            </label>
                                        @endforeach
                                    @endforeach
                                </div>
                                <p class="hp-addon-empty" data-addon-empty hidden>There are no active add-ons for this package.</p>
                            </div>
                            <div class="hp-form-step hp-calc-quantity-wrap" data-quantity-wrap>
                                <div>
                                    <strong>Quantity for selected unit-priced item</strong>
                                    <small>Only applies when the selected item has an approved unit price.</small>
                                </div>
                                <div class="hp-calc-quantity-control" role="group" aria-label="Item quantity">
                                    <button type="button" data-qty-minus aria-label="Decrease quantity">−</button>
                                    <output data-qty-value aria-live="polite">1</output>
                                    <button type="button" data-qty-plus aria-label="Increase quantity">+</button>
                                </div>
                            </div>
                            <p class="hp-fineprint">Rates and add-ons are loaded from Admin → Pricing Management. This is an indicative calculation, not a binding quotation. Contact the team for final scope and any unit-rate conditions.</p>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="hp-calc-side">
                            <div class="hp-calc-result" aria-live="polite">
                                <span>NETT ESTIMATED COST</span>
                                <small class="hp-calc-estimate-label">Published package estimate</small>
                                <strong data-estimate-amount>Choose a paint grade</strong>
                                <p data-estimate-explainer>Amounts shown only when your admin has approved pricing data.</p>
                                <div class="hp-calc-breakdown" data-estimate-breakdown hidden></div>
                                <div class="hp-calc-assurances">
                                    <div><i class="bi bi-check-circle"></i> Pricing items from the live CMS</div>
                                    <div><i class="bi bi-check-circle"></i> Optional extras displayed separately</div>
                                    <div><i class="bi bi-check-circle"></i> Final quotation subject to site assessment</div>
                                </div>
                                <a class="btn btn-brand" href="#quote-form"><i class="bi bi-whatsapp"></i> Request a Written Quote</a>
                            </div>
                            <div class="hp-calc-includes">
                                <strong>What happens next?</strong>
                                <ol class="hp-calc-next-steps mb-0">
                                    <li>Send your scope and contact details.</li>
                                    <li>Confirm materials, property and site conditions.</li>
                                    <li>Receive the approved final quotation.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="hp-editorial-empty">No active pricing packages with pricing items are available. Configure Pricing Packages and Items in Admin to enable the calculator.</div>
            @endif
        </div>
    </div>
</section>
