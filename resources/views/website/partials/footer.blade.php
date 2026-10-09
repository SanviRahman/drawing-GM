@php
    $siteName = (string) ($settings['site.name'] ?? 'Painting Services');
    $phone = $contacts->firstWhere('type','phone');
    $email = $contacts->firstWhere('type','email');
    $whatsapp = $contacts->firstWhere('type','whatsapp');
    $whatsappHref = $whatsapp ? 'https://wa.me/'.preg_replace('/\D+/','',(string)$whatsapp->value) : null;
@endphp
<footer class="site-footer" id="footer">
    <div class="container footer-container">
        <div class="row g-4 g-xl-5 footer-columns">
            <div class="col-xl-4 col-lg-4 col-md-6">
                <div class="footer-brand-panel">
                    <div class="footer-brand">
                        <a href="{{ route('website.home') }}" aria-label="{{ $siteName }} home">
                            @if($logo)<img src="{{ $logo }}" alt="{{ $siteName }}" class="footer-brand-image">@else<span class="site-brand-icon"><i class="bi bi-house-fill"></i></span>@endif
                            <span class="footer-site-name">{{ $siteName }}</span>
                        </a>
                    </div>
                    @if(!empty($settings['footer.description']))<p class="footer-description">{{ trim(strip_tags((string) $settings['footer.description'])) }}</p>@endif
                    <div class="footer-actions">
                        <a class="footer-action footer-action-primary" href="{{ route('website.home') }}#quote-form"><i class="bi bi-file-earmark-text"></i><span>Get a Free Quote</span><i class="bi bi-arrow-right"></i></a>
                        @if($whatsappHref)<a class="footer-action footer-action-secondary" href="{{ $whatsappHref }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-whatsapp"></i><span>WhatsApp Us</span></a>@endif
                    </div>
                </div>
            </div>

            @foreach(['footer-company' => ['Quick Links','bi-grid'], 'footer-services' => ['Popular Services','bi-brush'], 'footer-legal' => ['Legal','bi-shield-check']] as $key => [$title,$icon])
                @php($items = $menus[$key] ?? collect())
                @if($items->isNotEmpty())
                    <div class="col-xl-2 col-lg-2 col-md-6 col-6 footer-column">
                        <h3 class="footer-title"><i class="bi {{ $icon }}"></i><span>{{ $title }}</span></h3>
                        <ul class="list-unstyled footer-links">
                            @foreach($items as $item)<li><a href="{{ $item->url }}" target="{{ $item->target }}" @if($item->target === '_blank') rel="noopener noreferrer" @endif><span>{{ $item->label }}</span><i class="bi bi-chevron-right"></i></a></li>@endforeach
                        </ul>
                    </div>
                @endif
            @endforeach

            @if($phone || $email || $whatsapp)
                <div class="col-xl-2 col-lg-2 col-md-6 col-12 footer-contact-column">
                    <h3 class="footer-title"><i class="bi bi-headset"></i><span>Contact Information</span></h3>
                    <ul class="list-unstyled footer-contacts">
                        @if($phone)<li><span class="footer-contact-icon"><i class="bi bi-telephone-fill"></i></span><div><small>Call us</small><a href="tel:{{ preg_replace('/[^0-9+]/','',(string)$phone->value) }}">{{ $phone->display_value ?: $phone->value }}</a></div></li>@endif
                        @if($email)<li><span class="footer-contact-icon"><i class="bi bi-envelope-fill"></i></span><div><small>Email us</small><a href="mailto:{{ $email->value }}">{{ $email->display_value ?: $email->value }}</a></div></li>@endif
                        @if($whatsapp)<li><span class="footer-contact-icon"><i class="bi bi-whatsapp"></i></span><div><small>Quick message</small><a href="{{ $whatsappHref }}" target="_blank" rel="noopener noreferrer">{{ $whatsapp->display_value ?: 'WhatsApp' }}</a></div></li>@endif
                    </ul>
                </div>
            @endif
        </div>

        <div class="footer-bottom">
            <span>{{ !empty($settings['footer.copyright']) ? trim(strip_tags((string) $settings['footer.copyright'])) : '© '.date('Y').' '.$siteName.'. All rights reserved.' }}</span>
        </div>
    </div>
</footer>
