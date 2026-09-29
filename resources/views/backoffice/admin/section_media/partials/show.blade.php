@php
    $media = $sectionMedia->media;
    $mediaUrl = null;
    $mime = (string) ($media?->mime_type ?? '');

    if ($media && ! $media->trashed()) {
        try {
            $mediaUrl = $media->getUrl();
        } catch (\Throwable $exception) {
            $mediaUrl = null;
        }
    }
@endphp

<div class="row">
    <div class="col-md-5">
        <div class="card border-0 bg-light h-100">
            <div class="card-body">
                <div class="border rounded bg-white d-flex align-items-center justify-content-center overflow-hidden mb-3" style="height:220px;">
                    @if($media && str_starts_with($mime, 'image/') && $mediaUrl)
                        <img src="{{ $mediaUrl }}" alt="{{ $media->name }}" style="width:100%;height:100%;object-fit:contain;">
                    @elseif($media && str_starts_with($mime, 'video/'))
                        <i class="fas fa-video fa-4x text-warning"></i>
                    @else
                        <i class="fas fa-file-alt fa-4x text-secondary"></i>
                    @endif
                </div>

                <dl class="row mb-0">
                    <dt class="col-sm-4">Media</dt><dd class="col-sm-8">{{ $media?->name ?? 'Deleted media' }}</dd>
                    <dt class="col-sm-4">File</dt><dd class="col-sm-8">{{ $media?->file_name ?? '-' }}</dd>
                    <dt class="col-sm-4">MIME</dt><dd class="col-sm-8">{{ $mime ?: '-' }}</dd>
                    <dt class="col-sm-4">Role</dt><dd class="col-sm-8"><code>{{ $sectionMedia->role }}</code></dd>
                    <dt class="col-sm-4">Order</dt><dd class="col-sm-8">{{ $sectionMedia->sort_order }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-7 mt-3 mt-md-0">
        <h6 class="font-weight-bold text-uppercase text-muted">Page</h6>
        <div class="border rounded p-3 bg-white mb-3">{{ $sectionMedia->pageSection?->page?->title ?? 'Deleted page' }}</div>

        <h6 class="font-weight-bold text-uppercase text-muted">Page Section</h6>
        <div class="border rounded p-3 bg-white mb-3"><strong>#{{ $sectionMedia->page_section_id }}</strong> — {{ $sectionMedia->pageSection?->sectionDefinition?->name ?? 'Deleted definition' }}@if($sectionMedia->pageSection?->heading)<div class="text-muted small mt-1">{{ $sectionMedia->pageSection->heading }}</div>@endif</div>

        <h6 class="font-weight-bold text-uppercase text-muted">Caption Override</h6>
        <div class="border rounded p-3 bg-white">{!! $sectionMedia->caption_override ?: '<span class="text-muted">No caption override.</span>' !!}</div>

        @if($media?->trashed())
            <div class="alert alert-warning mt-3 mb-0"><i class="fas fa-exclamation-triangle mr-1"></i>The referenced media record is currently in Trash.</div>
        @endif
    </div>
</div>
