@php
    $items = $menu->items->sortBy([['sort_order', 'asc'], ['id', 'asc']])->values();
    $roots = $items->whereNull('parent_id');
@endphp

<div class="text-center mb-4">
    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center text-white mb-3 shadow" style="width: 80px; height: 80px; font-size: 32px;">
        <i class="fas fa-sitemap"></i>
    </div>

    <h4 class="font-weight-bold text-dark mb-1">{{ $menu->name }}</h4>
    <p class="text-muted mb-2"><i class="fas fa-map-marker-alt mr-2 text-primary"></i>{{ $menu->location }}</p>

    <span class="badge badge-{{ $menu->is_active ? 'success' : 'secondary' }} px-3 py-1 font-weight-bold shadow-sm mr-1">
        {{ $menu->is_active ? 'Active' : 'Inactive' }}
    </span>
    <span class="badge badge-info px-3 py-1 font-weight-bold shadow-sm">
        {{ $items->count() }} Item(s)
    </span>
</div>

<div class="card bg-light border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center px-3 py-2">
        <h6 class="font-weight-bold text-dark mb-0">
            <i class="fas fa-list mr-2 text-info"></i>Menu Items
        </h6>

        @can('menu_create')
            <button type="button"
                    class="btn btn-primary btn-sm font-weight-bold shadow-sm btn-item-add"
                    data-url="{{ route('admin.menus.item_create', $menu->id) }}"
                    data-reload-modal="{{ route('admin.menus.show', $menu->id) }}"
                    data-reload-title="Menu Details & Items">
                <i class="fas fa-plus mr-1"></i>Add Item
            </button>
        @endcan
    </div>

    <div class="card-body p-2" style="max-height: 340px; overflow-y: auto;">
        @include('backoffice.admin.menus.partials.items', [
            'menu' => $menu,
            'items' => $items,
            'roots' => $roots,
            'depth' => 0,
        ])
    </div>
</div>

<div class="row text-center mt-3 bg-light rounded py-3 shadow-sm mx-0">
    <div class="col-6 border-right">
        <p class="text-muted small text-uppercase font-weight-bold mb-1"><i class="fas fa-calendar-plus mr-1"></i>Created</p>
        <h6 class="text-dark font-weight-bold mb-0">{{ $menu->created_at?->format('d M, Y') }}</h6>
        <small class="text-muted">{{ $menu->created_at?->format('h:i A') }}</small>
    </div>
    <div class="col-6">
        <p class="text-muted small text-uppercase font-weight-bold mb-1"><i class="fas fa-edit mr-1"></i>Last Updated</p>
        <h6 class="text-dark font-weight-bold mb-0">{{ $menu->updated_at?->format('d M, Y') }}</h6>
        <small class="text-muted">{{ $menu->updated_at?->format('h:i A') }}</small>
    </div>
</div>

<div class="text-right border-top pt-3 mt-4 bg-white rounded-bottom">
    <button type="button" class="btn btn-secondary font-weight-bold px-5 shadow-sm" data-dismiss="modal">Close</button>
</div>
