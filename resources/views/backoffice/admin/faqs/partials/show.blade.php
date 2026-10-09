<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="font-weight-bold text-dark mb-0">Question</h5>
        <span class="badge badge-{{ $faq->is_active ? 'success' : 'secondary' }} px-3 py-2">{{ $faq->is_active ? 'Active' : 'Inactive' }}</span>
    </div>
    <div class="border rounded bg-light p-3 faq-rich-content">{!! $faq->question !!}</div>
</div>

<div class="mb-4">
    <h5 class="font-weight-bold text-dark mb-2">Answer</h5>
    <div class="border rounded p-3 faq-rich-content">{!! $faq->answer !!}</div>
</div>

<div class="card bg-light border-0 shadow-sm mb-3">
    <div class="card-body">
        <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3"><i class="fas fa-link mr-1"></i>Mapped Targets</h6>
        <div class="mb-3">
            <div class="font-weight-bold mb-2">Primary Navbar</div>
            @forelse($faq->menuItems as $menuItem)
                <span class="badge badge-dark mr-1 mb-1">{{ $menuItem->label }} #{{ $menuItem->pivot->sort_order }}</span>
            @empty
                <span class="text-muted small">No navbar section mapping</span>
            @endforelse
        </div>
        <div class="row">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="font-weight-bold mb-2">Pages</div>
                @forelse($faq->pages as $page)
                    <span class="badge badge-primary mr-1 mb-1">{{ $page->title }} #{{ $page->pivot->sort_order }}</span>
                @empty
                    <span class="text-muted small">No page mapping</span>
                @endforelse
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="font-weight-bold mb-2">Services</div>
                @forelse($faq->services as $service)
                    <span class="badge badge-info mr-1 mb-1">{{ $service->name }} #{{ $service->pivot->sort_order }}</span>
                @empty
                    <span class="text-muted small">No service mapping</span>
                @endforelse
            </div>
            <div class="col-md-4">
                <div class="font-weight-bold mb-2">Locations</div>
                @forelse($faq->locations as $location)
                    <span class="badge badge-warning mr-1 mb-1">{{ $location->name }} #{{ $location->pivot->sort_order }}</span>
                @empty
                    <span class="text-muted small">No location mapping</span>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row text-center bg-light rounded py-3 shadow-sm mx-0">
    <div class="col-6 border-right">
        <p class="text-muted small text-uppercase font-weight-bold mb-1">Created</p>
        <h6 class="font-weight-bold mb-0">{{ $faq->created_at->format('d M, Y') }}</h6>
        <small class="text-muted">{{ $faq->created_at->format('h:i A') }}</small>
    </div>
    <div class="col-6">
        <p class="text-muted small text-uppercase font-weight-bold mb-1">Updated</p>
        <h6 class="font-weight-bold mb-0">{{ $faq->updated_at->format('d M, Y') }}</h6>
        <small class="text-muted">{{ $faq->updated_at->format('h:i A') }}</small>
    </div>
</div>

<div class="text-right border-top pt-3 mt-4">
    <button type="button" class="btn btn-secondary font-weight-bold px-5" data-dismiss="modal">Close</button>
</div>
