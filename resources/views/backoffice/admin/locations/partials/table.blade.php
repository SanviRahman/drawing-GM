@php
    $isTrash = $isTrash ?? false;
    $admin = auth('admin')->user();
    $canView = $admin?->can('location_view') ?? false;
    $canUpdate = $admin?->can('location_update') ?? false;
    $canDelete = $admin?->can('location_delete') ?? false;
    $canPublish = $admin?->can('location_publish') ?? false;
    $canUnpublish = $admin?->can('location_unpublish') ?? false;
    $canDuplicate = $admin?->can('location_duplicate') ?? false;
    $canRestore = $admin?->can('location_restore') ?? false;
    $canForceDelete = $admin?->can('location_force_delete') ?? false;
@endphp

<div class="table-responsive">
    <table class="table table-hover mb-0 location-table">
        <thead class="thead-light"><tr><th class="text-center" style="width:45px;"><input type="checkbox" id="checkAll" aria-label="Select all"></th><th style="width:90px;">Preview</th><th>Location</th><th style="width:150px;">Region</th><th style="width:105px;">Status</th><th style="width:80px;">Order</th><th style="width:130px;">Published</th><th class="text-right" style="width:190px;">Actions</th></tr></thead>
        <tbody>
            @if($locations->count() > 0)
                @foreach($locations as $item)
                    @php
                        $previewUrl = null;
                        try {
                            $previewUrl = $item->getFirstMediaUrl('hero_desktop') ?: $item->getFirstMediaUrl('hero_mobile');
                        } catch (\Throwable) {
                            $previewUrl = null;
                        }
                    @endphp

                    <tr>
                        <td class="text-center"><input type="checkbox" class="row-checkbox" value="{{ $item->id }}" aria-label="Select {{ $item->name }}"></td>

                        <td>
                            <div class="border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width:64px;height:54px;">
                                @if($previewUrl)
                                    <img src="{{ $previewUrl }}" alt="{{ $item->name }}" class="location-thumb" loading="lazy">
                                @else
                                    <i class="fas fa-map-marker-alt fa-lg text-muted"></i>
                                @endif
                            </div>
                        </td>

                        <td class="location-title-cell">
                            <div class="font-weight-bold text-dark">{{ $item->name }}</div>
                            <small class="text-muted">/{{ $item->slug }}</small>
                            @if($item->summary)
                                <small class="text-muted d-block mt-1">{{ \Illuminate\Support\Str::limit(strip_tags($item->summary), 80) }}</small>
                            @endif
                        </td>

                        <td>
                            <span class="badge badge-light border">{{ $item->region ?: '—' }}</span>
                            @if(is_array($item->postal_codes) && count($item->postal_codes) > 0)
                                <small class="text-muted d-block mt-1">{{ count($item->postal_codes) }} postal code{{ count($item->postal_codes) === 1 ? '' : 's' }}</small>
                            @endif
                        </td>

                        <td>
                            <span class="badge badge-{{ $item->statusBadgeClass() }}">{{ $item->statusLabel() }}</span>
                            @if($isTrash && $item->deleted_at)
                                <small class="text-danger d-block mt-1">{{ $item->deleted_at->diffForHumans() }}</small>
                            @endif
                        </td>

                        <td><span class="font-weight-bold">{{ $item->sort_order }}</span></td>

                        <td>
                            @if($item->published_at)
                                <small class="text-dark d-block">{{ $item->published_at->format('d M Y') }}</small>
                                <small class="text-muted">{{ $item->published_at->format('h:i A') }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td class="text-right text-nowrap">
                            <div class="btn-group btn-group-sm" role="group">
                                @if(!$isTrash)
                                    @if($canView)
                                        <button type="button" class="btn btn-outline-info btn-show" data-url="{{ route('admin.locations.show', $item->id) }}" title="View"><i class="fas fa-eye"></i></button>
                                    @endif

                                    @if($canUpdate)
                                        <button type="button" class="btn btn-outline-primary btn-edit" data-url="{{ route('admin.locations.edit', $item->id) }}" title="Edit"><i class="fas fa-pen"></i></button>
                                    @endif

                                    @if($item->status === 'published' && $canUnpublish)
                                        <button type="button" class="btn btn-outline-warning btn-status-action" data-url="{{ route('admin.locations.unpublish', $item->id) }}" data-label="Move to Draft" title="Move to Draft"><i class="fas fa-pause"></i></button>
                                    @elseif($item->status !== 'published' && $canPublish)
                                        <button type="button" class="btn btn-outline-success btn-status-action" data-url="{{ route('admin.locations.publish', $item->id) }}" data-label="Publish" title="Publish"><i class="fas fa-play"></i></button>
                                    @endif

                                    @if($canDuplicate)
                                        <button type="button" class="btn btn-outline-secondary btn-duplicate" data-url="{{ route('admin.locations.duplicate', $item->id) }}" title="Duplicate"><i class="fas fa-copy"></i></button>
                                    @endif

                                    @if($canDelete)
                                        <button type="button" class="btn btn-outline-danger btn-delete" data-url="{{ route('admin.locations.destroy', $item->id) }}" title="Move to trash"><i class="fas fa-trash"></i></button>
                                    @endif
                                @else
                                    @if($canRestore)
                                        <button type="button" class="btn btn-outline-success btn-restore" data-url="{{ route('admin.locations.restore', $item->id) }}" title="Restore"><i class="fas fa-undo-alt"></i></button>
                                    @endif

                                    @if($canForceDelete)
                                        <button type="button" class="btn btn-outline-danger btn-force-delete" data-url="{{ route('admin.locations.force_delete', $item->id) }}" title="Permanently delete"><i class="fas fa-fire-alt"></i></button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr><td colspan="8"><div class="text-center py-5"><div class="mb-3"><span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width:72px;height:72px;"><i class="fas fa-map-marker-alt fa-2x text-muted"></i></span></div><h5 class="text-muted mb-1">{{ $isTrash ? 'Trash bin is empty' : 'No locations found' }}</h5><p class="text-muted small mb-0">{{ $isTrash ? 'Deleted locations will appear here.' : 'Create a location or adjust your filters.' }}</p></div></td></tr>
            @endif
        </tbody>
    </table>
</div>

@if($locations->hasPages())
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between px-3 py-3 border-top"><div class="text-muted small mb-2 mb-md-0">Showing <strong>{{ $locations->firstItem() ?? 0 }}</strong> to <strong>{{ $locations->lastItem() ?? 0 }}</strong> of <strong>{{ $locations->total() }}</strong> locations</div><div>{{ $locations->links('pagination::bootstrap-4') }}</div></div>
@endif
