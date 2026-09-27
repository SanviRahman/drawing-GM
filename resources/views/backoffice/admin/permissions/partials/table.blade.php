<div class="table-responsive table-responsive-custom">
    <table class="table table-hover border-bottom permission-table mb-0 text-nowrap">
        <thead class="thead-light">
        <tr>
            <th style="width: 40px;" class="text-center align-middle">
                <input type="checkbox" id="checkAll">
            </th>
            <th style="width: 70px;" class="align-middle">ID</th>
            <th class="align-middle">Permission Name</th>
            <th class="align-middle">Guard Name</th>
            <th class="align-middle">Group Name</th>
            <th style="width: 150px;" class="align-middle">
                {{ ($isTrash ?? false) ? 'Deleted At' : 'Created At' }}
            </th>
            <th style="width: 130px;" class="text-center align-middle">Actions</th>
        </tr>
        </thead>

        <tbody>
        @forelse($permissions as $permission)
            <tr>
                <td data-label="Select" class="text-center align-middle">
                    <input type="checkbox" class="row-checkbox" value="{{ $permission->id }}">
                </td>

                <td data-label="ID" class="align-middle font-weight-bold text-primary">
                    #{{ $permission->id }}
                </td>

                <td data-label="Permission Name" class="align-middle font-weight-bold text-dark">
                    {{ $permission->name }}
                </td>

                <td data-label="Guard Name" class="align-middle">
                    <span class="badge badge-light border text-dark px-2 py-1"><i class="fas fa-shield-alt mr-1 text-info"></i>{{ $permission->guard_name }}</span>
                </td>

                <td data-label="Group Name" class="align-middle">
                    <span class="badge badge-light border text-dark px-2 py-1"><i class="fas fa-layer-group mr-1 text-primary"></i>{{ $permission->group_name ?? 'General' }}</span>
                </td>

                <td data-label="{{ ($isTrash ?? false) ? 'Deleted At' : 'Created At' }}" class="align-middle small font-weight-bold text-muted">
                    {{ ($isTrash ?? false) ? $permission->deleted_at->format('d M Y, h:i A') : $permission->created_at->format('d M Y, h:i A') }}
                </td>

                <td data-label="Actions" class="text-center align-middle action-cell">
                    @if($isTrash ?? false)
                        @can('permission_restore')
                            <button type="button" class="btn btn-sm btn-outline-success btn-restore shadow-sm mx-1" data-url="{{ route('admin.permissions.restore', $permission->id) }}" title="Restore"><i class="fas fa-undo"></i></button>
                        @endcan
                        @can('permission_force_delete')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-force-delete shadow-sm mx-1" data-url="{{ route('admin.permissions.force_delete', $permission->id) }}" title="Permanent Delete"><i class="fas fa-trash-alt"></i></button>
                        @endcan
                    @else
                        @can('permission_view')
                            <button type="button" class="btn btn-sm btn-outline-info btn-show shadow-sm mx-1" data-url="{{ route('admin.permissions.show', $permission->id) }}" title="View"><i class="fas fa-eye"></i></button>
                        @endcan
                        @can('permission_update')
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit shadow-sm mx-1" data-url="{{ route('admin.permissions.edit', $permission->id) }}" title="Edit"><i class="fas fa-pen"></i></button>
                        @endcan
                        @can('permission_delete')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete shadow-sm mx-1" data-url="{{ route('admin.permissions.destroy', $permission->id) }}" title="Trash"><i class="fas fa-trash"></i></button>
                        @endcan
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-key fa-3x mb-3 text-light"></i>
                        <h5 class="font-weight-bold">No Permissions Found</h5>
                        <p class="mb-0 small">No data available in the table. Try adjusting your search or filters.</p>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination Links --}}
@if($permissions->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top bg-light">
        <div class="text-muted small font-weight-bold mb-2 mb-md-0">
            Showing {{ $permissions->firstItem() ?? 0 }} to {{ $permissions->lastItem() ?? 0 }} of {{ $permissions->total() }} entries
        </div>
        <div class="m-0 pagination-sm">
            {!! $permissions->appends(request()->query())->links('pagination::bootstrap-4') !!}
        </div>
    </div>
@endif

<style>
    @media (max-width: 767.98px) {
        .table-responsive-custom { border: none !important; }
        .permission-table, .permission-table tbody, .permission-table tr, .permission-table td { display: block; width: 100%; }
        .permission-table thead { display: none; }
        .permission-table tr {
            margin-bottom: 1rem;
            border: 1px solid #e3e6f0 !important;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            background-color: #fff;
            overflow: hidden;
        }
        .permission-table td {
            display: flex !important;
            justify-content: space-between;
            align-items: center;
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 12px 15px !important;
            text-align: right;
        }
        .permission-table td:last-child { border-bottom: none !important; }
        .permission-table td::before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            color: #858796;
            margin-right: auto;
            text-align: left;
        }
        .permission-table td.action-cell {
            justify-content: center;
            background-color: #f8f9fc;
            padding: 15px !important;
        }
        .permission-table td.action-cell::before { display: none; }
    }
</style>
