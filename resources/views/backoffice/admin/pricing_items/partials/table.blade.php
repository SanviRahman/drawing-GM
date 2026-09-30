<div class="table-responsive">
    <table class="table table-hover border-bottom pricing-item-table mb-0 text-nowrap">
        <thead class="thead-light">
            <tr>
                <th style="width:40px;" class="text-center"><input type="checkbox" id="checkAll"></th>
                <th>Package</th>
                <th>Label</th>
                <th>Type</th>
                <th>Price</th>
                <th>Unit</th>
                <th class="text-center">Order</th>
                <th class="text-center">{{ ($isTrash ?? false) ? 'Deleted' : 'Status' }}</th>
                <th style="width:190px;" class="text-center">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($items as $item)
                <tr>
                    <td class="text-center"><input type="checkbox" class="row-checkbox" value="{{ $item->id }}"></td>
                    <td>
                        <strong>{{ $item->pricingPackage?->name ?? 'Deleted package' }}</strong>
                        @if($item->pricingPackage?->service)
                            <small class="text-muted d-block">{{ $item->pricingPackage->service->name }}</small>
                        @elseif($item->pricingPackage?->location)
                            <small class="text-muted d-block">{{ $item->pricingPackage->location->name }}</small>
                        @else
                            <small class="text-muted d-block">Global</small>
                        @endif
                    </td>
                    <td class="font-weight-bold">{{ $item->label }}</td>
                    <td><span class="badge badge-info">{{ $item->price_type_label }}</span></td>
                    <td>{{ $item->display_price }}</td>
                    <td>{{ $item->unit ?: '—' }}</td>
                    <td class="text-center">{{ $item->sort_order }}</td>

                    <td class="text-center">
                        @if($isTrash ?? false)
                            <span class="text-muted small">{{ $item->deleted_at?->format('d M Y') }}</span>
                        @else
                            <span class="badge badge-{{ $item->is_active ? 'success' : 'secondary' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span>
                        @endif
                    </td>

                    <td class="text-center">
                        @if($isTrash ?? false)
                            @can('pricing_item_restore')
                                <button type="button" class="btn btn-sm btn-outline-success btn-restore" data-url="{{ route('admin.pricing_items.restore', $item->id) }}" title="Restore"><i class="fas fa-undo"></i></button>
                            @endcan
                            @can('pricing_item_force_delete')
                                <button type="button" class="btn btn-sm btn-outline-danger btn-force-delete" data-url="{{ route('admin.pricing_items.force_delete', $item->id) }}" title="Permanent Delete"><i class="fas fa-trash-alt"></i></button>
                            @endcan
                        @else
                            @can('pricing_item_view')
                                <button type="button" class="btn btn-sm btn-outline-info btn-show" data-url="{{ route('admin.pricing_items.show', $item->id) }}" title="View"><i class="fas fa-eye"></i></button>
                            @endcan
                            @can('pricing_item_update')
                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-url="{{ route('admin.pricing_items.edit', $item->id) }}" title="Edit"><i class="fas fa-pen"></i></button>
                            @endcan
                            @can('pricing_item_toggle')
                                <button type="button" class="btn btn-sm btn-outline-warning btn-toggle" data-url="{{ route('admin.pricing_items.toggle', $item->id) }}" title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}"><i class="fas fa-{{ $item->is_active ? 'pause' : 'play' }}"></i></button>
                            @endcan
                            @can('pricing_item_duplicate')
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-duplicate" data-url="{{ route('admin.pricing_items.duplicate', $item->id) }}" title="Duplicate"><i class="fas fa-copy"></i></button>
                            @endcan
                            @can('pricing_item_delete')
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-url="{{ route('admin.pricing_items.destroy', $item->id) }}" title="Trash"><i class="fas fa-trash"></i></button>
                            @endcan
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center py-5"><div class="text-muted"><i class="fas fa-tags fa-3x mb-3 text-light"></i><h5 class="font-weight-bold">No pricing items found</h5><p class="mb-0 small">Create a pricing item or adjust your filters.</p></div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($items->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top bg-light">
        <div class="text-muted small font-weight-bold mb-2 mb-md-0">Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} entries</div>
        <div class="m-0 pagination-sm">{!! $items->appends(request()->query())->links('pagination::bootstrap-4') !!}</div>
    </div>
@endif
