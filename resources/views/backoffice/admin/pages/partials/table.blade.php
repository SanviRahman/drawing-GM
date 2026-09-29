@php
    $isTrash = $isTrash ?? false;
    $admin = auth('admin')->user();
    $canView = $admin?->can('page_view') ?? false;
    $canUpdate = $admin?->can('page_update') ?? false;
    $canDelete = $admin?->can('page_delete') ?? false;
    $canRestore = $admin?->can('page_restore') ?? false;
    $canForceDelete = $admin?->can('page_force_delete') ?? false;
    $canPublish = $admin?->can('page_publish') ?? false;
    $canUnpublish = $admin?->can('page_unpublish') ?? false;
    $canDuplicate = $admin?->can('page_duplicate') ?? false;
@endphp

<div class="table-responsive table-responsive-custom">
    <table class="table table-hover border-bottom page-table mb-0 text-nowrap">
        <thead class="thead-light"><tr><th class="text-center align-middle" style="width:40px;"><input type="checkbox" id="checkAll"></th><th class="align-middle">Page</th><th class="align-middle" style="width:125px;">Template</th><th class="text-center align-middle" style="width:120px;">Status</th><th class="text-center align-middle" style="width:110px;">Homepage</th><th class="align-middle" style="width:160px;">Updated</th><th class="text-center align-middle" style="width:210px;">Actions</th></tr></thead>
        <tbody>
            @if($pages->count() > 0)
                @foreach($pages as $page)
                    <tr>
                        <td class="text-center align-middle" data-label="Select"><input type="checkbox" class="row-checkbox" value="{{ $page->id }}"></td>
                        <td class="align-middle page-title-cell" data-label="Page"><div class="font-weight-bold text-dark"><i class="fas fa-file-alt text-primary mr-1"></i>{{ $page->title }}</div><div class="small text-muted"><i class="fas fa-link mr-1"></i>/{{ $page->slug }}</div>@if($page->excerpt)<div class="small text-muted text-truncate mt-1" style="max-width:360px;" title="{{ strip_tags($page->excerpt) }}">{{ \Illuminate\Support\Str::limit(strip_tags($page->excerpt), 140) }}</div>@endif</td>
                        <td class="align-middle" data-label="Template"><span class="badge badge-light border px-2 py-1">{{ $page->template }}</span></td>
                        <td class="text-center align-middle" data-label="Status"><span class="badge badge-{{ $page->statusBadgeClass() }} px-2 py-1">{{ $page->statusLabel() }}</span>@if($page->published_at)<small class="text-muted d-block mt-1">{{ $page->published_at->format('d M Y') }}</small>@endif</td>
                        <td class="text-center align-middle" data-label="Homepage">@if($page->is_homepage)<span class="badge badge-primary px-2 py-1"><i class="fas fa-home mr-1"></i>Homepage</span>@else<span class="text-muted">—</span>@endif</td>
                        <td class="align-middle" data-label="Updated"><div>{{ optional($page->updated_at)->format('d M Y') }}</div><small class="text-muted">{{ optional($page->updated_at)->format('h:i A') }}</small>@if($page->updatedBy)<small class="text-muted d-block">by {{ $page->updatedBy->name }}</small>@endif @if($isTrash && $page->deleted_at)<small class="text-danger d-block">Deleted {{ $page->deleted_at->diffForHumans() }}</small>@endif</td>
                        <td class="text-center align-middle action-cell" data-label="Actions">
                            <div class="btn-group btn-group-sm" role="group">
                                @if(!$isTrash)
                                    @if($canView)<button type="button" class="btn btn-outline-info btn-show" data-url="{{ route('admin.pages.show', $page->id) }}" title="View"><i class="fas fa-eye"></i></button>@endif
                                    @if($canUpdate)<button type="button" class="btn btn-outline-primary btn-edit" data-url="{{ route('admin.pages.edit', $page->id) }}" title="Edit"><i class="fas fa-edit"></i></button>@endif
                                    @if($page->status !== 'published' && $canPublish)<button type="button" class="btn btn-outline-success btn-status-action" data-url="{{ route('admin.pages.publish', $page->id) }}" data-label="Publish" title="Publish"><i class="fas fa-paper-plane"></i></button>@endif
                                    @if($page->status === 'published' && $canUnpublish)<button type="button" class="btn btn-outline-warning btn-status-action" data-url="{{ route('admin.pages.unpublish', $page->id) }}" data-label="Move to Draft" title="Move to Draft"><i class="fas fa-pause"></i></button>@endif
                                    @if($canDuplicate)<button type="button" class="btn btn-outline-secondary btn-duplicate" data-url="{{ route('admin.pages.duplicate', $page->id) }}" title="Duplicate"><i class="fas fa-copy"></i></button>@endif
                                    @if($canDelete)<button type="button" class="btn btn-outline-danger btn-delete" data-url="{{ route('admin.pages.destroy', $page->id) }}" title="Trash"><i class="fas fa-trash-alt"></i></button>@endif
                                @else
                                    @if($canRestore)<button type="button" class="btn btn-outline-success btn-restore" data-url="{{ route('admin.pages.restore', $page->id) }}" title="Restore"><i class="fas fa-undo-alt"></i></button>@endif
                                    @if($canForceDelete)<button type="button" class="btn btn-outline-danger btn-force-delete" data-url="{{ route('admin.pages.force_delete', $page->id) }}" title="Force Delete"><i class="fas fa-fire-alt"></i></button>@endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr><td colspan="7"><div class="text-center py-5"><div class="mb-3"><span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width:72px;height:72px;"><i class="far fa-file-alt fa-2x text-muted"></i></span></div><h5 class="text-muted mb-1">{{ $isTrash ? 'Trash bin is empty' : 'No pages found' }}</h5><p class="text-muted small mb-0">{{ $isTrash ? 'Deleted pages will appear here.' : 'Create a page or adjust the current filters.' }}</p></div></td></tr>
            @endif
        </tbody>
    </table>
</div>

@if($pages->hasPages())
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between px-3 py-3 border-top"><div class="text-muted small mb-2 mb-md-0">Showing <strong>{{ $pages->firstItem() ?? 0 }}</strong> to <strong>{{ $pages->lastItem() ?? 0 }}</strong> of <strong>{{ $pages->total() }}</strong> pages</div><div>{{ $pages->links('pagination::bootstrap-4') }}</div></div>
@endif
