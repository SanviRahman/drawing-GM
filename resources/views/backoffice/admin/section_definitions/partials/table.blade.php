@php
    $isTrash = $isTrash ?? false;
    $admin = auth('admin')->user();
    $canView = $admin?->can('section_definition_view') ?? false;
    $canUpdate = $admin?->can('section_definition_update') ?? false;
    $canDelete = $admin?->can('section_definition_delete') ?? false;
    $canToggle = $admin?->can('section_definition_toggle') ?? false;
    $canRestore = $admin?->can('section_definition_restore') ?? false;
    $canForceDelete = $admin?->can('section_definition_force_delete') ?? false;
@endphp

<div class="table-responsive">
    <table class="table table-hover mb-0 section-definition-table">
        <thead class="thead-light"><tr><th class="text-center" style="width:45px;"><input type="checkbox" id="checkAll" aria-label="Select all"></th><th style="width:190px;">Key</th><th>Section</th><th>Schema</th><th style="width:105px;">Status</th><th style="width:135px;">Updated</th><th class="text-right" style="width:155px;">Actions</th></tr></thead>
        <tbody>
            @if($sectionDefinitions->count() > 0)
                @foreach($sectionDefinitions as $item)
                    @php
                        $description = trim(strip_tags((string) $item->description));
                        $schemaCount = is_array($item->schema_json) ? count($item->schema_json) : 0;
                    @endphp
                    <tr>
                        <td class="text-center"><input type="checkbox" class="row-checkbox" value="{{ $item->id }}" aria-label="Select {{ $item->name }}"></td>
                        <td><code class="font-weight-bold">{{ $item->key }}</code><small class="text-muted d-block mt-1">&lt;x-sections.{{ $item->component_name }} /&gt;</small></td>
                        <td><div class="font-weight-bold text-dark">{{ $item->name }}</div>@if($description !== '')<small class="text-muted d-block mt-1">{{ \Illuminate\Support\Str::limit($description, 100) }}</small>@else<small class="text-muted">No description</small>@endif</td>
                        <td><div class="schema-preview"><span class="badge badge-light border mr-1"><i class="fas fa-code mr-1"></i>JSON</span><small class="text-muted">{{ $schemaCount }} top-level item{{ $schemaCount === 1 ? '' : 's' }}</small></div></td>
                        <td>@if($item->is_active)<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Active</span>@else<span class="badge badge-secondary px-2 py-1"><i class="fas fa-pause-circle mr-1"></i>Inactive</span>@endif</td>
                        <td><small class="text-muted">{{ optional($item->updated_at)->format('d M Y') }}</small>@if($isTrash && $item->deleted_at)<small class="text-danger d-block">Deleted {{ $item->deleted_at->diffForHumans() }}</small>@endif</td>
                        <td class="text-right text-nowrap">
                            <div class="btn-group btn-group-sm" role="group">
                                @if(!$isTrash)
                                    @if($canView)<button type="button" class="btn btn-outline-info btn-show" data-url="{{ route('admin.section_definitions.show', $item->id) }}" title="View"><i class="fas fa-eye"></i></button>@endif
                                    @if($canUpdate)<button type="button" class="btn btn-outline-primary btn-edit" data-url="{{ route('admin.section_definitions.edit', $item->id) }}" title="Edit"><i class="fas fa-pen"></i></button>@endif
                                    @if($canToggle)<button type="button" class="btn btn-outline-{{ $item->is_active ? 'warning' : 'success' }} btn-toggle" data-url="{{ route('admin.section_definitions.toggle', $item->id) }}" data-label="{{ $item->is_active ? 'Deactivate' : 'Activate' }}" title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}"><i class="fas fa-{{ $item->is_active ? 'pause' : 'play' }}"></i></button>@endif
                                    @if($canDelete)<button type="button" class="btn btn-outline-danger btn-delete" data-url="{{ route('admin.section_definitions.destroy', $item->id) }}" title="Move to trash"><i class="fas fa-trash"></i></button>@endif
                                @else
                                    @if($canRestore)<button type="button" class="btn btn-outline-success btn-restore" data-url="{{ route('admin.section_definitions.restore', $item->id) }}" title="Restore"><i class="fas fa-undo-alt"></i></button>@endif
                                    @if($canForceDelete)<button type="button" class="btn btn-outline-danger btn-force-delete" data-url="{{ route('admin.section_definitions.force_delete', $item->id) }}" title="Permanently delete"><i class="fas fa-fire-alt"></i></button>@endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr><td colspan="7"><div class="text-center py-5"><div class="mb-3"><span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width:72px;height:72px;"><i class="fas fa-layer-group fa-2x text-muted"></i></span></div><h5 class="text-muted mb-1">{{ $isTrash ? 'Trash bin is empty' : 'No section definitions found' }}</h5><p class="text-muted small mb-0">{{ $isTrash ? 'Deleted definitions will appear here.' : 'Create a section definition or adjust your filters.' }}</p></div></td></tr>
            @endif
        </tbody>
    </table>
</div>

@if($sectionDefinitions->hasPages())
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between px-3 py-3 border-top"><div class="text-muted small mb-2 mb-md-0">Showing <strong>{{ $sectionDefinitions->firstItem() ?? 0 }}</strong> to <strong>{{ $sectionDefinitions->lastItem() ?? 0 }}</strong> of <strong>{{ $sectionDefinitions->total() }}</strong> sections</div><div>{{ $sectionDefinitions->links('pagination::bootstrap-4') }}</div></div>
@endif
