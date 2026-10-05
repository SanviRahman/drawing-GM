@php
    $isTrash = $isTrash ?? false;
@endphp

<div class="table-responsive table-responsive-custom">
    <table class="table table-hover border-bottom category-table mb-0 text-nowrap">
        <thead class="thead-light">
        <tr>
            <th style="width: 40px;" class="text-center align-middle">
                <input type="checkbox" id="checkAll">
            </th>
            <th class="align-middle">Name</th>
            <th class="align-middle">Slug</th>
            <th class="align-middle">Description</th>
            <th style="width: 100px;" class="text-center align-middle">Status</th>
            <th style="width: 125px;" class="text-center align-middle">Updated</th>
            <th style="width: 190px;" class="text-center align-middle">Actions</th>
        </tr>
        </thead>

        <tbody>
        @forelse($categories as $category)
            @php
                $descriptionPreview = \Illuminate\Support\Str::limit(
                    trim(strip_tags((string) $category->description)),
                    90
                );
            @endphp

            <tr>
                <td data-label="Select" class="text-center align-middle">
                    <input type="checkbox" class="row-checkbox" value="{{ $category->id }}">
                </td>

                <td data-label="Name" class="align-middle font-weight-bold text-dark">
                    {{ $category->name }}
                </td>

                <td data-label="Slug" class="align-middle">
                    <code>{{ $category->slug }}</code>
                </td>

                <td data-label="Description" class="align-middle text-muted category-description-preview">
                    {{ $descriptionPreview !== '' ? $descriptionPreview : '—' }}
                </td>

                <td data-label="Status" class="text-center align-middle">
                    <span class="badge badge-{{ $category->is_active ? 'success' : 'secondary' }} px-2 py-1 shadow-sm">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>

                <td data-label="Updated" class="text-center align-middle text-muted small">
                    {{ $category->updated_at?->format('d M Y') ?? '—' }}
                </td>

                <td data-label="Actions" class="text-center align-middle action-cell">
                    @if($isTrash)
                        @can('blog_category_restore')
                            <button type="button"
                                    class="btn btn-sm btn-outline-success btn-restore shadow-sm mx-1"
                                    data-url="{{ route('admin.categories.restore', $category->id) }}"
                                    title="Restore">
                                <i class="fas fa-undo"></i>
                            </button>
                        @endcan

                        @can('blog_category_force_delete')
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-force-delete shadow-sm mx-1"
                                    data-url="{{ route('admin.categories.force_delete', $category->id) }}"
                                    title="Permanent Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        @endcan
                    @else
                        @can('blog_category_view')
                            <button type="button"
                                    class="btn btn-sm btn-outline-info btn-show shadow-sm mx-1"
                                    data-url="{{ route('admin.categories.show', $category->id) }}"
                                    title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                        @endcan

                        @can('blog_category_update')
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-edit shadow-sm mx-1"
                                    data-url="{{ route('admin.categories.edit', $category->id) }}"
                                    title="Edit">
                                <i class="fas fa-pen"></i>
                            </button>
                        @endcan

                        @can('blog_category_toggle')
                            <button type="button"
                                    class="btn btn-sm btn-outline-{{ $category->is_active ? 'warning' : 'success' }} btn-toggle shadow-sm mx-1"
                                    data-url="{{ route('admin.categories.toggle', $category->id) }}"
                                    title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fas fa-{{ $category->is_active ? 'pause' : 'play' }}"></i>
                            </button>
                        @endcan

                        @can('blog_category_delete')
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-delete shadow-sm mx-1"
                                    data-url="{{ route('admin.categories.destroy', $category->id) }}"
                                    title="Trash">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endcan
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-folder-open fa-3x mb-3 text-light"></i>
                        <h5 class="font-weight-bold">No Categories Found</h5>
                        <p class="mb-0 small">No category data is available for the current filter.</p>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($categories->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top bg-light">
        <div class="text-muted small font-weight-bold mb-2 mb-md-0">
            Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} of {{ $categories->total() }} entries
        </div>
        <div class="m-0 pagination-sm">
            {!! $categories->appends(request()->query())->links('pagination::bootstrap-4') !!}
        </div>
    </div>
@endif
