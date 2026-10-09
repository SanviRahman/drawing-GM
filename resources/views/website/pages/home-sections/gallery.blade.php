<section class="hp-section hp-gallery" id="hp-gallery" aria-labelledby="hpGalleryHeading">
    <div class="container">
        <div class="hp-section-heading text-center">
            <span class="hp-eyebrow">Verified case studies</span>
            <h2 id="hpGalleryHeading">{{ $gallerySection?->heading ?: 'Before & After Workmanship Gallery' }}</h2>
            <p>{{ $gallerySection?->subheading ?: 'Browse project images from the gallery. Drag the comparison handle to explore transformations.' }}</p>
        </div>
        @if($galleriesItems->isNotEmpty())
            <div class="hp-slider" data-hp-carousel aria-label="Before and after project gallery carousel">
                <div class="hp-slider-viewport" data-hp-track id="hpGalleryTrack" tabindex="0" role="region" aria-roledescription="carousel" aria-label="Published project gallery">
                    @foreach($galleriesItems as $item)
                        @php($before = $item->getFirstMediaUrl('before'))
                        @php($after = $item->getFirstMediaUrl('after'))
                        @php($single = $item->getFirstMediaUrl('image'))
                        <div class="hp-slider-slide hp-slide-gallery" role="group" aria-roledescription="slide" aria-label="Project {{ $loop->iteration }} of {{ $galleriesItems->count() }}">
                            <figure class="hp-gallery-card h-100">
                                <div class="hp-gallery-topline"><span class="hp-gallery-project-index">PROJECT {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><span class="hp-gallery-media-type">{{ $before && $after ? 'Before / After' : 'Project image' }}</span></div>
                                <div class="hp-gallery-image">
                                    @if($before && $after)
                                        <div class="hp-compare" data-comparison style="--split:50%">
                                            <img class="hp-compare-after" src="{{ $after }}" alt="After: {{ $item->title ?: 'Project' }}" loading="lazy">
                                            <div class="hp-compare-before"><img src="{{ $before }}" alt="Before: {{ $item->title ?: 'Project' }}" loading="lazy"></div>
                                            <span class="hp-compare-handle" aria-hidden="true"><i class="bi bi-arrows" aria-hidden="true"></i></span>
                                            <input type="range" min="0" max="100" value="50" aria-label="Compare before and after: {{ $item->title ?: 'Project' }}">
                                            <span class="hp-before-label">BEFORE</span><span class="hp-after-label">AFTER</span>
                                        </div>
                                    @elseif($single || $after || $before)
                                        <img src="{{ $single ?: $after ?: $before }}" alt="{{ $item->title ?: 'Project' }}" loading="lazy">
                                    @endif
                                </div>
                                <figcaption><h3>{{ $item->title ?: 'Project photograph' }}</h3>@if($item->caption)<p>{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $item->caption)), 105) }}</p>@endif</figcaption>
                            </figure>
                        </div>
                    @endforeach
                </div>
                @if($galleriesItems->count() > 1)
                    <div class="hp-carousel-toolbar">
                        <span class="hp-carousel-caption" data-hp-position aria-live="polite">1 / {{ $galleriesItems->count() }}</span>
                        <div class="hp-carousel-arrows">
                            <button type="button" class="hp-carousel-arrow" data-hp-prev aria-label="Previous projects" aria-controls="hpGalleryTrack"><svg aria-hidden="true" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg><span class="visually-hidden">Previous</span></button>
                            <button type="button" class="hp-carousel-arrow" data-hp-next aria-label="Next projects" aria-controls="hpGalleryTrack"><svg aria-hidden="true" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg><span class="visually-hidden">Next</span></button>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="hp-editorial-empty">This gallery will appear when active Gallery records have before/after or project images.</div>
        @endif
    </div>
</section>
