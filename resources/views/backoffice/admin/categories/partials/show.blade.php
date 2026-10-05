<div class="text-center mb-4">
    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center text-white mb-3 shadow"
         style="width: 80px; height: 80px; font-size: 30px; font-weight: bold;">
        <i class="fas fa-folder-open"></i>
    </div>

    <h4 class="font-weight-bold text-dark mb-1">{{ $category->name }}</h4>
    <p class="text-muted mb-2">
        <i class="fas fa-link mr-2 text-primary"></i>{{ $category->slug }}
    </p>
    <span class="badge badge-{{ $category->is_active ? 'success' : 'secondary' }} px-3 py-1 font-weight-bold shadow-sm">
        {{ $category->is_active ? 'Active' : 'Inactive' }}
    </span>
</div>

<div class="card bg-light border-0 p-4 shadow-sm mb-4">
    <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">
        <i class="fas fa-align-left mr-2 text-info"></i>Description
    </h6>

    @if($category->description)
        <div class="text-dark category-rich-content">{!! $category->description !!}</div>
    @else
        <span class="text-muted small font-italic">
            <i class="fas fa-info-circle mr-1"></i>No description added.
        </span>
    @endif
</div>

<div class="row text-center mt-3 bg-light rounded py-3 shadow-sm mx-0">
    <div class="col-6 border-right">
        <p class="text-muted small text-uppercase font-weight-bold mb-1">
            <i class="fas fa-calendar-plus mr-1"></i>Created
        </p>
        <h6 class="text-dark font-weight-bold mb-0">{{ $category->created_at?->format('d M, Y') ?? '—' }}</h6>
        <small class="text-muted">{{ $category->created_at?->format('h:i A') ?? '' }}</small>
    </div>

    <div class="col-6">
        <p class="text-muted small text-uppercase font-weight-bold mb-1">
            <i class="fas fa-edit mr-1"></i>Last Updated
        </p>
        <h6 class="text-dark font-weight-bold mb-0">{{ $category->updated_at?->format('d M, Y') ?? '—' }}</h6>
        <small class="text-muted">{{ $category->updated_at?->format('h:i A') ?? '' }}</small>
    </div>
</div>

<div class="text-right border-top pt-3 mt-4 bg-white rounded-bottom">
    <button type="button" class="btn btn-secondary font-weight-bold px-5 shadow-sm" data-dismiss="modal">Close</button>
</div>
