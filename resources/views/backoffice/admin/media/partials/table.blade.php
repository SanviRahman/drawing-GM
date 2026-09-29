@php
    $isTrash = $isTrash ?? false;
    $admin = auth('admin')->user();
    $canView = $admin?->can('media_view') ?? false;
    $canUpdate = $admin?->can('media_update') ?? false;
    $canDownload = $admin?->can('media_download') ?? false;
    $canDelete = $admin?->can('media_delete') ?? false;
    $canRestore = $admin?->can('media_restore') ?? false;
    $canForceDelete = $admin?->can('media_force_delete') ?? false;
@endphp

<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="thead-light">
            <tr>
                <th class="text-center align-middle" style="width:45px;"><input type="checkbox" id="checkAll" aria-label="Select all media"></th>
                <th class="align-middle" style="width:90px;">Preview</th>
                <th class="align-middle">Media</th>
                <th class="align-middle" style="width:150px;">Owner</th>
                <th class="align-middle" style="width:160px;">Collection</th>
                <th class="align-middle" style="width:105px;">Type</th>
                <th class="align-middle" style="width:105px;">Size</th>
                <th class="text-right align-middle" style="width:175px;">Actions</th>
            </tr>
        </thead>

        <tbody>
            @if($media->count() > 0)

                @foreach($media as $item)

                    @php
                        $mimeType = (string) $item->mime_type;
                        $isImage = str_starts_with($mimeType, 'image/');
                        $isVideo = str_starts_with($mimeType, 'video/');
                        $extension = strtoupper(pathinfo((string) $item->file_name, PATHINFO_EXTENSION));
                        $extension = $extension !== '' ? $extension : 'FILE';
                        $previewUrl = null;

                        if ($item->isPickerSafe()) {
                            try {
                                $previewUrl = $item->getUrl();
                            } catch (\Throwable $exception) {
                                $previewUrl = null;
                            }
                        }

                        $itemSize = (int) $item->size;

                        if ($itemSize >= 1073741824) {
                            $size = number_format($itemSize / 1073741824, 2) . ' GB';
                        } elseif ($itemSize >= 1048576) {
                            $size = number_format($itemSize / 1048576, 1) . ' MB';
                        } elseif ($itemSize >= 1024) {
                            $size = number_format($itemSize / 1024, 1) . ' KB';
                        } else {
                            $size = number_format($itemSize) . ' B';
                        }

                        $ownerType = class_basename((string) $item->model_type);
                        $createdAt = $item->created_at ? $item->created_at->format('d M Y, h:i A') : null;
                        $typeLabel = $isImage ? 'Image' : ($isVideo ? 'Video' : 'File');
                        $typeBadge = $isImage ? 'success' : ($isVideo ? 'warning' : 'secondary');
                        $typeIcon = $isImage ? 'fa-image' : ($isVideo ? 'fa-video' : 'fa-file-alt');
                    @endphp

                    <tr class="{{ $isTrash ? 'table-light' : '' }}">

                        {{-- Checkbox --}}
                        <td class="text-center align-middle"><input type="checkbox" class="row-checkbox" value="{{ $item->id }}" aria-label="Select {{ $item->name }}"></td>

                        {{-- Preview --}}
                        <td class="align-middle">
                            <div class="border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden shadow-sm" style="width:64px;height:54px;">
                                @if($isImage && $previewUrl)
                                    <img src="{{ $previewUrl }}" alt="{{ $item->name }}" class="img-fluid" loading="lazy" style="width:100%;height:100%;object-fit:cover;">
                                @elseif($isVideo)
                                    <div class="text-center"><i class="fas fa-video fa-lg text-warning"></i></div>
                                @else
                                    <div class="text-center"><i class="fas fa-file-alt fa-lg text-secondary"></i></div>
                                @endif
                            </div>
                        </td>

                        {{-- Media Information --}}
                        <td class="align-middle">
                            <div class="font-weight-bold text-dark text-truncate" style="max-width:300px;" title="{{ $item->name }}">{{ $item->name }}</div>
                            <div class="text-muted small text-truncate" style="max-width:320px;" title="{{ $item->file_name }}">{{ $item->file_name }}</div>

                            <div class="mt-1">
                                <span class="badge badge-light border mr-1">{{ $extension }}</span>
                                @if($mimeType !== '')
                                    <small class="text-muted">{{ $mimeType }}</small>
                                @endif
                            </div>

                            @if($createdAt)
                                <small class="text-muted d-block mt-1"><i class="far fa-clock mr-1"></i>{{ $createdAt }}</small>
                            @endif

                            @if($isTrash && $item->deleted_at)
                                <small class="text-danger d-block mt-1"><i class="fas fa-trash-alt mr-1"></i>Deleted: {{ $item->deleted_at->format('d M Y, h:i A') }}</small>
                            @endif
                        </td>

                        {{-- Owner --}}
                        <td class="align-middle">
                            <span class="badge badge-light border px-2 py-1" title="{{ $item->model_type }}"><i class="fas fa-user-circle text-muted mr-1"></i>{{ $ownerType ?: 'Unknown' }} <span class="text-muted">#{{ $item->model_id }}</span></span>
                        </td>

                        {{-- Collection --}}
                        <td class="align-middle">
                            <span class="badge badge-info px-2 py-1" title="{{ $item->collection_name }}">{{ $item->collection_name }}</span>
                            <small class="text-muted d-block mt-1"><i class="fas fa-hdd mr-1"></i>{{ $item->disk }}</small>
                        </td>

                        {{-- Type --}}
                        <td class="align-middle">
                            <span class="badge badge-{{ $typeBadge }} px-2 py-1"><i class="fas {{ $typeIcon }} mr-1"></i>{{ $typeLabel }}</span>
                        </td>

                        {{-- Size --}}
                        <td class="align-middle">
                            <span class="font-weight-bold text-dark">{{ $size }}</span>
                        </td>

                        {{-- Actions --}}
                        <td class="text-right align-middle text-nowrap">
                            <div class="btn-group btn-group-sm" role="group" aria-label="Media actions">

                                @if(!$isTrash)

                                    @if($canView)
                                        <button type="button" class="btn btn-outline-info btn-show" data-url="{{ route('admin.media.show', $item->id) }}" title="View media"><i class="fas fa-eye"></i></button>
                                    @endif

                                    @if($canUpdate)
                                        <button type="button" class="btn btn-outline-primary btn-edit" data-url="{{ route('admin.media.edit', $item->id) }}" title="Edit media"><i class="fas fa-pen"></i></button>
                                    @endif

                                    @if($canDownload)
                                        <a href="{{ route('admin.media.download', $item->id) }}" class="btn btn-outline-secondary" title="Download media"><i class="fas fa-download"></i></a>
                                    @endif

                                    @if($canDelete)
                                        <button type="button" class="btn btn-outline-danger btn-delete" data-url="{{ route('admin.media.destroy', $item->id) }}" title="Move to trash"><i class="fas fa-trash"></i></button>
                                    @endif

                                @else

                                    @if($canRestore)
                                        <button type="button" class="btn btn-outline-success btn-restore" data-url="{{ route('admin.media.restore', $item->id) }}" title="Restore media"><i class="fas fa-undo-alt"></i></button>
                                    @endif

                                    @if($canForceDelete)
                                        <button type="button" class="btn btn-outline-danger btn-force-delete" data-url="{{ route('admin.media.force_delete', $item->id) }}" title="Permanently delete"><i class="fas fa-fire-alt"></i></button>
                                    @endif

                                @endif

                            </div>
                        </td>
                    </tr>

                @endforeach

            @else

                <tr>
                    <td colspan="8">
                        <div class="text-center py-5">
                            <div class="mb-3"><span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width:72px;height:72px;"><i class="far fa-images fa-2x text-muted"></i></span></div>
                            <h5 class="text-muted mb-1">{{ $isTrash ? 'Trash bin is empty' : 'No media found' }}</h5>
                            <p class="text-muted small mb-0">@if($isTrash) Deleted media will appear here. @else Upload media or adjust your filters to see files. @endif</p>
                        </div>
                    </td>
                </tr>

            @endif
        </tbody>
    </table>
</div>

@if($media->hasPages())
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between px-3 py-3 border-top">
        <div class="text-muted small mb-2 mb-md-0">Showing <strong>{{ $media->firstItem() ?? 0 }}</strong> to <strong>{{ $media->lastItem() ?? 0 }}</strong> of <strong>{{ $media->total() }}</strong> media files</div>
        <div>{{ $media->links('pagination::bootstrap-4') }}</div>
    </div>
@endif