<div class="table-responsive table-responsive-custom">
    <table class="table table-hover border-bottom role-table mb-0 text-nowrap">
        <thead class="thead-light">
        <tr>
            <th style="width: 40px;" class="text-center align-middle"><input type="checkbox" id="checkAll"></th>
            <th class="align-middle">Role Name</th>
            <th class="align-middle">Guard Name</th>
            <th class="text-center align-middle">Permissions Count</th>
            <th class="text-center align-middle">Users Count</th>
            <th style="width: 150px;" class="text-center align-middle">Actions</th>
        </tr>
        </thead>

        <tbody>
        @forelse($roles as $role)
            <tr>
                <td data-label="Select" class="text-center align-middle">
                    @if(!$role->isSuperAdmin())
                        <input type="checkbox" class="row-checkbox" value="{{ $role->id }}">
                    @endif
                </td>

                <td data-label="Role Name" class="align-middle font-weight-bold text-dark">
                    <i class="fas fa-shield-alt text-primary mr-1"></i>{{ $role->name }}
                    @if($role->isSuperAdmin())
                        <span class="badge badge-danger ml-1 shadow-sm">Protected</span>
                    @endif
                </td>

                <td data-label="Guard Name" class="align-middle">
                    <span class="badge badge-secondary px-2 py-1 text-uppercase">{{ $role->guard_name }}</span>
                </td>

                <td data-label="Permissions Count" class="text-center align-middle font-weight-bold text-info">
                    <span class="badge badge-info px-2 py-1">{{ $role->permissions_count ?? $role->permissions->count() }}</span>
                </td>

                <td data-label="Users Count" class="text-center align-middle font-weight-bold text-success">
                    <span class="badge badge-success px-2 py-1">{{ $role->users_count ?? 0 }}</span>
                </td>

                <td data-label="Actions" class="text-center align-middle action-cell">
                    @if($isTrash ?? false)
                        @can('role_restore')
                            <button type="button" class="btn btn-sm btn-outline-success btn-restore shadow-sm mx-1" data-url="{{ route('admin.roles.restore', $role->id) }}" title="Restore"><i class="fas fa-undo"></i></button>
                        @endcan

                        @can('role_force_delete')
                            @if(!$role->isSuperAdmin())
                                <button type="button" class="btn btn-sm btn-outline-danger btn-force-delete shadow-sm mx-1" data-url="{{ route('admin.roles.force_delete', $role->id) }}" title="Permanent Delete"><i class="fas fa-trash-alt"></i></button>
                            @endif
                        @endcan
                    @else
                        @can('role_view')
                            <button type="button" class="btn btn-sm btn-outline-info btn-show shadow-sm mx-1" data-url="{{ route('admin.roles.show', $role->id) }}" title="View"><i class="fas fa-eye"></i></button>
                        @endcan

                        @can('role_update')
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit shadow-sm mx-1" data-url="{{ route('admin.roles.edit', $role->id) }}" title="Edit"><i class="fas fa-pen"></i></button>
                        @endcan

                        @can('role_delete')
                            @if(!$role->isSuperAdmin())
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete shadow-sm mx-1" data-url="{{ route('admin.roles.destroy', $role->id) }}" title="Trash"><i class="fas fa-trash"></i></button>
                            @endif
                        @endcan
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-user-lock fa-3x mb-3 text-light"></i>
                        <h5 class="font-weight-bold">No Roles Found</h5>
                        <p class="mb-0 small">No data available in the table.</p>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($roles->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top bg-light">
        <div class="text-muted small font-weight-bold mb-2 mb-md-0">
            Showing {{ $roles->firstItem() ?? 0 }} to {{ $roles->lastItem() ?? 0 }} of {{ $roles->total() }} entries
        </div>
        <div class="m-0 pagination-sm">
            {!! $roles->appends(request()->query())->links('pagination::bootstrap-4') !!}
        </div>
    </div>
@endif

<style>
    @media (max-width: 767.98px) {
        .table-responsive-custom { border: none !important; }
        .role-table, .role-table tbody, .role-table tr, .role-table td { display: block; width: 100%; }
        .role-table thead { display: none; }
        .role-table tr { margin-bottom: 1rem; border: 1px solid #e3e6f0 !important; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); background-color: #fff; overflow: hidden; }
        .role-table td { display: flex !important; justify-content: space-between; align-items: center; border: none !important; border-bottom: 1px solid #f1f5f9 !important; padding: 12px 15px !important; text-align: right; }
        .role-table td:last-child { border-bottom: none !important; }
        .role-table td::before { content: attr(data-label); font-weight: 700; font-size: 12px; text-transform: uppercase; color: #858796; margin-right: auto; text-align: left; }
        .role-table td.action-cell { justify-content: center; background-color: #f8f9fc; padding: 15px !important; }
        .role-table td.action-cell::before { display: none; }
    }
</style>
