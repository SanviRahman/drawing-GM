<section class="hp-section plaster-intro" id="plaster-overview" aria-labelledby="plasterIntroTitle">
    <div class="container">
        <div class="row g-4 g-xl-5 align-items-start">
            <div class="col-lg-7">
                <span class="hp-eyebrow">Preparing your home for a smooth finish</span>
                <h2 id="plasterIntroTitle">{{ $benefitSection?->heading ?: 'Wall Plastering for Your Home' }}</h2>
                <p class="plaster-intro-lead">{{ $benefitSection?->subheading ?: 'Discuss your wall condition, surface preparation, desired finish and project scope with our team before arranging the work.' }}</p>
                <div class="plaster-benefit-grid">
                    @forelse($plasterBenefits as $item)
                        <article class="plaster-intro-card">
                            <span class="plaster-intro-icon"><i class="bi bi-check2" aria-hidden="true"></i></span>
                            <div><h3>{{ $item['title'] }}</h3>@if(!empty($item['description']))<p>{{ $item['description'] }}</p>@endif</div>
                        </article>
                    @empty
                        <article class="plaster-intro-card"><span class="plaster-intro-icon"><i class="bi bi-check2"></i></span><div><h3>Assess the wall condition</h3><p>Share photos of cracks, peeling, uneven surfaces or moisture concerns.</p></div></article>
                        <article class="plaster-intro-card"><span class="plaster-intro-icon"><i class="bi bi-check2"></i></span><div><h3>Confirm preparation and materials</h3><p>Discuss suitable plastering methods, finish and protection for the work area.</p></div></article>
                        <article class="plaster-intro-card"><span class="plaster-intro-icon"><i class="bi bi-check2"></i></span><div><h3>Agree on the final scope</h3><p>Confirm the quotation, access and schedule before work begins.</p></div></article>
                    @endforelse
                </div>
                <div class="plaster-intro-actions"><a href="#hp-gallery" class="btn btn-outline-brand">View Project Photos <i class="bi bi-arrow-right"></i></a><a href="#quote-form" class="btn btn-brand">Get a Free Quote <i class="bi bi-arrow-up-right"></i></a></div>
            </div>
            <div class="col-lg-5">
                <aside class="plaster-contact-panel" aria-labelledby="plasterContactTitle">
                    <span class="plaster-contact-eyebrow"><i class="bi bi-whatsapp" aria-hidden="true"></i> DIRECT CONTACT</span>
                    <h2 id="plasterContactTitle">Ask About Your Plastering Project</h2>
                    <p>Contact a published WhatsApp desk, or send your project details through the quotation form.</p>
                    <div class="plaster-contact-options">
                        @foreach($whatsapps as $contact)
                            @php($digits = preg_replace('/\D+/', '', (string) $contact->value))
                            @php($waHref = 'https://wa.me/'.$digits.($contact->message_template_text ? '?text='.rawurlencode($contact->message_template_text) : ''))
                            <a href="{{ $waHref }}" class="plaster-contact-link {{ $loop->first ? 'is-primary' : '' }}" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-whatsapp" aria-hidden="true"></i>
                                <span><strong>{{ $contact->label ?: 'WhatsApp Contact' }}</strong><small>{{ $contact->display_value ?: $contact->value }}</small></span>
                                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                            </a>
                        @endforeach
                        <a href="#quote-form" class="plaster-contact-link is-quote"><i class="bi bi-clipboard-check" aria-hidden="true"></i><span><strong>Request a Site Quote</strong><small>Provide your scope and property details</small></span><i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                    <p class="plaster-contact-note"><i class="bi bi-shield-check" aria-hidden="true"></i> Availability, prices and timelines are confirmed by the team.</p>
                </aside>
            </div>
        </div>
    </div>
</section>
