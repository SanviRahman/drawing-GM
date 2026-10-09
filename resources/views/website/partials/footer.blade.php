@php
    $siteName = (string) ($settings['site.name'] ?? 'Painting Services');
    $phone = $contacts->firstWhere('type','phone');
    $email = $contacts->firstWhere('type','email');
    $whatsapp = $contacts->firstWhere('type','whatsapp');
@endphp
<footer class="site-footer" id="footer"><div class="container">
    <div class="row g-4 footer-columns">
        <div class="col-lg-4 col-md-6">
            <div class="footer-brand"><a href="{{ route('website.home') }}" aria-label="{{ $siteName }} home">@if($logo)<img src="{{ $logo }}" alt="{{ $siteName }}" class="footer-brand-image">@else<span class="site-brand-icon"><i class="bi bi-house-fill"></i></span>@endif<span class="footer-site-name">{{ $siteName }}</span></a></div>
            @if(!empty($settings['footer.description']))<p class="footer-description">{{ trim(strip_tags((string) $settings['footer.description'])) }}</p>@endif
        </div>
        @foreach(['footer-company' => 'Quick Links', 'footer-services' => 'Popular Services', 'footer-legal' => 'Legal'] as $key => $title)
            @php($items = $menus[$key] ?? collect())
            @if($items->isNotEmpty())
                <div class="col-lg-2 col-md-6 col-6"><h3 class="footer-title">{{ $title }}</h3><ul class="list-unstyled footer-links">@foreach($items as $item)<li><a href="{{ $item->url }}" target="{{ $item->target }}" @if($item->target === '_blank') rel="noopener noreferrer" @endif>{{ $item->label }}</a></li>@endforeach</ul></div>
            @endif
        @endforeach
        @if($phone || $email || $whatsapp)
            <div class="col-lg-2 col-md-6 col-6"><h3 class="footer-title">Contact Information</h3><ul class="list-unstyled footer-contacts">
                @if($phone)<li><i class="bi bi-telephone-fill"></i><a href="tel:{{ preg_replace('/[^0-9+]/','',(string)$phone->value) }}">{{ $phone->display_value ?: $phone->value }}</a></li>@endif
                @if($email)<li><i class="bi bi-envelope-fill"></i><a href="mailto:{{ $email->value }}">{{ $email->display_value ?: $email->value }}</a></li>@endif
                @if($whatsapp)<li><i class="bi bi-whatsapp"></i><a href="https://wa.me/{{ preg_replace('/\D+/','',(string)$whatsapp->value) }}" target="_blank" rel="noopener noreferrer">WhatsApp</a></li>@endif
            </ul></div>
        @endif
    </div>
    <div class="footer-bottom"><span>{{ !empty($settings['footer.copyright']) ? trim(strip_tags((string) $settings['footer.copyright'])) : '© '.date('Y').' '.$siteName.'. All rights reserved.' }}</span></div>
</div></footer>
