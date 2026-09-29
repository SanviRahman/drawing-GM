@php
    $isTrash = $isTrash ?? false;
    $admin = auth('admin')->user();
    $canView = $admin?->can('page_section_view') ?? false;
    $canUpdate = $admin?->can('page_section_update') ?? false;
    $canDelete = $admin?->can('page_section_delete') ?? false;
    $canToggle = $admin?->can('page_section_toggle') ?? false;
    $canRestore = $admin?->can('page_section_restore') ?? false;
    $canForceDelete = $admin?->can('page_section_force_delete') ?? false;
@endphp

<div class="table-responsive">
    <table class="table table-hover mb-0 page-section-table">
        <thead class="thead-light"><tr><th class="text-center" style="width:45px;"><input type="checkbox" id="checkAll" aria-label="Select all"></th><th style="width:190px;">Page</th><th style="width:170px;">Section Type</th><th>Content</th><th style="width:90px;">Theme</th><th style="width:70px;">Order</th><th style="width:115px;">State</th><th class="text-right" style="width:155px;">Actions</th></tr></thead>
        <tbody>
            @if($pageSections->count() > 0)
                @foreach($pageSections as $item)
                    @php
                        $status = $item->scheduleStatus();
                        $payloadCount = is_array($item->payload) ? count($item->payload) : 0;
                    @endphp
                    <tr>
                        <td class="text-center"><input type="checkbox" class="row-checkbox" value="{{ $item->id }}" aria-label="Select page section {{ $item->id }}"></td>
                        <td><div class="font-weight-bold text-dark">{{ $item->page?->title ?? 'Deleted page' }}</div><small class="text-muted">/{{ $item->page?->slug ?? '-' }}</small></td>
                        <td><span class="badge badge-info px-2 py-1">{{ $item->sectionDefinition?->name ?? 'Deleted definition' }}</span><small class="text-muted d-block mt-1"><code>{{ $item->sectionDefinition?->key ?? '-' }}</code></small></td>
                        <td>@if($item->heading)<div class="font-weight-bold text-dark">{{ $item->heading }}</div>@else<small class="text-muted">No heading</small>@endif @if($item->subheading)<small class="text-muted d-block mt-1">{{ \Illuminate\Support\Str::limit($item->subheading, 90) }}</small>@endif<div class="payload-preview mt-1"><span class="badge badge-light border mr-1"><i class="fas fa-code mr-1"></i>JSON</span><small class="text-muted">{{ $payloadCount }} top-level item{{ $payloadCount === 1 ? '' : 's' }}</small></div></td>
                        <td><span class="badge badge-light border">{{ $item->theme }}</span></td>
                        <td><span class="font-weight-bold">{{ $item->sort_order }}</span></td>
                        <td><span class="badge badge-{{ $item->scheduleBadgeClass() }} px-2 py-1">{{ ucfirst($status) }}</span>@if($item->starts_at && $status === 'scheduled')<small class="text-muted d-block mt-1">{{ $item->starts_at->format('d M') }}</small>@endif @if($isTrash && $item->deleted_at)<small class="text-danger d-block mt-1">Deleted {{ $item->deleted_at->diffForHumans() }}</small>@endif</td>
                        <td class="text-right text-nowrap"><div class="btn-group btn-group-sm" role="group">
                            @if(!$isTrash)
                                @if($canView)<button type="button" class="btn btn-outline-info btn-show" data-url="{{ route('admin.page_sections.show', $item->id) }}" title="View"><i class="fas fa-eye"></i></button>@endif
                                @if($canUpdate)<button type="button" class="btn btn-outline-primary btn-edit" data-url="{{ route('admin.page_sections.edit', $item->id) }}" title="Edit"><i class="fas fa-pen"></i></button>@endif
                                @if($canToggle)<button type="button" class="btn btn-outline-{{ $item->is_active ? 'warning' : 'success' }} btn-toggle" data-url="{{ route('admin.page_sections.toggle', $item->id) }}" data-label="{{ $item->is_active ? 'Deactivate' : 'Activate' }}" title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}"><i class="fas fa-{{ $item->is_active ? 'pause' : 'play' }}"></i></button>@endif
                                @if($canDelete)<button type="button" class="btn btn-outline-danger btn-delete" data-url="{{ route('admin.page_sections.destroy', $item->id) }}" title="Move to trash"><i class="fas fa-trash"></i></button>@endif
                            @else
                                @if($canRestore)<button type="button" class="btn btn-outline-success btn-restore" data-url="{{ route('admin.page_sections.restore', $item->id) }}" title="Restore"><i class="fas fa-undo-alt"></i></button>@endif
                                @if($canForceDelete)<button type="button" class="btn btn-outline-danger btn-force-delete" data-url="{{ route('admin.page_sections.force_delete', $item->id) }}" title="Permanently delete"><i class="fas fa-fire-alt"></i></button>@endif
                            @endif
                        </div></td>
                    </tr>
                @endforeach
            @else
                <tr><td colspan="8"><div class="text-center py-5"><div class="mb-3"><span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width:72px;height:72px;"><i class="fas fa-th-large fa-2x text-muted"></i></span></div><h5 class="text-muted mb-1">{{ $isTrash ? 'Trash bin is empty' : 'No page sections found' }}</h5><p class="text-muted small mb-0">{{ $isTrash ? 'Deleted page sections will appear here.' : 'Create a page section or adjust your filters.' }}</p></div></td></tr>
            @endif
        </tbody>
    </table>
</div>

@if($pageSections->hasPages())
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between px-3 py-3 border-top"><div class="text-muted small mb-2 mb-md-0">Showing <strong>{{ $pageSections->firstItem() ?? 0 }}</strong> to <strong>{{ $pageSections->lastItem() ?? 0 }}</strong> of <strong>{{ $pageSections->total() }}</strong> page sections</div><div>{{ $pageSections->links('pagination::bootstrap-4') }}</div></div>
@endif
