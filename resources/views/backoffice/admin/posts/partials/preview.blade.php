@php
    $featuredUrl = $post->featured_image_url;
@endphp

<article class="mx-auto" style="max-width:900px;">
    <div class="text-center mb-4">
        <span class="badge badge-{{ $post->statusBadgeClass() }} mb-2">{{ $post->statusLabel() }}</span>
        <h2 class="font-weight-bold mb-2">{{ $post->title }}</h2>
        <div class="text-muted small">
            By {{ $post->author?->name ?? 'Unknown' }}
            @if($post->category) · {{ $post->category->name }} @endif
            @if($post->published_at) · {{ $post->published_at->format('d M Y') }} @endif
            @if($post->reading_minutes) · {{ $post->reading_minutes }} min read @endif
        </div>
    </div>

    @if($featuredUrl)
        <img src="{{ $featuredUrl }}" alt="{{ $post->title }}" class="img-fluid rounded shadow-sm w-100 mb-4" style="max-height:460px;object-fit:cover;">
    @endif

    @if($post->excerpt)
        <div class="lead post-rich-content border-left pl-3 mb-4">{!! $post->excerpt !!}</div>
    @endif

    <div class="post-rich-content">{!! $post->body !!}</div>

    <div class="alert alert-light border mt-4 mb-0 small text-muted">
        <i class="fas fa-eye mr-1"></i>This is an Admin preview. Public Blog routes/templates are a separate frontend implementation step.
    </div>
</article>

<div class="text-right border-top pt-3 mt-4"><button type="button" class="btn btn-secondary font-weight-bold px-5" data-dismiss="modal">Close Preview</button></div>
