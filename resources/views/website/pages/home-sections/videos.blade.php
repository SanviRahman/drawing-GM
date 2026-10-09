 <section class="hp-section hp-videos" id="hp-videos" aria-labelledby="hpVideosHeading">
    <div class="container">
        <div class="hp-section-heading hp-left-heading hp-heading-with-action">
            <div>
                <span class="hp-eyebrow">Real work on site</span>
                <h2 id="hpVideosHeading">{{ $videoSection?->heading ?: 'See How House Painting & Wall Plastering Is Done' }}</h2>
                <p>{{ $videoSection?->subheading ?: 'Watch real painting and plastering projects added through Video Management.' }}</p>
            </div>
        </div>
        @if($videos->isNotEmpty())
            <div class="hp-slider" data-hp-carousel aria-label="Painting project videos carousel">
                <div class="hp-slider-viewport" data-hp-track id="hpVideosTrack" tabindex="0" role="region" aria-roledescription="carousel" aria-label="Published project videos">
                    @foreach($videos as $video)
                        <div class="hp-slider-slide hp-slide-video" role="group" aria-roledescription="slide" aria-label="Video {{ $loop->iteration }} of {{ $videos->count() }}">
                            <article class="hp-video-card h-100">
                                <div class="hp-video-poster">
                                    @if($video->resolved_source_type === 'upload')
                                        <video controls preload="none" poster="{{ $video->poster_url }}" playsinline title="{{ $video->title }}"><source src="{{ $video->playback_url }}"></video>
                                    @elseif($video->poster_url)
                                        <img src="{{ $video->poster_url }}" alt="Thumbnail: {{ $video->title }}" loading="lazy">
                                        <button type="button" class="hp-play-button" data-video-load data-video-url="{{ $video->playback_url }}" data-video-title="{{ $video->title }}" aria-label="Play {{ $video->title }}"><i class="bi bi-play-fill" aria-hidden="true"></i></button>
                                    @else
                                        <button type="button" class="hp-video-empty-play" data-video-load data-video-url="{{ $video->playback_url }}" data-video-title="{{ $video->title }}" aria-label="Play {{ $video->title }}"><i class="bi bi-play-circle-fill" aria-hidden="true"></i><span>Play project video</span></button>
                                    @endif
                                </div>
                                <div class="hp-video-info">
                                    <span class="hp-tag">Project video</span>
                                    <h3>{{ $video->title }}</h3>
                                    @if($video->caption)<p>{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $video->caption)), 130) }}</p>@endif
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
                @if($videos->count() > 1)
                    <div class="hp-carousel-toolbar">
                        <span class="hp-carousel-caption" data-hp-position aria-live="polite">1 / {{ $videos->count() }}</span>
                        <div class="hp-carousel-arrows">
                            <button type="button" class="hp-carousel-arrow" data-hp-prev aria-label="Previous videos" aria-controls="hpVideosTrack"><svg aria-hidden="true" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg><span class="visually-hidden">Previous</span></button>
                            <button type="button" class="hp-carousel-arrow" data-hp-next aria-label="Next videos" aria-controls="hpVideosTrack"><svg aria-hidden="true" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg><span class="visually-hidden">Next</span></button>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="hp-editorial-empty">Project videos will appear here when active, playable videos are added in Video Management.</div>
        @endif
    </div>
</section>
