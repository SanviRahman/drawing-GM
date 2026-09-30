<div class="table-responsive table-responsive-custom">
    <table class="table table-hover border-bottom gallery-item-table mb-0 text-nowrap">
        <thead class="thead-light">
            <tr>
                <th style="width:40px;" class="text-center align-middle"><input type="checkbox" id="checkAll"></th>
                <th style="width:80px;" class="text-center align-middle">Preview</th>
                <th class="align-middle">Gallery</th>
                <th class="align-middle">Title</th>
                <th class="align-middle">Type</th>
                <th style="width:100px;" class="text-center align-middle">Status</th>
                <th style="width:150px;" class="text-center align-middle">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td data-label="Select" class="text-center align-middle">
                    <input type="checkbox" class="row-checkbox" value="{{ $item->id }}">
                </td>
                <td data-label="Preview" class="text-center align-middle">
                    @if($item->hasMedia('image'))
                    <img src="{{ $item->getFirstMediaUrl('image') }}" alt="{{ $item->title }}" class="rounded border"
                        style="width:50px;height:50px;object-fit:cover;" loading="lazy">
                    @else
                    <span
                        class="d-inline-flex align-items-center justify-content-center rounded border bg-light text-muted"
                        style="width:50px;height:50px;"><i class="fas fa-image"></i></span>
                    @endif
                </td>
                <td data-label="Gallery" class="align-middle font-weight-bold text-dark">
                    <i class="fas fa-images text-primary mr-1"></i>{{ $item->gallery?->name ?? 'N/A' }}
                </td>
                <td data-label="Title" class="align-middle text-dark">
                    {{ $item->title ?? 'Untitled' }}
                    @if($item->caption)
                    <div class="small text-muted">{{ Str::limit($item->caption,40) }}</div>
                    @endif
                </td>
                <td data-label="Type" class="align-middle">
                    <span class="badge badge-info px-2 py-1 text-uppercase">{{ $item->item_type }}</span>
                </td>
                <td data-label="Status" class="text-center align-middle">
                    @if($item->is_active)
                    <span class="badge badge-success px-2 py-1 shadow-sm">Active</span>
                    @else
                    <span class="badge badge-secondary px-2 py-1 shadow-sm">Inactive</span>
                    @endif
                </td>
                <td data-label="Actions" class="text-center align-middle action-cell">
                    @if($isTrash ?? false)

                    @can('gallery_item_restore')
                    <button type="button" class="btn btn-sm btn-outline-success btn-restore shadow-sm mx-1"
                        data-url="{{ route('admin.gallery_items.restore',$item->id) }}" title="Restore"><i
                            class="fas fa-undo"></i></button>
                    @endcan

                    @can('gallery_item_force_delete')
                    <button type="button" class="btn btn-sm btn-outline-danger btn-force-delete shadow-sm mx-1"
                        data-url="{{ route('admin.gallery_items.force_delete',$item->id) }}" title="Permanent Delete"><i
                            class="fas fa-trash-alt"></i></button>
                    @endcan

                    @else

                    @can('gallery_item_view')
                    <button type="button" class="btn btn-sm btn-outline-info btn-show shadow-sm mx-1"
                        data-url="{{ route('admin.gallery_items.show',$item->id) }}" title="View"><i
                            class="fas fa-eye"></i></button>
                    @endcan

                    @can('gallery_item_update')
                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit shadow-sm mx-1"
                        data-url="{{ route('admin.gallery_items.edit',$item->id) }}" title="Edit"><i
                            class="fas fa-pen"></i></button>
                    @endcan

                    @can('gallery_item_delete')
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete shadow-sm mx-1"
                        data-url="{{ route('admin.gallery_items.destroy',$item->id) }}" title="Trash"><i
                            class="fas fa-trash"></i></button>
                    @endcan

                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-photo-video fa-3x mb-3 text-light"></i>
                        <h5 class="font-weight-bold">No Gallery Items Found</h5>
                        <p class="mb-0 small">No data available in the table.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($items->hasPages())
<div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top bg-light">
    <div class="text-muted small font-weight-bold mb-2 mb-md-0">Showing {{ $items->firstItem() ?? 0 }} to
        {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} entries</div>
    <div class="m-0 pagination-sm">{!! $items->appends(request()->query())->links('pagination::bootstrap-4') !!}</div>
</div>
@endif

<style>
@media(max-width:767.98px) {
    .table-responsive-custom {
        border: none !important;
    }

    .gallery-item-table,
    .gallery-item-table tbody,
    .gallery-item-table tr,
    .gallery-item-table td {
        display: block;
        width: 100%;
    }

    .gallery-item-table thead {
        display: none;
    }

    .gallery-item-table tr {
        margin-bottom: 1rem;
        border: 1px solid #e3e6f0 !important;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, .05);
        background: #fff;
        overflow: hidden;
    }

    .gallery-item-table td {
        display: flex !important;
        justify-content: space-between;
        align-items: center;
        border: none !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 12px 15px !important;
        text-align: right;
    }

    .gallery-item-table td:last-child {
        border-bottom: none !important;
    }

    .gallery-item-table td::before {
        content: attr(data-label);
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        color: #858796;
        margin-right: auto;
        text-align: left;
    }

    .gallery-item-table td.action-cell {
        justify-content: center;
        background: #f8f9fc;
        padding: 15px !important;
    }

    .gallery-item-table td.action-cell::before {
        display: none;
    }
}
</style>