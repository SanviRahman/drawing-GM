{{-- Shared contact launcher. Never hardcode competitor numbers or response guarantees. --}}
@php
    $whatsappContacts = collect($contacts ?? [])
        ->filter(fn ($channel) => $channel->type === 'whatsapp' && (bool) $channel->is_active)
        ->filter(function ($channel) {
            $digits = preg_replace('/\D+/', '', (string) $channel->value);
            return is_string($digits) && preg_match('/^[0-9]{7,15}$/', $digits) === 1;
        })
        ->values();
    $siteTitle = trim((string) ($settings['site.name'] ?? ''));
@endphp

<aside class="contact-float hp-contact-float" aria-label="WhatsApp contact options">
    <div class="contact-panel hp-contact-panel" id="contactPanel" role="region"
         aria-label="Available WhatsApp contacts" hidden>
        <div class="contact-panel-heading hp-contact-panel-heading">
            <div>
                <strong>{{ $siteTitle !== '' ? $siteTitle.' WhatsApp Desk' : 'WhatsApp Desk' }}</strong>
                <small>{{ $whatsappContacts->count() }} {{ \Illuminate\Support\Str::plural('contact', $whatsappContacts->count()) }} available to message</small>
            </div>
            <button type="button" class="contact-panel-close hp-contact-close" data-contact-close
                    aria-label="Close WhatsApp contacts">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>

        <div class="contact-choices hp-contact-choices">
            @forelse($whatsappContacts as $channel)
                @php
                    $number = preg_replace('/\D+/', '', (string) $channel->value);
                    $message = $channel->message_template_text;
                    $url = 'https://wa.me/'.$number.($message ? '?text='.rawurlencode($message) : '');
                    $label = trim((string) ($channel->label ?: 'WhatsApp Contact'));
                @endphp
                <article class="contact-choice hp-contact-card">
                    <div class="hp-contact-card-information">
                        <div class="hp-contact-card-title"><span class="hp-contact-presence-dot" aria-hidden="true"></span>
                            <strong>{{ $label }}</strong>
                        </div>
                        <small class="hp-contact-number">{{ $channel->display_value ?: $channel->value }}</small>
                        @if($channel->region)
                            <small class="hp-contact-region">{{ $channel->region }}</small>
                        @endif
                        @if($channel->availability_text)
                            <small class="hp-contact-availability">{{ $channel->availability_text }}</small>
                        @endif
                    </div>
                    <a class="hp-contact-chat-button" href="{{ $url }}" target="_blank"
                       rel="noopener noreferrer" aria-label="Chat with {{ $label }} on WhatsApp"
                       data-contact-id="{{ $channel->id }}" data-contact-label="{{ $label }}" data-contact-scope="{{ $channel->region ?: 'general' }}">
                        <i class="bi bi-chat-left-text" aria-hidden="true"></i> Chat
                    </a>
                </article>
            @empty
                <div class="contact-empty hp-contact-empty" role="status">
                    <i class="bi bi-whatsapp" aria-hidden="true"></i>
                    <span>No active WhatsApp numbers yet. Add contacts under Admin → Contact Channels.</span>
                </div>
            @endforelse
        </div>
        <div class="hp-contact-panel-footer">
            <i class="bi bi-shield-check" aria-hidden="true"></i>
            <span>Choose a team member to continue securely on WhatsApp.</span>
        </div>
    </div>

    <button type="button" class="contact-trigger hp-contact-trigger" data-contact-toggle
            aria-expanded="false" aria-controls="contactPanel" aria-label="Show WhatsApp contacts"
            title="WhatsApp contacts">
        <i class="bi bi-plus-lg" aria-hidden="true"></i>
    </button>
</aside>
