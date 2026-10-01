@php $isTrash=$isTrash??false; @endphp
<div class="table-responsive">
    <table class="table table-hover border-bottom video-table mb-0 text-nowrap">
        <thead class="thead-light">
            <tr>
                <th style="width:40px;" class="text-center"><input type="checkbox" id="checkAll"></th>
                <th style="width:80px;">Poster</th>
                <th>Title</th>
                <th>Source Priority</th>
                <th>Processing</th>
                <th>Status</th>
                <th>Order</th>
                <th style="width:190px;" class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($videos as $video)
            @php
            $posterUrl=$video->poster_url;$captionPreview=\Illuminate\Support\Str::limit(trim(strip_tags((string)$video->caption)),70);$available=$video->available_sources;
            @endphp
            <tr>
                <td class="text-center"><input type="checkbox" class="row-checkbox" value="{{ $video->id }}"></td>
                <td>@if($posterUrl)<img src="{{ $posterUrl }}" alt="{{ $video->title }}"
                        class="rounded border video-thumb">@else<div
                        class="rounded border bg-light text-muted video-empty-thumb"><i class="fas fa-video"></i></div>
                    @endif</td>
                <td><strong class="d-block">{{ $video->title }}</strong>@if($captionPreview)<small
                        class="text-muted caption-preview d-block">{{ $captionPreview }}</small>@endif</td>
                <td><span class="badge badge-primary px-2 py-1 mb-1">Using: {{ $video->source_type_label }}</span>
                    <div>@if(in_array('youtube',$available,true))<span class="badge badge-danger mr-1">1
                            YouTube</span>@endif @if(in_array('embed',$available,true))<span
                            class="badge badge-info mr-1">2 Embed</span>@endif
                        @if(in_array('upload',$available,true))<span class="badge badge-success">3 Upload</span>@endif
                    </div>
                </td>
                <td><span
                        class="badge badge-{{ $video->processing_status==='ready'?'success':($video->processing_status==='failed'?'danger':'warning') }} px-2 py-1">{{ ucfirst($video->processing_status) }}</span>
                </td>
                <td><span
                        class="badge badge-{{ $video->is_active?'success':'secondary' }} px-2 py-1">{{ $video->is_active?'Active':'Inactive' }}</span>
                </td>
                <td>{{ $video->sort_order }}</td>
                <td class="text-right">@if($isTrash)@can('video_restore')<button type="button"
                        class="btn btn-sm btn-outline-success btn-restore"
                        data-url="{{ route('admin.videos.restore',$video->id) }}" title="Restore"><i
                            class="fas fa-undo"></i></button>@endcan @can('video_force_delete')<button type="button"
                        class="btn btn-sm btn-outline-danger btn-force-delete"
                        data-url="{{ route('admin.videos.force_delete',$video->id) }}" title="Delete Permanently"><i
                            class="fas fa-times"></i></button>@endcan @else @can('video_view')<button type="button"
                        class="btn btn-sm btn-outline-info btn-show"
                        data-url="{{ route('admin.videos.show',$video->id) }}" title="View"><i
                            class="fas fa-eye"></i></button>@endcan @can('video_update')<button type="button"
                        class="btn btn-sm btn-outline-primary btn-edit"
                        data-url="{{ route('admin.videos.edit',$video->id) }}" title="Edit"><i
                            class="fas fa-pen"></i></button>@endcan @can('video_toggle')<button type="button"
                        class="btn btn-sm btn-outline-warning btn-toggle"
                        data-url="{{ route('admin.videos.toggle',$video->id) }}" title="Toggle"><i
                            class="fas fa-power-off"></i></button>@endcan @can('video_delete')<button type="button"
                        class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="{{ route('admin.videos.destroy',$video->id) }}" title="Trash"><i
                            class="fas fa-trash"></i></button>@endcan @endif</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="text-muted"><i class="fas fa-video fa-3x mb-3 text-light"></i>
                        <h5 class="font-weight-bold">No videos found</h5>
                        <p class="mb-0 small">
                            {{ $isTrash?'Trash is empty or no records match the filters.':'Create a video or adjust your filters.' }}
                        </p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($videos->hasPages())<div
    class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top bg-light">
    <div class="text-muted small font-weight-bold mb-2 mb-md-0">Showing {{ $videos->firstItem()??0 }} to
        {{ $videos->lastItem()??0 }} of {{ $videos->total() }} entries</div>
    <div class="m-0 pagination-sm">{!! $videos->appends(request()->query())->links('pagination::bootstrap-4') !!}</div>
</div>@endif