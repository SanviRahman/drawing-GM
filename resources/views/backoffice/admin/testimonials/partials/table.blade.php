<div class="table-responsive">
    <table class="table table-hover mb-0 text-nowrap testimonial-table">
        <thead class="thead-light">
            <tr>
                <th width="40" class="text-center"><input type="checkbox" id="checkAll"></th>
                <th>Preview</th>
                <th>Customer</th>
                <th>Type</th>
                <th>Rating</th>
                <th>Source</th>
                <th>Featured</th>
                <th>Status</th>
                <th>Order</th>
                <th width="150" class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($testimonials as $testimonial)
            @php
            $preview=$testimonial->hasMedia('testimonial_screenshot')?$testimonial->getFirstMediaUrl('testimonial_screenshot'):($testimonial->hasMedia('photo')?$testimonial->getFirstMediaUrl('photo'):null);
            @endphp
            <tr>
                <td class="text-center align-middle"><input type="checkbox" class="row-checkbox"
                        value="{{ $testimonial->id }}"></td>
                <td class="align-middle">@if($preview)<img src="{{ $preview }}" width="52" height="52"
                        class="rounded border" style="object-fit:cover;">@else<span class="text-muted"><i
                            class="far fa-image fa-2x"></i></span>@endif</td>
                <td class="align-middle">
                    <strong>{{ $testimonial->customer_name }}</strong>@if($testimonial->customer_title)<div
                        class="small text-muted">{{ $testimonial->customer_title }}</div>@endif<div
                        class="small text-muted">
                        {{ \Illuminate\Support\Str::limit(strip_tags($testimonial->review),60) }}</div>
                </td>
                <td class="align-middle"><span
                        class="badge badge-info">{{ ucfirst(str_replace('_',' ',$testimonial->type)) }}</span></td>
                <td class="align-middle">@if($testimonial->rating)<span
                        class="text-warning">{{ str_repeat('★',$testimonial->rating) }}</span>@else<span
                        class="text-muted">—</span>@endif</td>
                <td class="align-middle">{{ ucfirst($testimonial->source) }}</td>
                <td class="align-middle">@if($testimonial->is_featured)<span
                        class="badge badge-warning">Featured</span>@else<span class="text-muted">No</span>@endif</td>
                <td class="align-middle">@if($testimonial->is_active)<span
                        class="badge badge-success">Active</span>@else<span
                        class="badge badge-secondary">Inactive</span>@endif</td>
                <td class="align-middle">{{ $testimonial->sort_order }}</td>
                <td class="text-center align-middle">
                    @if($isTrash??false)
                    @can('testimonial_restore')<button type="button" class="btn btn-sm btn-outline-success btn-restore"
                        data-url="{{ route('admin.testimonials.restore',$testimonial->id) }}"><i
                            class="fas fa-undo"></i></button>@endcan
                    @can('testimonial_force_delete')<button type="button"
                        class="btn btn-sm btn-outline-danger btn-force-delete"
                        data-url="{{ route('admin.testimonials.force_delete',$testimonial->id) }}"><i
                            class="fas fa-trash-alt"></i></button>@endcan
                    @else
                    @can('testimonial_view')<button type="button" class="btn btn-sm btn-outline-info btn-show"
                        data-url="{{ route('admin.testimonials.show',$testimonial->id) }}"><i
                            class="fas fa-eye"></i></button>@endcan
                    @can('testimonial_update')<button type="button" class="btn btn-sm btn-outline-primary btn-edit"
                        data-url="{{ route('admin.testimonials.edit',$testimonial->id) }}"><i
                            class="fas fa-pen"></i></button>@endcan
                    @can('testimonial_delete')<button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="{{ route('admin.testimonials.destroy',$testimonial->id) }}"><i
                            class="fas fa-trash"></i></button>@endcan
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center py-5 text-muted"><i
                        class="fas fa-comments fa-3x mb-3 text-light"></i>
                    <h5>No Testimonials Found</h5>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($testimonials->hasPages())
<div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
    <div class="small text-muted">Showing {{ $testimonials->firstItem()??0 }} to {{ $testimonials->lastItem()??0 }} of
        {{ $testimonials->total() }}</div>
    <div>{{ $testimonials->links('pagination::bootstrap-4') }}</div>
</div>
@endif