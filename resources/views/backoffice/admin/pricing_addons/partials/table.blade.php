@php
    $isTrash = $isTrash ?? false;
@endphp
<div class="table-responsive"><table class="table table-hover border-bottom pricing-addon-table mb-0 text-nowrap"><thead class="thead-light"><tr><th style="width:40px;" class="text-center"><input type="checkbox" id="checkAll"></th><th style="width:70px;">Image</th><th>Name</th><th>Price Type</th><th>Price</th><th>Status</th><th>Order</th><th style="width:190px;" class="text-right">Actions</th></tr></thead><tbody>
@forelse($addons as $addon)
    @php
        $imageUrl = $addon->getFirstMediaUrl(\App\Models\PricingAddon::MEDIA_COLLECTION);
        $descriptionPreview = \Illuminate\Support\Str::limit(trim(strip_tags((string) $addon->description)), 65);
    @endphp
    <tr><td class="text-center"><input type="checkbox" class="row-checkbox" value="{{ $addon->id }}"></td><td>
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $addon->name }}" class="rounded border pricing-addon-thumb">
        @else
            <div class="rounded border bg-light text-muted pricing-addon-empty-thumb"><i class="far fa-image"></i></div>
        @endif
    </td><td><strong class="d-block">{{ $addon->name }}</strong>@if($descriptionPreview)<small class="text-muted">{{ $descriptionPreview }}</small>@endif</td><td><span class="badge badge-info px-2 py-1">{{ $addon->price_type_label }}</span></td><td><strong>{{ $addon->display_price }}</strong></td><td><span class="badge badge-{{ $addon->is_active ? 'success' : 'secondary' }} px-2 py-1">{{ $addon->is_active ? 'Active' : 'Inactive' }}</span></td><td>{{ $addon->sort_order }}</td><td class="text-right">
        @if($isTrash)
            @can('pricing_addon_restore')
                <button type="button" class="btn btn-sm btn-outline-success btn-restore" data-url="{{ route('admin.pricing_addons.restore', $addon->id) }}" title="Restore"><i class="fas fa-undo"></i></button>
            @endcan
            @can('pricing_addon_force_delete')
                <button type="button" class="btn btn-sm btn-outline-danger btn-force-delete" data-url="{{ route('admin.pricing_addons.force_delete', $addon->id) }}" title="Delete Permanently"><i class="fas fa-times"></i></button>
            @endcan
        @else
            @can('pricing_addon_view')
                <button type="button" class="btn btn-sm btn-outline-info btn-show" data-url="{{ route('admin.pricing_addons.show', $addon->id) }}" title="View"><i class="fas fa-eye"></i></button>
            @endcan
            @can('pricing_addon_update')
                <button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-url="{{ route('admin.pricing_addons.edit', $addon->id) }}" title="Edit"><i class="fas fa-pen"></i></button>
            @endcan
            @can('pricing_addon_toggle')
                <button type="button" class="btn btn-sm btn-outline-warning btn-toggle" data-url="{{ route('admin.pricing_addons.toggle', $addon->id) }}" title="Toggle"><i class="fas fa-power-off"></i></button>
            @endcan
            @can('pricing_addon_duplicate')
                <button type="button" class="btn btn-sm btn-outline-secondary btn-duplicate" data-url="{{ route('admin.pricing_addons.duplicate', $addon->id) }}" title="Duplicate"><i class="fas fa-copy"></i></button>
            @endcan
            @can('pricing_addon_delete')
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-url="{{ route('admin.pricing_addons.destroy', $addon->id) }}" title="Trash"><i class="fas fa-trash"></i></button>
            @endcan
        @endif
    </td></tr>
@empty
    <tr><td colspan="8" class="text-center py-5"><div class="text-muted"><i class="fas fa-puzzle-piece fa-3x mb-3 text-light"></i><h5 class="font-weight-bold">No pricing add-ons found</h5><p class="mb-0 small">{{ $isTrash ? 'Trash is empty or no records match the filters.' : 'Create an add-on or adjust your filters.' }}</p></div></td></tr>
@endforelse
</tbody></table></div>
@if($addons->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top bg-light"><div class="text-muted small font-weight-bold mb-2 mb-md-0">Showing {{ $addons->firstItem() ?? 0 }} to {{ $addons->lastItem() ?? 0 }} of {{ $addons->total() }} entries</div><div class="m-0 pagination-sm">{!! $addons->appends(request()->query())->links('pagination::bootstrap-4') !!}</div></div>
@endif
