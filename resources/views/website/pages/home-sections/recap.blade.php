<section class="hp-section hp-recap"><div class="container hp-narrow">
    <div class="hp-section-heading text-center"><h2>Let us recap what you can explore</h2><p>Useful information before booking your next painting project.</p></div>
    <div class="hp-recap-panel"><ul class="list-unstyled mb-0">
        @foreach($recapItems as $item)<li><i class="bi bi-check-circle"></i><span>{{ $item }}</span></li>@endforeach
    </ul></div>
    <div class="text-center mt-4"><a href="#quote-form" class="btn btn-brand">Get My Free Quotation <i class="bi bi-arrow-right"></i></a></div>
</div></section>
