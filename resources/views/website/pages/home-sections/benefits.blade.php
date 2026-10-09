<section class="hp-section hp-section-white" id="hp-services">
    <div class="container">
        <div class="hp-section-heading text-center">
            <span class="hp-eyebrow">{{ $benefitSection?->subheading ?: 'Real work. Practical service.' }}</span>
            <h2>{{ $benefitSection?->heading ?: 'WHY CHOOSE OUR HOUSE PAINTERS?' }}</h2>
            <p>Discover our services and what makes each one relevant to your project.</p>
        </div>
        @if($benefitCards->isNotEmpty())
            <div class="row g-4 hp-benefit-grid">
                @foreach($benefitCards as $card)
                    <div class="col-lg-6">
                        <article class="hp-benefit-card">
                            <div class="hp-benefit-photo">
                                @if($card['image'])<img src="{{ $card['image'] }}" alt="{{ $card['title'] }}" loading="lazy">@else<div class="hp-media-placeholder"><i class="bi bi-house-gear"></i></div>@endif
                            </div>
                            <div class="hp-benefit-caption"><div class="hp-benefit-title"><span>{{ $loop->iteration }}</span><h3>{{ $card['title'] }}</h3><i class="bi bi-arrow-up-right"></i></div>@if($card['description'])<p>{{ \Illuminate\Support\Str::limit($card['description'], 200) }}</p>@endif</div>
                        </article>
                    </div>
                @endforeach
            </div>
        @else
            <div class="hp-editorial-empty">Add Benefit Grid items in <strong>CMS Pages → Page Sections</strong> or publish your services to show this section.</div>
        @endif
    </div>
</section>
