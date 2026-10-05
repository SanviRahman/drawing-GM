@props(['section', 'data' => []])
<section class="campaign-section campaign-section-hero-benefits">
    @if($section->heading)
        <h2>{{ $section->heading }}</h2>
    @endif
    @if($section->subheading)
        <p>{{ $section->subheading }}</p>
    @endif
    {-- The frontend resolver should pass pre-resolved, authorized view data through $data. --}
    {{ $slot }}
</section>
