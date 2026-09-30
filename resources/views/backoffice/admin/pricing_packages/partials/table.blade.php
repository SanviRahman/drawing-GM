<div class="table-responsive"><table class="table table-hover border-bottom pricing-package-table mb-0 text-nowrap"><thead class="thead-light"><tr><th style="width:40px;" class="text-center"><input type="checkbox" id="checkAll"></th><th>Package</th><th>Service</th><th>Location</th><th>Currency</th><th class="text-center">Featured</th><th class="text-center">Status</th><th class="text-center">Order</th><th style="width:190px;" class="text-center">Actions</th></tr></thead><tbody>
@forelse($packages as $package)
<tr><td class="text-center"><input type="checkbox" class="row-checkbox" value="{{ $package->id }}"></td><td><strong>{{ $package->name }}</strong>
    @if($package->subtitle)
        <small class="d-block text-muted">{{ $package->subtitle }}</small>
    @endif
</td><td>{{ $package->service?->name ?? 'Global' }}</td><td>{{ $package->location?->name ?? 'Global' }}</td><td>{{ $package->currency }}</td><td class="text-center"><span class="badge badge-{{ $package->is_featured ? 'warning' : 'light' }}">{{ $package->is_featured ? 'Yes' : 'No' }}</span></td><td class="text-center"><span class="badge badge-{{ $package->is_active ? 'success' : 'secondary' }}">{{ $package->is_active ? 'Active' : 'Inactive' }}</span></td><td class="text-center">{{ $package->sort_order }}</td><td class="text-center">
    @if($isTrash ?? false)
        @can('pricing_package_restore')
            <button type="button" class="btn btn-sm btn-outline-success btn-restore" data-url="{{ route('admin.pricing_packages.restore', $package->id) }}" title="Restore"><i class="fas fa-undo"></i></button>
        @endcan
        @can('pricing_package_force_delete')
            <button type="button" class="btn btn-sm btn-outline-danger btn-force-delete" data-url="{{ route('admin.pricing_packages.force_delete', $package->id) }}" title="Permanent Delete"><i class="fas fa-trash-alt"></i></button>
        @endcan
    @else
        @can('pricing_package_view')
            <button type="button" class="btn btn-sm btn-outline-info btn-show" data-url="{{ route('admin.pricing_packages.show', $package->id) }}" title="View"><i class="fas fa-eye"></i></button>
        @endcan
        @can('pricing_package_update')
            <button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-url="{{ route('admin.pricing_packages.edit', $package->id) }}" title="Edit"><i class="fas fa-pen"></i></button>
        @endcan
        @can('pricing_package_toggle')
            <button type="button" class="btn btn-sm btn-outline-warning btn-toggle" data-url="{{ route('admin.pricing_packages.toggle', $package->id) }}" title="Toggle"><i class="fas fa-power-off"></i></button>
        @endcan
        @can('pricing_package_duplicate')
            <button type="button" class="btn btn-sm btn-outline-secondary btn-duplicate" data-url="{{ route('admin.pricing_packages.duplicate', $package->id) }}" title="Duplicate"><i class="fas fa-copy"></i></button>
        @endcan
        @can('pricing_package_delete')
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-url="{{ route('admin.pricing_packages.destroy', $package->id) }}" title="Trash"><i class="fas fa-trash"></i></button>
        @endcan
    @endif
</td></tr>
@empty
<tr><td colspan="9" class="text-center py-5"><div class="text-muted"><i class="fas fa-tags fa-3x mb-3 text-light"></i><h5 class="font-weight-bold">No pricing packages found</h5><p class="mb-0 small">Create a package or adjust your filters.</p></div></td></tr>
@endforelse
</tbody></table></div>
@if($packages->hasPages())
<div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top bg-light"><div class="text-muted small font-weight-bold mb-2 mb-md-0">Showing {{ $packages->firstItem() ?? 0 }} to {{ $packages->lastItem() ?? 0 }} of {{ $packages->total() }} entries</div><div class="m-0 pagination-sm">{!! $packages->appends(request()->query())->links('pagination::bootstrap-4') !!}</div></div>
@endif
