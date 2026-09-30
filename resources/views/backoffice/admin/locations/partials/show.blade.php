@php
    $desktopHero = null;
    $mobileHero = null;
    try {
        $desktopHero = $location->getFirstMediaUrl('hero_desktop') ?: null;
        $mobileHero = $location->getFirstMediaUrl('hero_mobile') ?: null;
    } catch (\Throwable) {
        $desktopHero = null;
        $mobileHero = null;
    }
    $gallery = $location->getMedia('gallery');
@endphp

<div class="row">
    <div class="col-md-8 mb-2"><small class="text-muted d-block">Location</small><h5 class="font-weight-bold text-dark mb-0">{{ $location->name }}</h5><small class="text-muted">/{{ $location->slug }}</small></div>
    <div class="col-md-2 mb-2"><small class="text-muted d-block">Status</small><span class="badge badge-{{ $location->statusBadgeClass() }} px-2 py-1">{{ $location->statusLabel() }}</span></div>
    <div class="col-md-2 mb-2"><small class="text-muted d-block">Order</small><strong>{{ $location->sort_order }}</strong></div>
    <div class="col-md-6 mb-2"><small class="text-muted d-block">Region</small><strong>{{ $location->region ?: '—' }}</strong></div>
    <div class="col-md-6 mb-2"><small class="text-muted d-block">Published</small><span>{{ $location->published_at?->format('d M Y, h:i A') ?? '—' }}</span></div>

    <div class="col-md-12 mb-2">
        <small class="text-muted d-block mb-1">Postal Codes</small>
        @if(is_array($location->postal_codes) && count($location->postal_codes) > 0)
            <div>
                @foreach($location->postal_codes as $postalCode)
                    <span class="badge badge-light border mr-1 mb-1">{{ $postalCode }}</span>
                @endforeach
            </div>
        @else
            <span class="text-muted">No postal codes configured.</span>
        @endif
    </div>

    <div class="col-md-12 mb-2">
        <small class="text-muted d-block mb-1">Summary</small>
        @if($location->summary)
            <div class="border rounded bg-light px-3 py-2">{!! $location->summary !!}</div>
        @else
            <div class="border rounded bg-light px-3 py-2 text-muted">No summary.</div>
        @endif
    </div>

    <div class="col-md-12 mb-3">
        <small class="text-muted d-block mb-1">Content</small>
        @if($location->content)
            <div class="border rounded bg-light px-3 py-2">{!! $location->content !!}</div>
        @else
            <div class="border rounded bg-light px-3 py-2 text-muted">No content.</div>
        @endif
    </div>

    <div class="col-md-12 mb-2"><h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-image mr-1"></i>Hero Configuration</h6></div>
    <div class="col-md-6 mb-2"><small class="text-muted d-block">Heading</small><span>{{ $location->heroValue('heading') ?: '—' }}</span></div>
    <div class="col-md-2 mb-2"><small class="text-muted d-block">Overlay</small><span>{{ $location->heroValue('overlay') !== null ? $location->heroValue('overlay') : '—' }}</span></div>
    <div class="col-md-4 mb-2"><small class="text-muted d-block">Focal Position</small><span>{{ $location->heroValue('focal_position') ?: '—' }}</span></div>
    <div class="col-md-6 mb-3"><small class="text-muted d-block">CTA Label</small><span>{{ $location->heroValue('cta.label') ?: '—' }}</span></div>
    <div class="col-md-6 mb-3"><small class="text-muted d-block">CTA URL</small><span>{{ $location->heroValue('cta.url') ?: '—' }}</span></div>

    <div class="col-md-6 mb-3">
        <small class="text-muted d-block mb-1">Desktop Hero</small>
        @if($desktopHero)
            <img src="{{ $desktopHero }}" alt="Desktop hero" class="img-fluid rounded border" style="width:100%;height:150px;object-fit:cover;">
        @else
            <div class="border rounded bg-light text-center text-muted py-5"><i class="far fa-image mr-1"></i>No desktop hero.</div>
        @endif
    </div>

    <div class="col-md-6 mb-3">
        <small class="text-muted d-block mb-1">Mobile Hero</small>
        @if($mobileHero)
            <img src="{{ $mobileHero }}" alt="Mobile hero" class="img-fluid rounded border" style="width:100%;height:150px;object-fit:cover;">
        @else
            <div class="border rounded bg-light text-center text-muted py-5"><i class="far fa-image mr-1"></i>No mobile hero.</div>
        @endif
    </div>

    <div class="col-md-12">
        <small class="text-muted d-block mb-1">Gallery</small>
        <div class="d-flex flex-wrap" style="gap:8px;">
            @if($gallery->count() > 0)
                @foreach($gallery as $media)
                    @php
                        try {
                            $galleryUrl = $media->getUrl();
                        } catch (\Throwable) {
                            $galleryUrl = null;
                        }
                    @endphp

                    @if($galleryUrl)
                        <img src="{{ $galleryUrl }}" alt="{{ $media->name }}" class="rounded border" style="width:105px;height:76px;object-fit:cover;">
                    @endif
                @endforeach
            @else
                <div class="border rounded bg-light text-center text-muted py-3 px-4"><i class="far fa-images mr-1"></i>No gallery images.</div>
            @endif
        </div>
    </div>

    <div class="col-md-6 mt-3"><small class="text-muted">Created: {{ $location->created_at?->format('d M Y, h:i A') ?? '—' }}</small></div>
    <div class="col-md-6 mt-3 text-md-right"><small class="text-muted">Updated: {{ $location->updated_at?->format('d M Y, h:i A') ?? '—' }}</small></div>
</div>
