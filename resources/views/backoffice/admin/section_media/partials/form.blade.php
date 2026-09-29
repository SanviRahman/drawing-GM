@php
    $isEdit = isset($sectionMedia);
    $model = $sectionMedia ?? null;
    $selectedSection = old('page_section_id', $model?->page_section_id ?? $selectedPageSectionId);
    $currentMedia = $model?->media;
    $currentMediaUrl = null;

    if ($currentMedia && ! $currentMedia->trashed()) {
        try {
            $currentMediaUrl = $currentMedia->getUrl();
        } catch (\Throwable $exception) {
            $currentMediaUrl = null;
        }
    }
@endphp

<form id="ajax-form" action="{{ $isEdit ? route('admin.section_media.update', $model->id) : route('admin.section_media.store') }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="form-group">
                <label for="page_section_id">Page Section <span class="text-danger">*</span></label>
                <select name="page_section_id" id="page_section_id" class="form-control" required>
                    <option value="">Select Page Section</option>
                    @foreach($pageSections as $section)
                        <option value="{{ $section->id }}" @selected((string) $selectedSection === (string) $section->id)>#{{ $section->id }} — {{ $section->page?->title ?? 'Deleted Page' }} / {{ $section->sectionDefinition?->name ?? 'Deleted Definition' }}{{ $section->heading ? ' — ' . \Illuminate\Support\Str::limit($section->heading, 45) : '' }}{{ $section->trashed() ? ' [TRASHED]' : '' }}</option>
                    @endforeach
                </select>
                <span class="invalid-feedback error-page_section_id d-block"></span>
            </div>

            <div class="form-group">
                <label>Media <span class="text-danger">*</span></label>
                <input type="hidden" name="media_id" id="section_media_id" value="{{ old('media_id', $model?->media_id) }}">
                <div class="border rounded bg-light p-3">
                    <div id="selected_media_preview" class="mb-3">
                        @if($currentMedia)
                            <div class="d-flex align-items-center">
                                <div class="border rounded bg-white d-flex align-items-center justify-content-center mr-3 overflow-hidden" style="width:100px;height:78px;">
                                    @if(str_starts_with((string) $currentMedia->mime_type, 'image/') && $currentMediaUrl)
                                        <img src="{{ $currentMediaUrl }}" alt="{{ $currentMedia->name }}" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        <i class="fas fa-file-alt fa-2x text-secondary"></i>
                                    @endif
                                </div>
                                <div><div class="font-weight-bold">{{ $currentMedia->name }}</div><small class="text-muted">{{ $currentMedia->file_name }}</small>@if($currentMedia->trashed())<div class="text-danger small mt-1">Referenced media is currently in Trash.</div>@endif</div>
                            </div>
                        @else
                            <div class="text-muted small"><i class="far fa-images mr-1"></i>No media selected.</div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnChooseSectionMedia"><i class="far fa-images mr-1"></i>Choose from Media Library</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm ml-1" id="btnClearSectionMedia"><i class="fas fa-times mr-1"></i>Clear</button>
                </div>
                <span class="invalid-feedback error-media_id d-block"></span>
                <small class="form-text text-muted">This creates a SectionMedia mapping to the existing Spatie media row. It does not move, reassign, or duplicate the source media file.</small>
            </div>

            <div class="form-group">
                <label for="caption_override">Caption Override</label>
                <textarea name="caption_override" id="caption_override" class="form-control tinymce-editor" rows="8" data-editor-height="280">{{ old('caption_override', $model?->caption_override) }}</textarea>
                <span class="invalid-feedback error-caption_override d-block"></span>
                <small class="form-text text-muted">This migration uses a TEXT column, so the project TinyMCE rule applies here.</small>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <div class="form-group">
                        <label for="role">Role <span class="text-danger">*</span></label>
                        <input type="text" name="role" id="role" class="form-control" value="{{ old('role', $model?->role ?? 'primary') }}" maxlength="80" autocomplete="off" placeholder="primary, background, gallery, icon..." required>
                        <span class="invalid-feedback error-role d-block"></span>
                        <small class="form-text text-muted">Use a stable snake_case / kebab-case key expected by the selected section renderer.</small>
                    </div>

                    <div class="form-group mb-0">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', $model?->sort_order) }}" min="0" step="1" placeholder="Auto">
                        <span class="invalid-feedback error-sort_order d-block"></span>
                        <small class="form-text text-muted">Leave empty on create to append automatically in increments of 10.</small>
                    </div>
                </div>
            </div>

            <div class="alert alert-info small mt-3 mb-0"><i class="fas fa-info-circle mr-1"></i>The database uniqueness rule prevents the same media item being attached to the same Page Section with the same role more than once. If the previous mapping is in Trash, restore it instead.</div>
        </div>
    </div>

    <div class="d-flex justify-content-end border-top pt-3 mt-4"><button type="button" class="btn btn-light mr-2" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>{{ $isEdit ? 'Update Section Media' : 'Attach Media' }}</button></div>
</form>

<script>
(function() {
    function escapeText(value) { return $('<div>').text(value || '').html(); }

    function renderSelectedMedia(media) {
        const $preview = $('#selected_media_preview').empty();
        const $row = $('<div>').addClass('d-flex align-items-center');
        const $box = $('<div>').addClass('border rounded bg-white d-flex align-items-center justify-content-center mr-3 overflow-hidden').css({ width: '100px', height: '78px' });
        const mime = String(media.mime_type || '');

        if (mime.startsWith('image/') && media.url) {
            $box.append($('<img>').attr({ src: media.url, alt: media.name || 'Selected media' }).css({ width: '100%', height: '100%', objectFit: 'cover' }));
        } else {
            $box.append($('<i>').addClass(mime.startsWith('video/') ? 'fas fa-video fa-2x text-warning' : 'fas fa-file-alt fa-2x text-secondary'));
        }

        const $info = $('<div>');
        $info.append($('<div>').addClass('font-weight-bold').text(media.name || 'Media #' + media.id));
        $info.append($('<small>').addClass('text-muted d-block').text(media.file_name || mime || ''));

        $row.append($box).append($info);
        $preview.append($row);
    }

    $('#btnChooseSectionMedia').on('click', function() {
        if (typeof MediaPicker === 'undefined') return Swal.fire('Error', 'Media Picker is not available on this page.', 'error');

        MediaPicker.open(function(selected) {
            const media = Array.isArray(selected) ? selected[0] : selected;
            if (!media || !media.id) return;
            $('#section_media_id').val(media.id);
            renderSelectedMedia(media);
        }, { multiple: false, title: 'Choose Section Media' });
    });

    $('#btnClearSectionMedia').on('click', function() {
        $('#section_media_id').val('');
        $('#selected_media_preview').html('<div class="text-muted small"><i class="far fa-images mr-1"></i>No media selected.</div>');
    });
})();
</script>
