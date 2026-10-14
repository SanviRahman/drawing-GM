<section class="hp-section fc-overview" id="ceiling-services" aria-labelledby="fcOverviewHeading">
    <div class="container">
        <div class="row g-4 g-xl-5 align-items-start">
            <div class="col-lg-7">
                <span class="hp-eyebrow">Design, materials & installation</span>
                <h2 id="fcOverviewHeading" class="fc-intro-heading">{{ $benefitSection?->heading ?: 'A Better Plan for Your False Ceiling' }}</h2>
                <p class="fc-section-lead">{{ $benefitSection?->subheading ?: 'Discuss your room dimensions, desired ceiling profile and selected partition materials before installation.' }}</p>
                <div class="fc-benefits">
                    @forelse($ceilingBenefits as $item)
                        <article class="fc-benefit"><i class="bi bi-check2-circle" aria-hidden="true"></i><div><h3>{{ $item['title'] }}</h3>@if(filled($item['description'] ?? null))<p>{{ $item['description'] }}</p>@endif</div></article>
                    @empty
                        <article class="fc-benefit"><i class="bi bi-check2-circle" aria-hidden="true"></i><div><h3>Choose a suitable design</h3><p>Discuss flat ceilings, perimeter boxes and lighting coves.</p></div></article>
                        <article class="fc-benefit"><i class="bi bi-check2-circle" aria-hidden="true"></i><div><h3>Confirm ceiling height and materials</h3><p>Consider existing services, humidity and access requirements.</p></div></article>
                        <article class="fc-benefit"><i class="bi bi-check2-circle" aria-hidden="true"></i><div><h3>Plan installation and handover</h3><p>Review the work schedule, finishes and any electrical coordination.</p></div></article>
                    @endforelse
                </div>
                <a href="#ceiling-scope" class="btn btn-outline-brand fc-work-link">View Our Services <i class="bi bi-arrow-down-right" aria-hidden="true"></i></a>
            </div>
            <div class="col-lg-5">
                <aside class="fc-contact-panel" aria-labelledby="fcContactHeading">
                    <span class="fc-contact-kicker"><i class="bi bi-whatsapp" aria-hidden="true"></i> DIRECT CONTACT DESKS</span>
                    <h2 id="fcContactHeading">Talk to Our Ceiling Team</h2>
                    <p>Contact any available regional WhatsApp desk to discuss a project-specific quotation.</p>
                    <div class="fc-contact-items">
                        @foreach($whatsapps as $contact)
                            @php($digits = preg_replace('/\D+/', '', (string) $contact->value))
                            @php($contactUrl = 'https://wa.me/'.$digits.($contact->message_template_text ? '?text='.rawurlencode($contact->message_template_text) : ''))
                            <a href="{{ $contactUrl }}" target="_blank" rel="noopener noreferrer" class="fc-contact-link {{ $loop->first ? 'fc-contact-primary' : '' }}"><i class="bi bi-whatsapp" aria-hidden="true"></i><span><strong>{{ $contact->label ?: 'WhatsApp Consultation' }}</strong><small>{{ $contact->display_value ?: $contact->value }}</small></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                        @endforeach
                        <a href="#quote-form" class="fc-contact-link fc-contact-quote"><i class="bi bi-clipboard2-check" aria-hidden="true"></i><span><strong>Send a Quote Request</strong><small>Provide your measurements and requirements</small></span><i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                    <div class="fc-contact-note"><i class="bi bi-shield-check" aria-hidden="true"></i> All prices and dates depend on the confirmed scope and availability.</div>
                </aside>
            </div>
        </div>
    </div>
</section>

<section class="hp-section fc-scope" id="ceiling-scope" aria-labelledby="fcScopeHeading">
    <div class="container">
        <div class="hp-section-heading text-center"><span class="hp-eyebrow">Explore the possibilities</span><h2 id="fcScopeHeading">{{ $scopeSection?->heading ?: 'False Ceiling & Partition Wall Options' }}</h2><p>{{ $scopeSection?->subheading ?: 'Discuss the suitable design and materials with our team before confirming installation.' }}</p></div>
        <div class="row g-4 justify-content-center">
            @forelse($ceilingGroups as $group)
                <div class="col-md-6 col-xl-4"><article class="fc-scope-card h-100">
                    <span class="fc-scope-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <h3>{{ $group['title'] }}</h3>
                    @if($group['description'])<p>{{ $group['description'] }}</p>@endif
                    <ul class="list-unstyled mb-0">@foreach($group['items'] as $line)<li><i class="bi bi-check-circle" aria-hidden="true"></i> {{ $line }}</li>@endforeach</ul>
                </article></div>
            @empty
                <div class="col-md-6"><article class="fc-scope-card h-100"><span class="fc-scope-number">01</span><h3>False Ceiling</h3><p>Typical ceiling installation topics to confirm.</p><ul class="list-unstyled"><li><i class="bi bi-check-circle"></i> Flat ceiling &amp; L-box design</li><li><i class="bi bi-check-circle"></i> Concealed lighting &amp; access panels</li><li><i class="bi bi-check-circle"></i> Finishing &amp; painting coordination</li></ul></article></div>
                <div class="col-md-6"><article class="fc-scope-card h-100"><span class="fc-scope-number">02</span><h3>Partition Walls</h3><p>Choose the type and use of the partition.</p><ul class="list-unstyled"><li><i class="bi bi-check-circle"></i> Standard drywall partitions</li><li><i class="bi bi-check-circle"></i> Acoustic or moisture-resistant options</li><li><i class="bi bi-check-circle"></i> Fire-rated specifications where required</li></ul></article></div>
            @endforelse
        </div>
    </div>
</section>
