@php
    $depth = (int) ($depth ?? 0);
    $marginLeft = ($depth * 26) . 'px';
@endphp

@forelse($roots as $item)
    <div class="d-flex align-items-center flex-wrap border rounded bg-white px-2 py-2 mb-2 shadow-sm" style="margin-left: {{ $marginLeft }};">

        <div class="flex-grow-1 text-truncate">
            @if($item->icon)
                <i class="{{ $item->icon }} text-primary mr-1"></i>
            @endif

            <span class="font-weight-bold text-dark">{{ $item->label }}</span>

            @if(! $item->is_active)
                <span class="badge badge-secondary ml-1">Hidden</span>
            @endif

            @if($item->link_type === 'linkable')
                <span class="badge badge-info ml-1" title="{{ $item->linkSummary() }}">Internal</span>
            @endif

            @if($item->target === '_blank')
                <span class="badge badge-dark ml-1">
                    <i class="fas fa-external-link-alt"></i>
                </span>
            @endif
        </div>

        <div class="text-muted small text-truncate mr-2" style="max-width: 180px;" title="{{ $item->linkSummary() }}">
            {{ $item->linkSummary() }}
        </div>

        <span class="badge badge-light text-secondary mr-2" title="Sort order">
            #{{ $item->sort_order }}
        </span>

        <div class="btn-group btn-group-sm" role="group">

            @can('menu_update')
                <button type="button" class="btn btn-outline-primary btn-item-edit shadow-sm" data-url="{{ route('admin.menus.item_edit', [$menu->id, $item->id]) }}" data-reload-modal="{{ route('admin.menus.show', $menu->id) }}" data-reload-title="Menu Details & Items" title="Edit Item">
                    <i class="fas fa-pen"></i>
                </button>
            @endcan

            @can('menu_delete')
                <button type="button" class="btn btn-outline-danger btn-delete shadow-sm" data-url="{{ route('admin.menus.item_destroy', [$menu->id, $item->id]) }}" data-reload-modal="{{ route('admin.menus.show', $menu->id) }}" data-reload-title="Menu Details & Items" title="Delete Item">
                    <i class="fas fa-trash"></i>
                </button>
            @endcan

        </div>

    </div>

    @php
        $children = $items->where('parent_id', $item->id);
    @endphp

    @if($children->isNotEmpty())
        @include('backoffice.admin.menus.partials.items', [
            'menu' => $menu,
            'items' => $items,
            'roots' => $children,
            'depth' => $depth + 1,
        ])
    @endif

@empty

    @if($depth === 0)
        <div class="text-center text-muted py-5">
            <i class="fas fa-list fa-3x mb-3 text-light"></i>
            <h6 class="font-weight-bold">No Menu Items Yet</h6>
            <p class="mb-0 small">Use the "Add Item" button to build this menu.</p>
        </div>
    @endif

@endforelse