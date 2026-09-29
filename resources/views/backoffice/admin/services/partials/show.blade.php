@php
    $heroDesktop = $service->getFirstMedia('hero_desktop');
    $heroMobile = $service->getFirstMedia('hero_mobile');
    $gallery = $service->getMedia('gallery');
@endphp

<div class="row">
    <div class="col-md-7">
        <div class="card border shadow-none">
            <div class="card-body">
                <h4 class="font-weight-bold mb-1">{{ $service->name }}</h4>
                <div class="mb-3"><code>/services/{{ $service->slug }}</code></div>
                <div class="mb-2">
                    <span class="badge badge-{{ $service->statusBadgeClass() }} px-2 py-1">{{ $service->statusLabel() }}</span>
                    @if($service->is_featured)
                        <span class="badge badge-primary px-2 py-1 ml-1"><i class="fas fa-star mr-1"></i>Featured</span>
                    @endif
                </div>
                <div class="text-muted small">Sort Order: <strong>{{ $service->sort_order }}</strong>@if($service->icon) · Icon: <strong>{{ $service->icon }}</strong>@endif</div>
            </div>
        </div>

        <div class="card border shadow-none">
            <div class="card-header bg-light py-2"><strong>Summary</strong></div>
            <div class="card-body">
                @if($service->summary)
                    <div class="service-rich-content">{!! $service->summary !!}</div>
                @else
                    <span class="text-muted">No summary.</span>
                @endif
            </div>
        </div>

        <div class="card border shadow-none">
            <div class="card-header bg-light py-2"><strong>Content</strong></div>
            <div class="card-body">
                @if($service->content)
                    <div class="service-rich-content">{!! $service->content !!}</div>
                @else
                    <span class="text-muted">No content.</span>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card border shadow-none"><div class="card-header bg-light py-2"><strong>Publication</strong></div><div class="card-body"><div><strong>Published At:</strong> {{ $service->published_at?->format('d M Y, h:i A') ?? 'Not published' }}</div><div class="mt-2"><strong>Created:</strong> {{ $service->created_at?->format('d M Y, h:i A') ?? '-' }}</div><div class="mt-2"><strong>Updated:</strong> {{ $service->updated_at?->format('d M Y, h:i A') ?? '-' }}</div></div></div>
        <div class="card border shadow-none"><div class="card-header bg-light py-2"><strong>Hero Configuration</strong></div><div class="card-body"><div><strong>Heading:</strong> {{ data_get($service->hero_config, 'heading') ?: '—' }}</div><div class="mt-2"><strong>Overlay:</strong> {{ data_get($service->hero_config, 'overlay') ?? '—' }}</div><div class="mt-2"><strong>Focal:</strong> {{ data_get($service->hero_config, 'focal_position') ?: '—' }}</div><div class="mt-2"><strong>CTA:</strong> {{ data_get($service->hero_config, 'cta.label') ?: '—' }}</div><div class="mt-1"><small class="text-muted">{{ data_get($service->hero_config, 'cta.url') ?: '' }}</small></div></div></div>
    </div>
</div>

<div class="card border shadow-none">
    <div class="card-header bg-light py-2"><strong>Media</strong></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="font-weight-bold">Desktop Hero</h6>
                @if($heroDesktop)
                    @php
                        try {
                            $desktopUrl = $heroDesktop->getUrl();
                        } catch (\Throwable) {
                            $desktopUrl = null;
                        }
                    @endphp
                    @if($desktopUrl)
                        <img src="{{ $desktopUrl }}" alt="Desktop hero" class="img-fluid rounded border">
                    @else
                        <span class="text-muted">Preview unavailable.</span>
                    @endif
                @else
                    <span class="text-muted">No image.</span>
                @endif
            </div>
            <div class="col-md-6">
                <h6 class="font-weight-bold">Mobile Hero</h6>
                @if($heroMobile)
                    @php
                        try {
                            $mobileUrl = $heroMobile->getUrl();
                        } catch (\Throwable) {
                            $mobileUrl = null;
                        }
                    @endphp
                    @if($mobileUrl)
                        <img src="{{ $mobileUrl }}" alt="Mobile hero" class="img-fluid rounded border">
                    @else
                        <span class="text-muted">Preview unavailable.</span>
                    @endif
                @else
                    <span class="text-muted">No image.</span>
                @endif
            </div>
        </div>

        @if($gallery->count() > 0)
            <hr>
            <h6 class="font-weight-bold">Gallery</h6>
            <div class="d-flex flex-wrap" style="gap:10px;">
                @foreach($gallery as $media)
                    @php
                        try {
                            $galleryUrl = $media->getUrl();
                        } catch (\Throwable) {
                            $galleryUrl = null;
                        }
                    @endphp
                    @if($galleryUrl)
                        <img src="{{ $galleryUrl }}" alt="{{ $media->name }}" class="rounded border" style="width:120px;height:86px;object-fit:cover;">
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
