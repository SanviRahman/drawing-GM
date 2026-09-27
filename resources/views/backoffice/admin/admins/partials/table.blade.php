<div class="table-responsive table-responsive-custom">
    <table class="table table-hover border-bottom admin-table mb-0 text-nowrap">
        <thead class="thead-light">
        <tr>
            <th style="width: 40px;" class="text-center align-middle">
                <input type="checkbox" id="checkAll">
            </th>
            <th class="align-middle">Name</th>
            <th class="align-middle">Email</th>
            <th class="align-middle">Roles</th>
            <th style="width: 100px;" class="text-center align-middle">Status</th>
            <th style="width: 150px;" class="text-center align-middle">Actions</th>
        </tr>
        </thead>

        <tbody>
        @forelse($admins as $admin)
            <tr>
                <td data-label="Select" class="text-center align-middle">
                    {{-- নিজের প্রোফাইল নিজে সিলেক্ট করা আটকানোর জন্য --}}
                    @if($admin->id !== auth('admin')->id())
                        <input type="checkbox" class="row-checkbox" value="{{ $admin->id }}">
                    @endif
                </td>

                <td data-label="Name" class="align-middle font-weight-bold text-dark">
                    {{ $admin->name }}
                    @if($admin->id === auth('admin')->id())
                        <span class="badge badge-danger ml-1 shadow-sm">You</span>
                    @endif
                </td>

                <td data-label="Email" class="align-middle text-muted">
                    <i class="fas fa-envelope text-muted mr-1"></i>{{ $admin->email }}
                </td>

                <td data-label="Roles" class="align-middle">
                    @forelse($admin->roles as $role)
                        <span class="badge badge-info px-2 py-1 mr-1 text-uppercase">{{ $role->name }}</span>
                    @empty
                        <span class="text-muted small">No roles</span>
                    @endforelse
                </td>

                <td data-label="Status" class="text-center align-middle">
                    @if($admin->status)
                        <span class="badge badge-success px-2 py-1 shadow-sm">Active</span>
                    @else
                        <span class="badge badge-secondary px-2 py-1 shadow-sm">Inactive</span>
                    @endif
                </td>

                <td data-label="Actions" class="text-center align-middle action-cell">
                    @if($isTrash ?? false)
                        @can('admin_restore')
                            <button type="button" class="btn btn-sm btn-outline-success btn-restore shadow-sm mx-1" data-url="{{ route('admin.admins.restore', $admin->id) }}" title="Restore"><i class="fas fa-undo"></i></button>
                        @endcan
                        @can('admin_force_delete')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-force-delete shadow-sm mx-1" data-url="{{ route('admin.admins.force_delete', $admin->id) }}" title="Permanent Delete"><i class="fas fa-trash-alt"></i></button>
                        @endcan
                    @else
                        @can('admin_view')
                            <button type="button" class="btn btn-sm btn-outline-info btn-show shadow-sm mx-1" data-url="{{ route('admin.admins.show', $admin->id) }}" title="View"><i class="fas fa-eye"></i></button>
                        @endcan
                        @can('admin_update')
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit shadow-sm mx-1" data-url="{{ route('admin.admins.edit', $admin->id) }}" title="Edit"><i class="fas fa-pen"></i></button>
                        @endcan
                        @can('admin_delete')
                            {{-- নিজের আইডি ডিলিট করার বাটন দেখা যাবে না --}}
                            @if($admin->id !== auth('admin')->id())
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete shadow-sm mx-1" data-url="{{ route('admin.admins.destroy', $admin->id) }}" title="Trash"><i class="fas fa-trash"></i></button>
                            @endif
                        @endcan
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-users fa-3x mb-3 text-light"></i>
                        <h5 class="font-weight-bold">No Admins Found</h5>
                        <p class="mb-0 small">No data available in the table.</p>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($admins->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top bg-light">
        <div class="text-muted small font-weight-bold mb-2 mb-md-0">
            Showing {{ $admins->firstItem() ?? 0 }} to {{ $admins->lastItem() ?? 0 }} of {{ $admins->total() }} entries
        </div>
        <div class="m-0 pagination-sm">
            {!! $admins->appends(request()->query())->links('pagination::bootstrap-4') !!}
        </div>
    </div>
@endif

<style>
    @media (max-width: 767.98px) {
        .table-responsive-custom { border: none !important; }
        .admin-table, .admin-table tbody, .admin-table tr, .admin-table td { display: block; width: 100%; }
        .admin-table thead { display: none; }
        .admin-table tr {
            margin-bottom: 1rem;
            border: 1px solid #e3e6f0 !important;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            background-color: #fff;
            overflow: hidden;
        }
        .admin-table td {
            display: flex !important;
            justify-content: space-between;
            align-items: center;
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 12px 15px !important;
            text-align: right;
        }
        .admin-table td:last-child { border-bottom: none !important; }
        .admin-table td::before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            color: #858796;
            margin-right: auto;
            text-align: left;
        }
        .admin-table td.action-cell {
            justify-content: center;
            background-color: #f8f9fc;
            padding: 15px !important;
        }
        .admin-table td.action-cell::before { display: none; }
    }
</style>
