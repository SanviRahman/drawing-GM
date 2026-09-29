@php
    $isTrash = $isTrash ?? false;
    $admin = auth('admin')->user();
    $canView = $admin?->can('section_media_view') ?? false;
    $canUpdate = $admin?->can('section_media_update') ?? false;
    $canDelete = $admin?->can('section_media_delete') ?? false;
    $canRestore = $admin?->can('section_media_restore') ?? false;
    $canForceDelete = $admin?->can('section_media_force_delete') ?? false;
@endphp

<div class="table-responsive">
    <table class="table table-hover mb-0 section-media-table">
        <thead class="thead-light">
            <tr>
                <th class="text-center" style="width:45px;"><input type="checkbox" id="checkAll" aria-label="Select all"></th>
                <th style="width:85px;">Preview</th>
                <th style="width:220px;">Page / Section</th>
                <th>Media</th>
                <th style="width:130px;">Role</th>
                <th style="width:90px;">Order</th>
                <th class="text-right" style="width:150px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @if($sectionMedia->count() > 0)

                @foreach($sectionMedia as $item)

                    @php
                        $media = $item->media;
                        $mime = (string) ($media?->mime_type ?? '');
                        $previewUrl = null;

                        if ($media && ! $media->trashed()) {
                            try {
                                $previewUrl = $media->getUrl();
                            } catch (\Throwable $exception) {
                                $previewUrl = null;
                            }
                        }
                    @endphp

                    <tr>
                        <td class="text-center"><input type="checkbox" class="row-checkbox" value="{{ $item->id }}" aria-label="Select section media {{ $item->id }}"></td>

                        <td>
                            <div class="border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden section-media-preview">
                                @if(str_starts_with($mime, 'image/') && $previewUrl)
                                    <img src="{{ $previewUrl }}" alt="{{ $media?->name ?? 'Media' }}" style="width:100%;height:100%;object-fit:cover;">
                                @elseif(str_starts_with($mime, 'video/'))
                                    <i class="fas fa-video text-warning"></i>
                                @else
                                    <i class="fas fa-file-alt text-secondary"></i>
                                @endif
                            </div>
                        </td>

                        <td>
                            <div class="font-weight-bold text-dark">{{ $item->pageSection?->page?->title ?? 'Deleted page' }}</div>
                            <small class="text-muted d-block">#{{ $item->page_section_id }} — {{ $item->pageSection?->sectionDefinition?->name ?? 'Deleted definition' }}</small>

                            @if($item->pageSection?->heading)
                                <small class="text-muted d-block mt-1">{{ \Illuminate\Support\Str::limit($item->pageSection->heading, 55) }}</small>
                            @endif
                        </td>

                        <td>
                            <div class="font-weight-bold text-dark">{{ $media?->name ?? 'Deleted media' }}</div>
                            <small class="text-muted d-block">{{ $media?->file_name ?? '-' }}</small>

                            @if($item->caption_override)
                                <small class="text-muted d-block mt-1 section-media-caption">{{ \Illuminate\Support\Str::limit(strip_tags($item->caption_override), 80) }}</small>
                            @endif

                            @if($media?->trashed())
                                <small class="text-danger d-block mt-1"><i class="fas fa-trash-alt mr-1"></i>Media is in Trash</small>
                            @endif

                            @if($isTrash && $item->deleted_at)
                                <small class="text-danger d-block mt-1">Mapping deleted {{ $item->deleted_at->diffForHumans() }}</small>
                            @endif
                        </td>

                        <td><span class="badge badge-info px-2 py-1"><code class="text-white">{{ $item->role }}</code></span></td>
                        <td><span class="font-weight-bold">{{ $item->sort_order }}</span></td>

                        <td class="text-right text-nowrap">
                            <div class="btn-group btn-group-sm" role="group">

                                @if(!$isTrash)

                                    @if($canView)
                                        <button type="button" class="btn btn-outline-info btn-show" data-url="{{ route('admin.section_media.show', $item->id) }}" title="View"><i class="fas fa-eye"></i></button>
                                    @endif

                                    @if($canUpdate)
                                        <button type="button" class="btn btn-outline-primary btn-edit" data-url="{{ route('admin.section_media.edit', $item->id) }}" title="Edit"><i class="fas fa-pen"></i></button>
                                    @endif

                                    @if($canDelete)
                                        <button type="button" class="btn btn-outline-danger btn-delete" data-url="{{ route('admin.section_media.destroy', $item->id) }}" title="Move to trash"><i class="fas fa-trash"></i></button>
                                    @endif

                                @else

                                    @if($canRestore)
                                        <button type="button" class="btn btn-outline-success btn-restore" data-url="{{ route('admin.section_media.restore', $item->id) }}" title="Restore"><i class="fas fa-undo-alt"></i></button>
                                    @endif

                                    @if($canForceDelete)
                                        <button type="button" class="btn btn-outline-danger btn-force-delete" data-url="{{ route('admin.section_media.force_delete', $item->id) }}" title="Permanently delete"><i class="fas fa-fire-alt"></i></button>
                                    @endif

                                @endif

                            </div>
                        </td>
                    </tr>

                @endforeach

            @else

                <tr>
                    <td colspan="7">
                        <div class="text-center py-5"><div class="mb-3"><span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width:72px;height:72px;"><i class="far fa-images fa-2x text-muted"></i></span></div><h5 class="text-muted mb-1">{{ $isTrash ? 'Trash bin is empty' : 'No section media found' }}</h5><p class="text-muted small mb-0">{{ $isTrash ? 'Deleted section media mappings will appear here.' : 'Attach media to a page section or adjust your filters.' }}</p></div>
                    </td>
                </tr>

            @endif
        </tbody>
    </table>
</div>

@if($sectionMedia->hasPages())
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between px-3 py-3 border-top"><div class="text-muted small mb-2 mb-md-0">Showing <strong>{{ $sectionMedia->firstItem() ?? 0 }}</strong> to <strong>{{ $sectionMedia->lastItem() ?? 0 }}</strong> of <strong>{{ $sectionMedia->total() }}</strong> section media records</div><div>{{ $sectionMedia->links('pagination::bootstrap-4') }}</div></div>
@endif
