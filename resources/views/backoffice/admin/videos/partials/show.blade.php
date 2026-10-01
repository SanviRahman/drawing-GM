@php
$posterUrl=$video->poster_url;
$resolved=$video->resolved_source_type;
$available=$video->available_sources;
@endphp
<div class="row">
    <div class="col-md-5 mb-3">@if($resolved==='youtube'&&$video->youtube_embed_url)<div
            class="embed-responsive embed-responsive-16by9 rounded border"><iframe class="embed-responsive-item"
                src="{{ $video->youtube_embed_url }}" title="{{ $video->title }}"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen></iframe></div>@elseif($resolved==='embed'&&$video->embedded_playback_url)<div
            class="embed-responsive embed-responsive-16by9 rounded border"><iframe class="embed-responsive-item"
                src="{{ $video->embedded_playback_url }}" title="{{ $video->title }}"
                allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>
        @elseif($resolved==='upload'&&$video->playback_url)<video src="{{ $video->playback_url }}" @if($posterUrl)
            poster="{{ $posterUrl }}" @endif @if($video->controls) controls @endif @if($video->autoplay) autoplay @endif
            @if($video->muted) muted @endif @if($video->loop) loop @endif playsinline class="w-100 rounded border
            bg-dark" style="max-height:300px;"></video>@elseif($posterUrl)<img src="{{ $posterUrl }}"
            alt="{{ $video->title }}" class="img-fluid rounded border"
            style="width:100%;max-height:280px;object-fit:cover;">@else<div
            class="border rounded bg-light d-flex align-items-center justify-content-center text-muted"
            style="height:220px;">
            <div class="text-center"><i class="fas fa-video fa-3x mb-2"></i>
                <div>No preview available</div>
            </div>
        </div>@endif</div>
    <div class="col-md-7 mb-3">
        <div class="row">
            <div class="col-md-8 mb-2"><small
                    class="text-muted d-block">Title</small><strong>{{ $video->title }}</strong></div>
            <div class="col-md-4 mb-2"><small class="text-muted d-block">Status</small><span
                    class="badge badge-{{ $video->is_active?'success':'secondary' }} px-2 py-1">{{ $video->is_active?'Active':'Inactive' }}</span>
            </div>
            <div class="col-md-6 mb-2"><small class="text-muted d-block">Frontend Selected Source</small><span
                    class="badge badge-primary px-2 py-1">{{ $video->source_type_label }}</span></div>
            <div class="col-md-6 mb-2"><small
                    class="text-muted d-block">Duration</small><span>{{ $video->duration_seconds!==null?$video->duration_seconds.' sec':'—' }}</span>
            </div>
            <div class="col-md-12 mb-2"><small class="text-muted d-block">Available Fallback
                    Sources</small>@foreach($available as $source)<span
                    class="badge badge-{{ $source==='youtube'?'danger':($source==='embed'?'info':'success') }} mr-1">{{ $source==='youtube'?'1. YouTube':($source==='embed'?'2. Embed':'3. Upload') }}</span>@endforeach
                @if(empty($available))<span class="text-muted">None</span>@endif</div>
            <div class="col-md-12 mb-2"><small class="text-muted d-block">YouTube Video
                    ID</small><span>{{ $video->provider_video_id?:'—' }}</span></div>
            <div class="col-md-12 mb-2"><small class="text-muted d-block">Embedded Video
                    URL</small>@if($video->source_url)<a href="{{ $video->source_url }}" target="_blank"
                    rel="noopener noreferrer" class="text-break">{{ $video->source_url }}</a>@else<span>—</span>@endif
            </div>
            <div class="col-md-6 mb-2"><small class="text-muted d-block">Uploaded
                    Video</small><span>{{ $video->hasMedia(\App\Models\Video::VIDEO_COLLECTION)?'Available':'Not uploaded' }}</span>
            </div>
            <div class="col-md-6 mb-2"><small class="text-muted d-block">Processing</small><span
                    class="badge badge-{{ $video->processing_status==='ready'?'success':($video->processing_status==='failed'?'danger':'warning') }}">{{ ucfirst($video->processing_status) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-12 mb-3"><small class="text-muted d-block mb-1">Caption</small>
        <div class="border rounded bg-light px-3 py-2">{!! $video->caption?:'<span class="text-muted">No
                caption.</span>' !!}</div>
    </div>
    <div class="col-md-12 mb-3">
        <div class="row text-center">
            <div class="col-3"><small
                    class="text-muted d-block">Autoplay</small><strong>{{ $video->autoplay?'Yes':'No' }}</strong></div>
            <div class="col-3"><small
                    class="text-muted d-block">Muted</small><strong>{{ $video->muted?'Yes':'No' }}</strong></div>
            <div class="col-3"><small
                    class="text-muted d-block">Controls</small><strong>{{ $video->controls?'Yes':'No' }}</strong></div>
            <div class="col-3"><small
                    class="text-muted d-block">Loop</small><strong>{{ $video->loop?'Yes':'No' }}</strong></div>
        </div>
    </div>
    @if($video->processing_error)<div class="col-md-12 mb-3"><small class="text-muted d-block mb-1">Processing
            Error</small>
        <div class="alert alert-danger mb-0">{{ $video->processing_error }}</div>
    </div>@endif
    <div class="col-md-4"><small class="text-muted">Sort Order: {{ $video->sort_order }}</small></div>
    <div class="col-md-4 text-md-center"><small class="text-muted">Created:
            {{ $video->created_at?->format('d M Y, h:i A')??'—' }}</small></div>
    <div class="col-md-4 text-md-right"><small class="text-muted">Updated:
            {{ $video->updated_at?->format('d M Y, h:i A')??'—' }}</small></div>
</div>