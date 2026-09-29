@php
    $isTrash = $isTrash ?? false;
    $admin = auth('admin')->user();
    $canView = $admin?->can('service_view') ?? false;
    $canUpdate = $admin?->can('service_update') ?? false;
    $canDelete = $admin?->can('service_delete') ?? false;
    $canPublish = $admin?->can('service_publish') ?? false;
    $canUnpublish = $admin?->can('service_unpublish') ?? false;
    $canDuplicate = $admin?->can('service_duplicate') ?? false;
    $canRestore = $admin?->can('service_restore') ?? false;
    $canForceDelete = $admin?->can('service_force_delete') ?? false;
@endphp

<div class="table-responsive">
    <table class="table table-hover mb-0 service-table">
        <thead class="thead-light">
            <tr>
                <th class="text-center" style="width:45px;"><input type="checkbox" id="checkAll" aria-label="Select all services"></th>
                <th style="width:86px;">Preview</th>
                <th>Service</th>
                <th style="width:115px;">Status</th>
                <th style="width:90px;">Featured</th>
                <th style="width:80px;">Order</th>
                <th style="width:150px;">Published</th>
                <th class="text-right" style="width:210px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @if($services->count() > 0)
                @foreach($services as $item)
                    @php
                        $hero = $item->getFirstMedia('hero_desktop') ?: $item->getFirstMedia('hero_mobile');
                        $heroUrl = null;
                        if ($hero) {
                            try {
                                $heroUrl = $hero->getUrl();
                            } catch (\Throwable) {
                                $heroUrl = null;
                            }
                        }
                    @endphp
                    <tr>
                        <td class="text-center"><input type="checkbox" class="row-checkbox" value="{{ $item->id }}" aria-label="Select {{ $item->name }}"></td>
                        <td><div class="border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width:64px;height:54px;">@if($heroUrl)<img src="{{ $heroUrl }}" alt="{{ $item->name }}" class="service-thumb" loading="lazy">@else<i class="fas fa-tools text-muted"></i>@endif</div></td>
                        <td class="service-title-cell"><div class="font-weight-bold text-dark">{{ $item->name }}</div><small class="text-muted d-block">/{{ $item->slug }}</small>@if($item->icon)<small class="text-muted d-block mt-1"><i class="fas fa-icons mr-1"></i>{{ $item->icon }}</small>@endif @if($isTrash && $item->deleted_at)<small class="text-danger d-block mt-1"><i class="fas fa-trash-alt mr-1"></i>{{ $item->deleted_at->diffForHumans() }}</small>@endif</td>
                        <td><span class="badge badge-{{ $item->statusBadgeClass() }} px-2 py-1">{{ $item->statusLabel() }}</span></td>
                        <td>@if($item->is_featured)<span class="badge badge-primary px-2 py-1"><i class="fas fa-star mr-1"></i>Yes</span>@else<span class="badge badge-light border px-2 py-1">No</span>@endif</td>
                        <td><span class="font-weight-bold">{{ $item->sort_order }}</span></td>
                        <td>@if($item->published_at)<small class="text-dark">{{ $item->published_at->format('d M Y') }}</small><small class="text-muted d-block">{{ $item->published_at->format('h:i A') }}</small>@else<small class="text-muted">—</small>@endif</td>
                        <td class="text-right text-nowrap">
                            <div class="btn-group btn-group-sm" role="group">
                                @if(!$isTrash)
                                    @if($canView)
                                        <button type="button" class="btn btn-outline-info btn-show" data-url="{{ route('admin.services.show', $item->id) }}" title="View"><i class="fas fa-eye"></i></button>
                                    @endif
                                    @if($canUpdate)
                                        <button type="button" class="btn btn-outline-primary btn-edit" data-url="{{ route('admin.services.edit', $item->id) }}" title="Edit"><i class="fas fa-pen"></i></button>
                                    @endif
                                    @if($item->status !== 'published' && $canPublish)
                                        <button type="button" class="btn btn-outline-success btn-status-action" data-url="{{ route('admin.services.publish', $item->id) }}" data-label="Publish" title="Publish"><i class="fas fa-check"></i></button>
                                    @elseif($item->status === 'published' && $canUnpublish)
                                        <button type="button" class="btn btn-outline-warning btn-status-action" data-url="{{ route('admin.services.unpublish', $item->id) }}" data-label="Move to Draft" title="Move to Draft"><i class="fas fa-pause"></i></button>
                                    @endif
                                    @if($canDuplicate)
                                        <button type="button" class="btn btn-outline-secondary btn-duplicate" data-url="{{ route('admin.services.duplicate', $item->id) }}" title="Duplicate"><i class="fas fa-copy"></i></button>
                                    @endif
                                    @if($canDelete)
                                        <button type="button" class="btn btn-outline-danger btn-delete" data-url="{{ route('admin.services.destroy', $item->id) }}" title="Move to trash"><i class="fas fa-trash"></i></button>
                                    @endif
                                @else
                                    @if($canRestore)
                                        <button type="button" class="btn btn-outline-success btn-restore" data-url="{{ route('admin.services.restore', $item->id) }}" title="Restore"><i class="fas fa-undo-alt"></i></button>
                                    @endif
                                    @if($canForceDelete)
                                        <button type="button" class="btn btn-outline-danger btn-force-delete" data-url="{{ route('admin.services.force_delete', $item->id) }}" title="Permanently delete"><i class="fas fa-fire-alt"></i></button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr><td colspan="8"><div class="text-center py-5"><div class="mb-3"><span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width:72px;height:72px;"><i class="fas fa-tools fa-2x text-muted"></i></span></div><h5 class="text-muted mb-1">{{ $isTrash ? 'Trash bin is empty' : 'No services found' }}</h5><p class="text-muted small mb-0">{{ $isTrash ? 'Deleted services will appear here.' : 'Create a service or adjust your filters.' }}</p></div></td></tr>
            @endif
        </tbody>
    </table>
</div>

@if($services->hasPages())
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between px-3 py-3 border-top"><div class="text-muted small mb-2 mb-md-0">Showing <strong>{{ $services->firstItem() ?? 0 }}</strong> to <strong>{{ $services->lastItem() ?? 0 }}</strong> of <strong>{{ $services->total() }}</strong> services</div><div>{{ $services->links('pagination::bootstrap-4') }}</div></div>
@endif
