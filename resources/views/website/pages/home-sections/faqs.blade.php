@if($faqs->isNotEmpty())
<section class="hp-section hp-faq-section" id="hp-faq"><div class="container hp-narrow">
    <div class="hp-section-heading text-center"><h2>{{ $faqSection?->heading ?: 'Frequently Asked Questions' }}</h2><p>{{ $faqSection?->subheading ?: 'Answers to common questions about services and enquiries.' }}</p></div>
    <div class="accordion hp-faq-accordion" id="hpAccordionFaq">
        @foreach($faqs->take(8) as $faq)
            <div class="accordion-item"><h3 class="accordion-header"><button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#hpFaqBody{{ $faq->id }}" aria-expanded="false" aria-controls="hpFaqBody{{ $faq->id }}">{{ $faq->question }}</button></h3><div id="hpFaqBody{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#hpAccordionFaq"><div class="accordion-body">{{ trim(strip_tags((string) $faq->answer)) }}</div></div></div>
        @endforeach
    </div>
</div></section>
@endif
