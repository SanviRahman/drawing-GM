<div class="text-center mb-4">
    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center text-white mb-3 shadow"
        style="width:80px;height:80px;font-size:32px;font-weight:bold;">
        <i class="fas fa-photo-video"></i>
    </div>
    <h4 class="font-weight-bold text-dark mb-1">{{ $galleryItem->title ?? 'Untitled Item' }}</h4>
    <p class="text-muted mb-2"><i
            class="fas fa-images mr-2 text-primary"></i>{{ $galleryItem->gallery?->name ?? 'No Gallery' }}</p>
    <span
        class="badge badge-{{ $galleryItem->is_active?'success':'secondary' }} px-3 py-1 font-weight-bold shadow-sm">{{ $galleryItem->is_active?'Active':'Inactive' }}</span>
</div>

<div class="card bg-light border-0 p-4 shadow-sm mb-4">
    <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2"><i
            class="fas fa-info-circle mr-2 text-info"></i>Gallery Item Information</h6>
    <div class="row">
        <div class="col-md-6 mb-3">
            <small class="text-muted font-weight-bold text-uppercase">Gallery</small>
            <h6 class="font-weight-bold text-dark mb-0">{{ $galleryItem->gallery?->name ?? 'N/A' }}</h6>
        </div>
        <div class="col-md-6 mb-3">
            <small class="text-muted font-weight-bold text-uppercase">Item Type</small>
            <h6 class="font-weight-bold text-dark mb-0">{{ ucfirst($galleryItem->item_type) }}</h6>
        </div>
        <div class="col-md-6 mb-3">
            <small class="text-muted font-weight-bold text-uppercase">Sort Order</small>
            <h6 class="font-weight-bold text-dark mb-0">{{ $galleryItem->sort_order }}</h6>
        </div>
        <div class="col-md-6 mb-3">
            <small class="text-muted font-weight-bold text-uppercase">Status</small>
            <h6 class="font-weight-bold text-dark mb-0">{{ $galleryItem->is_active?'Active':'Inactive' }}</h6>
        </div>
        <div class="col-md-12 mb-3">
            <small class="text-muted font-weight-bold text-uppercase">Caption</small>
            <p class="text-dark mb-0">{{ $galleryItem->caption ?? 'No caption available.' }}</p>
        </div>
    </div>
</div>

<div class="card bg-light border-0 p-4 shadow-sm mb-4">
    <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2"><i class="fas fa-images mr-2 text-primary"></i>Media
        Preview</h6>

    <div class="row text-center">

        @if($galleryItem->hasMedia('image'))
        <div class="col-md-4 mb-3">
            <p class="font-weight-bold text-muted mb-2">Main Image</p>
            <img src="{{ $galleryItem->getFirstMediaUrl('image') }}" class="img-thumbnail shadow-sm"
                style="width:150px;height:150px;object-fit:cover;">
        </div>
        @endif

        @if($galleryItem->hasMedia('before'))
        <div class="col-md-4 mb-3">
            <p class="font-weight-bold text-muted mb-2">Before Image</p>
            <img src="{{ $galleryItem->getFirstMediaUrl('before') }}" class="img-thumbnail shadow-sm"
                style="width:150px;height:150px;object-fit:cover;">
        </div>
        @endif

        @if($galleryItem->hasMedia('after'))
        <div class="col-md-4 mb-3">
            <p class="font-weight-bold text-muted mb-2">After Image</p>
            <img src="{{ $galleryItem->getFirstMediaUrl('after') }}" class="img-thumbnail shadow-sm"
                style="width:150px;height:150px;object-fit:cover;">
        </div>
        @endif

        @if(!$galleryItem->hasMedia('image')&&!$galleryItem->hasMedia('before')&&!$galleryItem->hasMedia('after'))
        <div class="col-md-12">
            <span class="text-muted small"><i class="fas fa-image mr-1"></i>No media uploaded.</span>
        </div>
        @endif

    </div>
</div>

<div class="card bg-light border-0 p-4 shadow-sm mb-4">
    <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2"><i
            class="fas fa-calendar-alt mr-2 text-primary"></i>Date Information</h6>
    <div class="row text-center">
        <div class="col-6 border-right">
            <p class="text-muted small text-uppercase font-weight-bold mb-1">Created At</p>
            <h6 class="text-dark font-weight-bold mb-0">{{ $galleryItem->created_at->format('d M, Y') }}</h6>
            <small class="text-muted">{{ $galleryItem->created_at->format('h:i A') }}</small>
        </div>
        <div class="col-6">
            <p class="text-muted small text-uppercase font-weight-bold mb-1">Updated At</p>
            <h6 class="text-dark font-weight-bold mb-0">{{ $galleryItem->updated_at->format('d M, Y') }}</h6>
            <small class="text-muted">{{ $galleryItem->updated_at->format('h:i A') }}</small>
        </div>
    </div>
</div>

<div class="text-right border-top pt-3 mt-4 bg-white rounded-bottom">
    <button type="button" class="btn btn-secondary font-weight-bold px-5 shadow-sm" data-dismiss="modal">Close</button>
</div>