@php
    $isTrash = $isTrash ?? false;
@endphp

<div class="table-responsive">
    <table class="table table-hover border-bottom pricing-package-addon-table mb-0 text-nowrap">
        <thead class="thead-light">
            <tr>
                <th style="width:40px;" class="text-center"><input type="checkbox" id="checkAll"></th>
                <th style="width:70px;">Image</th>
                <th>Pricing Package</th>
                <th>Pricing Add-on</th>
                <th>Override Data</th>
                <th>Created</th>
                <th style="width:150px;" class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($mappings as $mapping)
            @php
                $package = $mapping->pricingPackage;
                $addon = $mapping->pricingAddon;
                $imageUrl = $addon?->getFirstMediaUrl(\App\Models\PricingAddon::MEDIA_COLLECTION) ?: '';
                $scope = collect([
                    $package?->service?->name,
                    $package?->location?->name,
                ])->filter()->implode(' / ');
                $overridePreview = $mapping->override_data
                    ? json_encode($mapping->override_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                    : null;
            @endphp

            <tr>
                <td class="text-center">
                    <input type="checkbox" class="row-checkbox" value="{{ $mapping->id }}">
                </td>

                <td>
                    @if($imageUrl)
                        <img src="{{ $imageUrl }}"
                             alt="{{ $addon?->name ?? 'Pricing add-on' }}"
                             class="rounded border pricing-package-addon-thumb">
                    @else
                        <div class="rounded border bg-light text-muted pricing-package-addon-empty-thumb">
                            <i class="far fa-image"></i>
                        </div>
                    @endif
                </td>

                <td>
                    <strong class="d-block">{{ $package?->name ?? 'Deleted package' }}</strong>
                    @if($scope)
                        <small class="text-muted">{{ $scope }}</small>
                    @endif
                    @if($package?->trashed())
                        <span class="badge badge-danger ml-1">Package Trashed</span>
                    @endif
                </td>

                <td>
                    <strong class="d-block">{{ $addon?->name ?? 'Deleted add-on' }}</strong>
                    @if($addon)
                        <small class="text-muted">{{ $addon->display_price }}</small>
                    @endif
                    @if($addon?->trashed())
                        <span class="badge badge-danger ml-1">Add-on Trashed</span>
                    @endif
                </td>

                <td>
                    @if($overridePreview)
                        <code class="override-preview d-block">{{ \Illuminate\Support\Str::limit($overridePreview, 100) }}</code>
                    @else
                        <span class="text-muted small">Uses add-on defaults</span>
                    @endif
                </td>

                <td>
                    <small class="text-muted">{{ $mapping->created_at?->format('d M Y, h:i A') ?? '—' }}</small>
                </td>

                <td class="text-right">
                    @if($isTrash)
                        @can('pricing_package_addon_restore')
                            <button type="button"
                                    class="btn btn-sm btn-outline-success btn-restore"
                                    data-url="{{ route('admin.pricing_package_addons.restore', $mapping->id) }}"
                                    title="Restore">
                                <i class="fas fa-undo"></i>
                            </button>
                        @endcan

                        @can('pricing_package_addon_force_delete')
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-force-delete"
                                    data-url="{{ route('admin.pricing_package_addons.force_delete', $mapping->id) }}"
                                    title="Delete Permanently">
                                <i class="fas fa-times"></i>
                            </button>
                        @endcan
                    @else
                        @can('pricing_package_addon_view')
                            <button type="button"
                                    class="btn btn-sm btn-outline-info btn-show"
                                    data-url="{{ route('admin.pricing_package_addons.show', $mapping->id) }}"
                                    title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                        @endcan

                        @can('pricing_package_addon_update')
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-edit"
                                    data-url="{{ route('admin.pricing_package_addons.edit', $mapping->id) }}"
                                    title="Edit">
                                <i class="fas fa-pen"></i>
                            </button>
                        @endcan

                        @can('pricing_package_addon_delete')
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-delete"
                                    data-url="{{ route('admin.pricing_package_addons.destroy', $mapping->id) }}"
                                    title="Trash">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endcan
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-link fa-3x mb-3 text-light"></i>
                        <h5 class="font-weight-bold">No package add-on mappings found</h5>
                        <p class="mb-0 small">
                            {{ $isTrash ? 'Trash is empty or no records match the filters.' : 'Attach an add-on to a pricing package or adjust your filters.' }}
                        </p>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($mappings->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top bg-light">
        <div class="text-muted small font-weight-bold mb-2 mb-md-0">
            Showing {{ $mappings->firstItem() ?? 0 }} to {{ $mappings->lastItem() ?? 0 }} of {{ $mappings->total() }} entries
        </div>
        <div class="m-0 pagination-sm">
            {!! $mappings->appends(request()->query())->links('pagination::bootstrap-4') !!}
        </div>
    </div>
@endif
