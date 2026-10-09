@php
    $customPayload = is_array($section->payload) ? $section->payload : [];
    $customCards = collect($customPayload['items'] ?? [])->filter(fn ($entry) => is_array($entry) && filled($entry['title'] ?? null))->take(16)->values();
    $customText = trim(strip_tags((string) ($customPayload['text'] ?? '')));
    $customPhotos = collect($section->mediaItems ?? [])->filter(fn ($entry) => $entry->media
        && ! $entry->media->trashed() && $entry->media->isImage() && $entry->media->isPickerSafe())->take(12);
@endphp
<section class="hp-section hp-custom-section" aria-label="{{ $section->heading ?: ($section->sectionDefinition?->name ?: 'Content') }}">
    <div class="container">
        <div class="hp-section-heading text-center">
            @if($section->heading)<h2>{{ $section->heading }}</h2>@endif
            @if($section->subheading)<p>{{ $section->subheading }}</p>@endif
        </div>
        @if($customText)<p class="hp-muted text-center">{{ $customText }}</p>@endif
        @if($customCards->isNotEmpty())
            <div class="row g-3">
                @foreach($customCards as $card)
                    <div class="col-md-6">
                        <article class="hp-custom-card h-100">
                            <span class="hp-custom-check"><i class="bi bi-check-lg"></i></span>
                            <div><h3>{{ $card['title'] }}</h3><p>{{ strip_tags((string) ($card['description'] ?? $card['text'] ?? '')) }}</p></div>
                        </article>
                    </div>
                @endforeach
            </div>
        @endif
        @if($customPhotos->isNotEmpty())
            <div class="row g-3 mt-3">
                @foreach($customPhotos as $entry)
                    <div class="col-md-6 col-lg-4">
                        <figure class="hp-custom-media h-100 mb-0">
                            <img src="{{ $entry->media->getUrl() }}" alt="{{ strip_tags((string) ($entry->caption_override ?: $entry->media->name ?: 'Project image')) }}" loading="lazy">
                        </figure>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>