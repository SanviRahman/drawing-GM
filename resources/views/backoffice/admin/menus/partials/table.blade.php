<div class="table-responsive table-responsive-custom">
    <table class="table table-hover border-bottom menu-table mb-0 text-nowrap">
        <thead class="thead-light">
        <tr>
            <th style="width: 40px;" class="text-center align-middle">
                <input type="checkbox" id="checkAll">
            </th>
            <th class="align-middle">Menu Name</th>
            <th class="align-middle">Location</th>
            <th style="width: 120px;" class="text-center align-middle">Items</th>
            <th style="width: 100px;" class="text-center align-middle">Status</th>
            <th style="width: 150px;" class="text-center align-middle">Actions</th>
        </tr>
        </thead>

        <tbody>
        @forelse($menus as $menu)
            <tr>
                <td data-label="Select" class="text-center align-middle">
                    <input type="checkbox" class="row-checkbox" value="{{ $menu->id }}">
                </td>

                <td data-label="Menu Name" class="align-middle font-weight-bold text-dark">
                    <i class="fas fa-sitemap text-primary mr-1"></i>{{ $menu->name }}
                </td>

                <td data-label="Location" class="align-middle">
                    <span class="badge badge-info px-2 py-1 text-lowercase">{{ $menu->location }}</span>
                </td>

                <td data-label="Items" class="text-center align-middle">
                    <span class="badge badge-success px-2 py-1">{{ $menu->items_count ?? $menu->items->count() }}</span>
                </td>

                <td data-label="Status" class="text-center align-middle">
                    @if($menu->is_active)
                        <span class="badge badge-success px-2 py-1 shadow-sm">Active</span>
                    @else
                        <span class="badge badge-secondary px-2 py-1 shadow-sm">Inactive</span>
                    @endif
                </td>

                <td data-label="Actions" class="text-center align-middle action-cell">
                    @if($isTrash ?? false)
                        @can('menu_restore')
                            <button type="button" class="btn btn-sm btn-outline-success btn-restore shadow-sm mx-1" data-url="{{ route('admin.menus.restore', $menu->id) }}" title="Restore"><i class="fas fa-undo"></i></button>
                        @endcan
                        @can('menu_force_delete')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-force-delete shadow-sm mx-1" data-url="{{ route('admin.menus.force_delete', $menu->id) }}" title="Permanent Delete"><i class="fas fa-trash-alt"></i></button>
                        @endcan
                    @else
                        @can('menu_view')
                            <button type="button" class="btn btn-sm btn-outline-info btn-show shadow-sm mx-1" data-url="{{ route('admin.menus.show', $menu->id) }}" title="View & Manage Items"><i class="fas fa-eye"></i></button>
                        @endcan
                        @can('menu_update')
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit shadow-sm mx-1" data-url="{{ route('admin.menus.edit', $menu->id) }}" title="Edit"><i class="fas fa-pen"></i></button>
                        @endcan
                        @can('menu_delete')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete shadow-sm mx-1" data-url="{{ route('admin.menus.destroy', $menu->id) }}" title="Trash"><i class="fas fa-trash"></i></button>
                        @endcan
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-sitemap fa-3x mb-3 text-light"></i>
                        <h5 class="font-weight-bold">No Menus Found</h5>
                        <p class="mb-0 small">No data available in the table.</p>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($menus->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top bg-light">
        <div class="text-muted small font-weight-bold mb-2 mb-md-0">
            Showing {{ $menus->firstItem() ?? 0 }} to {{ $menus->lastItem() ?? 0 }} of {{ $menus->total() }} entries
        </div>
        <div class="m-0 pagination-sm">
            {!! $menus->appends(request()->query())->links('pagination::bootstrap-4') !!}
        </div>
    </div>
@endif

<style>
    @media (max-width: 767.98px) {
        .table-responsive-custom { border: none !important; }
        .menu-table, .menu-table tbody, .menu-table tr, .menu-table td { display: block; width: 100%; }
        .menu-table thead { display: none; }
        .menu-table tr {
            margin-bottom: 1rem;
            border: 1px solid #e3e6f0 !important;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            background-color: #fff;
            overflow: hidden;
        }
        .menu-table td {
            display: flex !important;
            justify-content: space-between;
            align-items: center;
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 12px 15px !important;
            text-align: right;
        }
        .menu-table td:last-child { border-bottom: none !important; }
        .menu-table td::before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            color: #858796;
            margin-right: auto;
            text-align: left;
        }
        .menu-table td.action-cell {
            justify-content: center;
            background-color: #f8f9fc;
            padding: 15px !important;
        }
        .menu-table td.action-cell::before { display: none; }
    }
</style>
