@if($chatReviews->isNotEmpty())
<section class="hp-section hp-chat-section" id="hp-chat-reviews" aria-labelledby="hpChatsHeading">
    <div class="container">
        <div class="hp-section-heading hp-left-heading">
            <span class="hp-eyebrow hp-eyebrow-orange">Unfiltered real proof</span>
            <h2 id="hpChatsHeading">{{ $chatSection?->heading ?: 'Actual Client Chats Upon Handover' }}</h2>
            <p>{{ $chatSection?->subheading ?: 'Customer feedback screenshots that your team has approved for publication.' }}</p>
        </div>
        <div class="hp-slider" data-hp-carousel aria-label="WhatsApp screenshot testimonials carousel">
            <div class="hp-slider-viewport" data-hp-track id="hpChatsTrack" tabindex="0" role="region" aria-roledescription="carousel" aria-label="Published screenshot testimonials">
                @foreach($chatReviews as $review)
                    <div class="hp-slider-slide hp-slide-chat" role="group" aria-roledescription="slide" aria-label="Chat {{ $loop->iteration }} of {{ $chatReviews->count() }}">
                        <figure class="hp-chat-card h-100">
                            <div class="hp-chat-header"><span class="hp-chat-avatar"><i class="bi bi-chat-dots-fill" aria-hidden="true"></i></span><strong>{{ $review->customer_name ?: 'Customer feedback' }}</strong></div>
                            <img src="{{ $review->screenshot_url }}" alt="Published customer chat testimonial screenshot" loading="lazy">
                            @if($review->review)<figcaption>{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $review->review)), 120) }}</figcaption>@endif
                        </figure>
                    </div>
                @endforeach
            </div>
            @if($chatReviews->count() > 1)
                <div class="hp-carousel-toolbar"><span class="hp-carousel-caption" data-hp-position aria-live="polite">1 / {{ $chatReviews->count() }}</span>
                    <div class="hp-carousel-arrows">
                        <button type="button" class="hp-carousel-arrow" data-hp-prev aria-label="Previous chat reviews" aria-controls="hpChatsTrack"><svg aria-hidden="true" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg><span class="visually-hidden">Previous</span></button>
                        <button type="button" class="hp-carousel-arrow" data-hp-next aria-label="Next chat reviews" aria-controls="hpChatsTrack"><svg aria-hidden="true" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg><span class="visually-hidden">Next</span></button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endif
