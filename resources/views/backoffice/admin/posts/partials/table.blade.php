@php
    $isTrash = $isTrash ?? false;
@endphp

<div class="table-responsive table-responsive-custom">
    <table class="table table-hover border-bottom post-table mb-0 text-nowrap">
        <thead class="thead-light">
        <tr>
            <th style="width:40px;" class="text-center"><input type="checkbox" id="checkAll"></th>
            <th style="width:90px;" class="text-center">Featured</th>
            <th>Post</th>
            <th>Category</th>
            <th>Author</th>
            <th style="width:110px;" class="text-center">Status</th>
            <th style="width:130px;" class="text-center">Published</th>
            <th style="width:260px;" class="text-center">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($posts as $post)
            @php
                $featuredUrl = $post->featured_image_url;
                $excerpt = \Illuminate\Support\Str::limit(trim(strip_tags((string) $post->excerpt)), 85);
            @endphp
            <tr>
                <td data-label="Select" class="text-center"><input type="checkbox" class="row-checkbox" value="{{ $post->id }}"></td>

                <td data-label="Featured" class="text-center">
                    @if($featuredUrl)
                        <img src="{{ $featuredUrl }}" alt="{{ $post->title }}" class="post-thumb border shadow-sm">
                    @else
                        <span class="d-inline-flex align-items-center justify-content-center rounded border bg-light text-muted" style="width:72px;height:52px;"><i class="far fa-image"></i></span>
                    @endif
                </td>

                <td data-label="Post" class="post-title-cell">
                    <div class="font-weight-bold text-dark">{{ $post->title }}</div>
                    <div class="small text-muted"><code>{{ $post->slug }}</code></div>
                    @if($excerpt !== '')<div class="small text-muted mt-1">{{ $excerpt }}</div>@endif
                </td>

                <td data-label="Category">
                    @if($post->category)
                        <span class="badge badge-light border px-2 py-1">{{ $post->category->name }}</span>
                        @if($post->category->trashed())<span class="badge badge-danger">Trashed</span>@endif
                    @else
                        <span class="text-muted">Uncategorized</span>
                    @endif
                </td>

                <td data-label="Author">
                    <span class="font-weight-bold">{{ $post->author?->name ?? 'Unknown' }}</span>
                    @if($post->author?->trashed())<span class="badge badge-danger ml-1">Trashed</span>@endif
                </td>

                <td data-label="Status" class="text-center">
                    <span class="badge badge-{{ $post->statusBadgeClass() }} px-2 py-1 shadow-sm">{{ $post->statusLabel() }}</span>
                </td>

                <td data-label="Published" class="text-center text-muted small">
                    {{ $post->published_at?->format('d M Y') ?? '—' }}
                    @if($post->published_at)<br><small>{{ $post->published_at->format('h:i A') }}</small>@endif
                </td>

                <td data-label="Actions" class="text-center action-cell">
                    @if($isTrash)
                        @can('blog_post_restore')
                            <button type="button" class="btn btn-sm btn-outline-success btn-restore shadow-sm mx-1" data-url="{{ route('admin.posts.restore', $post->id) }}" title="Restore"><i class="fas fa-undo"></i></button>
                        @endcan
                        @can('blog_post_force_delete')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-force-delete shadow-sm mx-1" data-url="{{ route('admin.posts.force_delete', $post->id) }}" title="Permanent Delete"><i class="fas fa-trash-alt"></i></button>
                        @endcan
                    @else
                        @can('blog_post_preview')
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-preview shadow-sm mx-1" data-url="{{ route('admin.posts.preview', $post->id) }}" title="Preview"><i class="fas fa-desktop"></i></button>
                        @endcan
                        @can('blog_post_view')
                            <button type="button" class="btn btn-sm btn-outline-info btn-show shadow-sm mx-1" data-url="{{ route('admin.posts.show', $post->id) }}" title="View"><i class="fas fa-eye"></i></button>
                        @endcan
                        @can('blog_post_update')
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit shadow-sm mx-1" data-url="{{ route('admin.posts.edit', $post->id) }}" title="Edit"><i class="fas fa-pen"></i></button>
                        @endcan

                        @if($post->status === 'published')
                            @can('blog_post_unpublish')
                                <button type="button" class="btn btn-sm btn-outline-warning btn-status-action shadow-sm mx-1" data-url="{{ route('admin.posts.unpublish', $post->id) }}" data-label="Move post to draft" title="Move to Draft"><i class="fas fa-pause"></i></button>
                            @endcan
                        @else
                            @can('blog_post_publish')
                                <button type="button" class="btn btn-sm btn-outline-success btn-status-action shadow-sm mx-1" data-url="{{ route('admin.posts.publish', $post->id) }}" data-label="Publish post" title="Publish"><i class="fas fa-paper-plane"></i></button>
                            @endcan
                        @endif

                        @can('blog_post_duplicate')
                            <button type="button" class="btn btn-sm btn-outline-dark btn-duplicate shadow-sm mx-1" data-url="{{ route('admin.posts.duplicate', $post->id) }}" title="Duplicate"><i class="fas fa-copy"></i></button>
                        @endcan

                        @can('blog_post_delete')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete shadow-sm mx-1" data-url="{{ route('admin.posts.destroy', $post->id) }}" title="Trash"><i class="fas fa-trash"></i></button>
                        @endcan
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-newspaper fa-3x mb-3 text-light"></i>
                        <h5 class="font-weight-bold">{{ $isTrash ? 'Trash Bin Is Empty' : 'No Blog Posts Found' }}</h5>
                        <p class="mb-0 small">{{ $isTrash ? 'Deleted blog posts will appear here.' : 'Create a blog post or adjust the current filters.' }}</p>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($posts->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top bg-light">
        <div class="text-muted small font-weight-bold mb-2 mb-md-0">
            Showing {{ $posts->firstItem() ?? 0 }} to {{ $posts->lastItem() ?? 0 }} of {{ $posts->total() }} entries
        </div>
        <div class="m-0 pagination-sm">{!! $posts->appends(request()->query())->links('pagination::bootstrap-4') !!}</div>
    </div>
@endif
