<div class="text-center mb-4">
    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center text-white mb-3 shadow" style="width:80px;height:80px;font-size:32px;font-weight:bold;">
        <i class="fas fa-images"></i>
    </div>

    <h4 class="font-weight-bold text-dark mb-1">{{ $gallery->name }}</h4>
    <p class="text-muted mb-2"><i class="fas fa-th-large mr-2 text-primary"></i>{{ ucfirst($gallery->layout) }}</p>

    <span class="badge badge-{{ $gallery->is_active ? 'success' : 'secondary' }} px-3 py-1 font-weight-bold shadow-sm">
        {{ $gallery->is_active ? 'Active' : 'Inactive' }}
    </span>
</div>


<div class="card bg-light border-0 p-4 shadow-sm mb-4">
    <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2"><i class="fas fa-info-circle mr-2 text-info"></i>Gallery Information</h6>

    <div class="row">
        <div class="col-md-6 mb-3">
            <small class="text-muted font-weight-bold text-uppercase">Gallery Name</small>
            <h6 class="font-weight-bold text-dark mb-0">{{ $gallery->name }}</h6>
        </div>

        <div class="col-md-6 mb-3">
            <small class="text-muted font-weight-bold text-uppercase">Layout</small>
            <h6 class="font-weight-bold text-dark mb-0">{{ ucfirst($gallery->layout) }}</h6>
        </div>

        <div class="col-md-6 mb-3">
            <small class="text-muted font-weight-bold text-uppercase">Total Items</small>
            <h6 class="font-weight-bold text-dark mb-0">{{ $gallery->items->count() }}</h6>
        </div>

        <div class="col-md-6 mb-3">
            <small class="text-muted font-weight-bold text-uppercase">Status</small>
            <h6 class="font-weight-bold text-dark mb-0">{{ $gallery->is_active ? 'Active' : 'Inactive' }}</h6>
        </div>
    </div>
</div>


<div class="card bg-light border-0 p-4 shadow-sm mb-4">
    <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2"><i class="fas fa-calendar-alt mr-2 text-primary"></i>Date Information</h6>

    <div class="row text-center">
        <div class="col-6 border-right">
            <p class="text-muted small text-uppercase font-weight-bold mb-1">Created At</p>
            <h6 class="text-dark font-weight-bold mb-0">{{ $gallery->created_at->format('d M, Y') }}</h6>
            <small class="text-muted">{{ $gallery->created_at->format('h:i A') }}</small>
        </div>

        <div class="col-6">
            <p class="text-muted small text-uppercase font-weight-bold mb-1">Updated At</p>
            <h6 class="text-dark font-weight-bold mb-0">{{ $gallery->updated_at->format('d M, Y') }}</h6>
            <small class="text-muted">{{ $gallery->updated_at->format('h:i A') }}</small>
        </div>
    </div>
</div>


<div class="text-right border-top pt-3 mt-4 bg-white rounded-bottom">
    <button type="button" class="btn btn-secondary font-weight-bold px-5 shadow-sm" data-dismiss="modal">Close</button>
</div>