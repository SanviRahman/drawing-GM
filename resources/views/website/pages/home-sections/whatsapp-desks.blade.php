{{-- Every valid, active WhatsApp channel is already loaded by HomeData.
     No hardcoded contact, response-time promise, artificial region, or item limit. --}}
@php
    $regionalWhatsapps = collect($whatsapps ?? [])
        ->filter(function ($channel) {
            $digits = preg_replace('/\D+/', '', (string) $channel->value);
            return is_string($digits) && preg_match('/^[0-9]{7,15}$/', $digits) === 1;
        })
        ->values();
@endphp

<section class="hp-section hp-regional-desks" id="hp-whatsapp-desks" aria-labelledby="hpRegionalDeskTitle">
    <div class="container">
        <div class="hp-regional-heading text-center">
            <span class="hp-regional-pill"><span class="hp-presence-dot" aria-hidden="true"></span> Contact our team directly</span>
            <h2 id="hpRegionalDeskTitle">WhatsApp Our Team About Your Project</h2>
            <p class="hp-regional-description">Choose an available WhatsApp contact to discuss your painting, plastering or home-service enquiry. Quotation details depend on the work scope.</p>
        </div>

        @if($regionalWhatsapps->isNotEmpty())
            <div class="hp-regional-grid">
                @foreach($regionalWhatsapps as $channel)
                    @php
                        $digits = preg_replace('/\D+/', '', (string) $channel->value);
                        $message = $channel->message_template_text;
                        $waUrl = 'https://wa.me/'.$digits.($message ? '?text='.rawurlencode($message) : '');
                        $region = trim((string) ($channel->region ?: 'Singapore'));
                        $label = trim((string) ($channel->label ?: 'WhatsApp Contact'));
                    @endphp
                    <article class="hp-regional-card {{ $loop->first ? 'hp-regional-card-primary' : '' }}">
                        <div class="hp-regional-card-meta">
                            <span class="hp-region-badge">{{ $region }}</span>
                            @if($channel->availability_text)
                                <small><span class="hp-presence-dot" aria-hidden="true"></span> {{ $channel->availability_text }}</small>
                            @endif
                        </div>
                        <h3>{{ $label }}</h3>
                        <p>Message this contact directly to discuss your service requirements.</p>
                        <div class="hp-regional-number">{{ $channel->display_value ?: $channel->value }}</div>
                        <a href="{{ $waUrl }}" class="hp-regional-chat {{ $loop->first ? 'is-primary' : '' }}" target="_blank" rel="noopener noreferrer" aria-label="Chat with {{ $label }} on WhatsApp">
                            <i class="bi bi-whatsapp" aria-hidden="true"></i> Chat on WhatsApp <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="hp-regional-no-contacts" role="status">
                <i class="bi bi-whatsapp" aria-hidden="true"></i>
                <div>
                    <h3>WhatsApp contact details are being updated</h3>
                    <p>You can still request a quotation using the enquiry form.</p>
                    <a href="#quote-form" class="btn btn-brand">Request a Quote</a>
                </div>
            </div>
        @endif
    </div>
</section>
