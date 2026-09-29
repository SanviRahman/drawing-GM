<div class="row">
    <div class="col-md-5 mb-3 mb-md-0">
        <div class="border rounded bg-light d-flex align-items-center justify-content-center" style="min-height:240px;overflow:hidden;">
            @if($data['type'] === 'image' && $data['url'])
                <img src="{{ $data['url'] }}" alt="{{ $data['alt_text'] ?: $media->name }}" style="max-width:100%;max-height:320px;object-fit:contain;">
            @elseif($data['type'] === 'video' && $data['url'])
                <video controls preload="metadata" style="max-width:100%;max-height:320px;"><source src="{{ $data['url'] }}" type="{{ $media->mime_type }}"></video>
            @else
                <i class="fas fa-file fa-5x text-secondary"></i>
            @endif
        </div>
    </div>
    <div class="col-md-7">
        <dl class="row mb-0">
            <dt class="col-sm-4">Name</dt><dd class="col-sm-8">{{ $media->name }}</dd>
            <dt class="col-sm-4">File</dt><dd class="col-sm-8 text-break">{{ $media->file_name }}</dd>
            <dt class="col-sm-4">Owner</dt><dd class="col-sm-8">{{ $data['owner_label'] }}</dd>
            <dt class="col-sm-4">Collection</dt><dd class="col-sm-8"><code>{{ $media->collection_name }}</code></dd>
            <dt class="col-sm-4">MIME</dt><dd class="col-sm-8">{{ $media->mime_type ?: '—' }}</dd>
            <dt class="col-sm-4">Disk</dt><dd class="col-sm-8">{{ $media->disk }}</dd>
            <dt class="col-sm-4">Size</dt><dd class="col-sm-8">{{ $data['size_human'] }}</dd>
            <dt class="col-sm-4">Alt Text</dt><dd class="col-sm-8">{{ $data['alt_text'] ?: '—' }}</dd>
            <dt class="col-sm-4">Caption</dt><dd class="col-sm-8">{{ $data['caption'] ?: '—' }}</dd>
        </dl>
    </div>
</div>
