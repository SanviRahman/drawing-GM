<section class="hp-section hack-scope" id="hacking-scope" aria-labelledby="hackingScopeTitle">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-7">
                <div class="hack-section-intro">
                    <span class="hp-eyebrow"><i class="bi bi-shield-check" aria-hidden="true"></i> Project planning</span>
                    <h2>{{ $benefitSection?->heading ?: 'Wall & Tile Hacking Services' }}</h2>
                    <p>{{ $benefitSection?->subheading ?: 'Discuss suitable removal, protection, approvals and debris management with the team before confirming your project.' }}</p>
                </div>
                <div class="hack-benefits">
                    @forelse($hackBenefits as $benefit)
                    <article class="hack-benefit"><span class="hack-check"><i class="bi bi-check2" aria-hidden="true"></i></span><div><h3>{{ $benefit['title'] }}</h3>@if(!empty($benefit['description']))<p>{{ $benefit['description'] }}</p>@endif</div></article>
                    @empty
                    <article class="hack-benefit"><span class="hack-check"><i class="bi bi-check2"></i></span><div><h3>Define the removal work</h3><p>Share which walls, floors, tiles, fittings or cabinets may need dismantling.</p></div></article>
                    <article class="hack-benefit"><span class="hack-check"><i class="bi bi-check2"></i></span><div><h3>Check necessary permissions</h3><p>Some renovation work requires permits or building-management approval before starting.</p></div></article>
                    <article class="hack-benefit"><span class="hack-check"><i class="bi bi-check2"></i></span><div><h3>Plan protection and disposal</h3><p>Discuss dust management, site access, debris removal and an agreed work schedule.</p></div></article>
                    @endforelse
                </div>
                <a href="#hacking-work" class="btn btn-outline-brand mt-4">View Hacking Work Areas <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="col-lg-5">
                {{-- Reuse the same live Admin Contact Channels displayed on Home/Plastering. --}}
                <aside class="hack-contact-card" aria-label="Available project contacts">
                    <div class="hack-contact-heading"><span><i class="bi bi-whatsapp" aria-hidden="true"></i> TALK TO OUR TEAM</span><h2>Request a Wall Hacking Quote</h2><p>Select a published contact, or fill in our quotation form.</p></div>
                    <div class="hack-contact-list">
                    @foreach($whatsapps as $contact)
                        @php($digits = preg_replace('/\D+/', '', (string) $contact->value))
                        @php($waUrl = 'https://wa.me/'.$digits.($contact->message_template_text ? '?text='.rawurlencode($contact->message_template_text) : ''))
                        <a href="{{ $waUrl }}" class="hack-contact-action {{ $loop->first ? 'hack-contact-primary' : '' }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-whatsapp" aria-hidden="true"></i><span><strong>{{ $contact->label ?: 'WhatsApp' }}</strong><small>{{ $contact->display_value ?: $contact->value }}</small></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                    @endforeach
                    @if($whatsapps->isEmpty())<p class="hack-contact-none">Please use the quotation form to contact our team.</p>@endif
                    <a href="#quote-form" class="hack-contact-action hack-contact-quote"><i class="bi bi-clipboard-check" aria-hidden="true"></i><span><strong>Request a Detailed Quote</strong><small>Send project details securely</small></span><i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                    <p class="hack-contact-disclaimer"><i class="bi bi-info-circle" aria-hidden="true"></i> Pricing and availability depend on the actual work and applicable approvals.</p>
                </aside>
            </div>
        </div>
    </div>
</section>
<section class="hp-section hack-work" id="hacking-work" aria-labelledby="hackingWorkTitle">
    <div class="container">
        <div class="hp-section-heading text-center">
            <span class="hp-eyebrow">Scope of work</span>
            <h2 id="hackingWorkTitle">{{ $scopeSection?->heading ?: 'What Wall & Tile Hacking Can Include' }}</h2>
            <p>{{ $scopeSection?->subheading ?: 'Choose the areas relevant to your property. Confirm the exact scope and any approvals before work begins.' }}</p>
        </div>
        @if($hackGroups->isNotEmpty())
            <div class="row g-4">
                @foreach($hackGroups as $group)
                    <div class="col-md-6"><article class="hack-work-card h-100">
                        <div class="hack-work-head"><span class="hack-work-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $group['title'] }}</h3></div>
                        <ul class="list-unstyled mb-0">
                            @foreach($group['items'] as $line)<li><i class="bi bi-check-circle" aria-hidden="true"></i><span>{{ $line }}</span></li>@endforeach
                        </ul>
                    </article></div>
                @endforeach
            </div>
        @else
            <div class="hack-work-empty"><p>Ask our team about specific kitchen, bathroom, bedroom and other wall or tile removal requirements.</p><a class="btn btn-brand" href="#quote-form">Describe Your Work <i class="bi bi-arrow-right"></i></a></div>
        @endif
    </div>
</section>
