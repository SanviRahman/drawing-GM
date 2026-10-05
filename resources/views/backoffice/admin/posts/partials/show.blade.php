@php
    $featuredUrl = $post->featured_image_url;
    $contentImages = $post->getMedia(\App\Models\Post::CONTENT_IMAGES_COLLECTION);
@endphp

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card border-0 bg-light shadow-sm h-100">
            <div class="card-body text-center">
                @if($featuredUrl)
                    <img src="{{ $featuredUrl }}" alt="{{ $post->title }}" class="img-fluid rounded border shadow-sm mb-3" style="max-height:240px;object-fit:cover;">
                @else
                    <div class="rounded bg-white border d-flex align-items-center justify-content-center mx-auto mb-3 text-muted" style="height:180px;"><i class="far fa-image fa-3x"></i></div>
                @endif
                <span class="badge badge-{{ $post->statusBadgeClass() }} px-3 py-1">{{ $post->statusLabel() }}</span>
                <h5 class="font-weight-bold mt-3 mb-1">{{ $post->title }}</h5>
                <code>{{ $post->slug }}</code>
            </div>
        </div>
    </div>

    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3"><small class="text-muted text-uppercase font-weight-bold">Author</small><div class="font-weight-bold">{{ $post->author?->name ?? 'Unknown' }}</div></div>
                    <div class="col-md-6 mb-3"><small class="text-muted text-uppercase font-weight-bold">Category</small><div class="font-weight-bold">{{ $post->category?->name ?? 'Uncategorized' }}</div></div>
                    <div class="col-md-6 mb-3"><small class="text-muted text-uppercase font-weight-bold">Published At</small><div>{{ $post->published_at?->format('d M Y, h:i A') ?? '—' }}</div></div>
                    <div class="col-md-3 mb-3"><small class="text-muted text-uppercase font-weight-bold">Reading</small><div>{{ $post->reading_minutes ? $post->reading_minutes . ' min' : '—' }}</div></div>
                    <div class="col-md-3 mb-3"><small class="text-muted text-uppercase font-weight-bold">Comments</small><div>{{ $post->allow_comments ? 'Allowed' : 'Disabled' }}</div></div>
                    <div class="col-md-6 mb-3"><small class="text-muted text-uppercase font-weight-bold">Created</small><div>{{ $post->created_at?->format('d M Y, h:i A') ?? '—' }}</div></div>
                    <div class="col-md-6 mb-3"><small class="text-muted text-uppercase font-weight-bold">Updated</small><div>{{ $post->updated_at?->format('d M Y, h:i A') ?? '—' }}</div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white font-weight-bold"><i class="fas fa-align-left text-info mr-2"></i>Excerpt</div>
    <div class="card-body post-rich-content">{!! $post->excerpt ?: '<span class="text-muted">No excerpt.</span>' !!}</div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white font-weight-bold"><i class="fas fa-file-alt text-primary mr-2"></i>Body</div>
    <div class="card-body post-rich-content">{!! $post->body !!}</div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white font-weight-bold"><i class="fas fa-images text-primary mr-2"></i>Content Images ({{ $contentImages->count() }})</div>
    <div class="card-body">
        <div class="d-flex flex-wrap" style="gap:10px;">
            @forelse($contentImages as $media)
                <a href="{{ $media->getUrl() }}" target="_blank" rel="noopener" class="d-block border rounded p-1 bg-light">
                    <img src="{{ $media->getUrl() }}" alt="{{ $media->name }}" style="width:130px;height:90px;object-fit:cover;" class="rounded">
                </a>
            @empty
                <span class="text-muted">No content images.</span>
            @endforelse
        </div>
    </div>
</div>

<div class="text-right border-top pt-3 mt-4"><button type="button" class="btn btn-secondary font-weight-bold px-5" data-dismiss="modal">Close</button></div>
