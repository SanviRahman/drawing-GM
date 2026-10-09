<section class="hp-section hp-process" id="hp-process"><div class="container">
    <div class="hp-section-heading text-center"><span class="hp-eyebrow">Our simple process</span><h2>From your enquiry to a planned project</h2><p>A straightforward way to discuss and arrange the work.</p><a href="#quote-form" class="btn btn-brand hp-process-main-cta">Get My Free Quotation <i class="bi bi-arrow-right"></i></a></div>
    <div class="hp-process-grid">
        @foreach($processItems as $step)
            <article class="hp-process-card"><div class="hp-process-title"><span class="hp-step-number {{ $loop->first ? 'is-primary' : '' }}">{{ $loop->iteration }}</span><h3>{{ $step['title'] }}</h3></div><p>{{ $step['description'] ?? '' }}</p><a class="btn {{ $loop->first ? 'btn-brand' : 'btn-warm' }}" href="{{ $loop->last ? '#hp-gallery' : '#quote-form' }}">{{ $step['button'] ?? 'Learn More' }} <i class="bi bi-arrow-right"></i></a></article>
        @endforeach
    </div>
</div></section>
