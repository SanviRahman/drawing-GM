<section class="hp-section hp-reviews" id="hp-reviews" aria-labelledby="hpReviewsHeading">
    <div class="container">
        <div class="hp-section-heading hp-left-heading hp-heading-with-action">
            <div>
                <span class="hp-eyebrow hp-eyebrow-orange">Real stories, real results</span>
                <h2 id="hpReviewsHeading">{{ $reviewSection?->heading ?: 'Homeowners Who Trusted Our Clean Handover' }}</h2>
                @if($reviewSection?->subheading)<p>{{ $reviewSection->subheading }}</p>@endif
            </div>
            @if($chatSlides->isNotEmpty())<a href="#hp-chat-reviews" class="btn btn-outline-brand btn-sm">See WhatsApp Feedback <i class="bi bi-arrow-right"></i></a>@endif
        </div>
        @if($textReviews->isNotEmpty())
        <div class="hp-slider" data-hp-carousel aria-label="Customer testimonials carousel">
            <div class="hp-slider-viewport" data-hp-track id="hpReviewsTrack" tabindex="0" role="region" aria-roledescription="carousel" aria-label="Published customer testimonials">
                @foreach($textReviews as $review)
                    <div class="hp-slider-slide hp-slide-review" role="group" aria-roledescription="slide" aria-label="Review {{ $loop->iteration }} of {{ $textReviews->count() }}">
                        <blockquote class="hp-review-card h-100">
                            @if($review->rating)
                                <div class="hp-review-stars" aria-label="{{ $review->rating }} out of 5 stars">
                                    @for($i = 1; $i <= 5; $i++)<i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}" aria-hidden="true"></i>@endfor
                                </div>
                            @endif
                            @php
                                // Source is explicitly managed in Admin → Testimonials.
                                $isGoogleReview = $review->source === 'google';
                                $hasSourceUrl = filled($review->source_url) && filter_var($review->source_url, FILTER_VALIDATE_URL);
                            @endphp
                            @if($isGoogleReview && $hasSourceUrl)
                                <a class="hp-review-source" href="{{ $review->source_url }}" target="_blank" rel="noopener noreferrer">Google Verified <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                            @elseif($isGoogleReview)
                                <span class="hp-review-source">Google Verified</span>
                            @else
                                <span class="hp-review-source hp-review-source-muted">Customer Review</span>
                            @endif
                            <p>“{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $review->review)), 240) }}”</p>
                            <footer>
                                @if($review->photo_url)
                                    <img class="hp-review-avatar hp-review-avatar-image" src="{{ $review->photo_url }}" alt="" loading="lazy">
                                @else
                                    <span class="hp-review-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr((string) ($review->customer_name ?: 'C'), 0, 1)) }}</span>
                                @endif
                                <span><strong>{{ $review->customer_name ?: 'Customer' }}</strong>@if($review->customer_title)<small>{{ $review->customer_title }}</small>@endif</span>
                            </footer>
                        </blockquote>
                    </div>
                @endforeach
            </div>
            @if($textReviews->count() > 1)
                <div class="hp-carousel-toolbar">
                    <span class="hp-carousel-caption" data-hp-position aria-live="polite">1 / {{ $textReviews->count() }}</span>
                    <div class="hp-carousel-arrows">
                        <button class="hp-carousel-arrow" type="button" data-hp-prev aria-label="Previous customer reviews" aria-controls="hpReviewsTrack"><svg aria-hidden="true" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg><span class="visually-hidden">Previous</span></button>
                        <button class="hp-carousel-arrow" type="button" data-hp-next aria-label="Next customer reviews" aria-controls="hpReviewsTrack"><svg aria-hidden="true" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg><span class="visually-hidden">Next</span></button>
                    </div>
                </div>
            @endif
        </div>
        @else
            <div class="hp-editorial-empty">Verified customer reviews will appear here when you approve and activate them in Admin → Testimonials.</div>
        @endif
    </div>
</section>
