@php
    $isTrash = $isTrash ?? false;
    $admin = auth('admin')->user();
    $canView = $admin?->can('service_feature_view') ?? false;
    $canUpdate = $admin?->can('service_feature_update') ?? false;
    $canDelete = $admin?->can('service_feature_delete') ?? false;
    $canToggle = $admin?->can('service_feature_toggle') ?? false;
    $canDuplicate = $admin?->can('service_feature_duplicate') ?? false;
    $canRestore = $admin?->can('service_feature_restore') ?? false;
    $canForceDelete = $admin?->can('service_feature_force_delete') ?? false;
@endphp

<div class="table-responsive">
    <table class="table table-hover mb-0 service-feature-table">
        <thead class="thead-light"><tr><th class="text-center" style="width:45px;"><input type="checkbox" id="checkAll" aria-label="Select all"></th><th style="width:190px;">Service</th><th>Feature</th><th style="width:120px;">Icon</th><th style="width:80px;">Order</th><th style="width:100px;">Status</th><th class="text-right" style="width:190px;">Actions</th></tr></thead>
        <tbody>
            @if($features->count() > 0)
                @foreach($features as $item)
                    <tr>
                        <td class="text-center"><input type="checkbox" class="row-checkbox" value="{{ $item->id }}" aria-label="Select {{ $item->title }}"></td>
                        <td><div class="font-weight-bold text-dark">{{ $item->service?->name ?? 'Deleted service' }}</div><small class="text-muted">#{{ $item->service_id }}</small></td>
                        <td><div class="font-weight-bold text-dark">{{ $item->title }}</div>@if($item->description)<small class="text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($item->description), 90) }}</small>@endif</td>
                        <td>@if($item->icon)<span class="badge badge-light border"><i class="fas fa-icons mr-1"></i>{{ $item->icon }}</span>@else<small class="text-muted">—</small>@endif</td>
                        <td><span class="font-weight-bold">{{ $item->sort_order }}</span></td>
                        <td><span class="badge badge-{{ $item->is_active ? 'success' : 'secondary' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span>@if($isTrash && $item->deleted_at)<small class="text-danger d-block mt-1">{{ $item->deleted_at->diffForHumans() }}</small>@endif</td>
                        <td class="text-right text-nowrap"><div class="btn-group btn-group-sm" role="group">
                            @if(!$isTrash)
                                @if($canView)<button type="button" class="btn btn-outline-info btn-show" data-url="{{ route('admin.service_features.show', $item->id) }}" title="View"><i class="fas fa-eye"></i></button>@endif
                                @if($canUpdate)<button type="button" class="btn btn-outline-primary btn-edit" data-url="{{ route('admin.service_features.edit', $item->id) }}" title="Edit"><i class="fas fa-pen"></i></button>@endif
                                @if($canToggle)<button type="button" class="btn btn-outline-{{ $item->is_active ? 'warning' : 'success' }} btn-toggle" data-url="{{ route('admin.service_features.toggle', $item->id) }}" title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}"><i class="fas fa-{{ $item->is_active ? 'pause' : 'play' }}"></i></button>@endif
                                @if($canDuplicate)<button type="button" class="btn btn-outline-secondary btn-duplicate" data-url="{{ route('admin.service_features.duplicate', $item->id) }}" title="Duplicate"><i class="fas fa-copy"></i></button>@endif
                                @if($canDelete)<button type="button" class="btn btn-outline-danger btn-delete" data-url="{{ route('admin.service_features.destroy', $item->id) }}" title="Move to trash"><i class="fas fa-trash"></i></button>@endif
                            @else
                                @if($canRestore)<button type="button" class="btn btn-outline-success btn-restore" data-url="{{ route('admin.service_features.restore', $item->id) }}" title="Restore"><i class="fas fa-undo-alt"></i></button>@endif
                                @if($canForceDelete)<button type="button" class="btn btn-outline-danger btn-force-delete" data-url="{{ route('admin.service_features.force_delete', $item->id) }}" title="Permanently delete"><i class="fas fa-fire-alt"></i></button>@endif
                            @endif
                        </div></td>
                    </tr>
                @endforeach
            @else
                <tr><td colspan="7"><div class="text-center py-5"><div class="mb-3"><span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width:72px;height:72px;"><i class="fas fa-list-ul fa-2x text-muted"></i></span></div><h5 class="text-muted mb-1">{{ $isTrash ? 'Trash bin is empty' : 'No service features found' }}</h5><p class="text-muted small mb-0">{{ $isTrash ? 'Deleted service features will appear here.' : 'Create a feature or adjust your filters.' }}</p></div></td></tr>
            @endif
        </tbody>
    </table>
</div>

@if($features->hasPages())
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between px-3 py-3 border-top"><div class="text-muted small mb-2 mb-md-0">Showing <strong>{{ $features->firstItem() ?? 0 }}</strong> to <strong>{{ $features->lastItem() ?? 0 }}</strong> of <strong>{{ $features->total() }}</strong> service features</div><div>{{ $features->links('pagination::bootstrap-4') }}</div></div>
@endif
