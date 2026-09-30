@php
    $isEdit = isset($page) && $page instanceof \App\Models\Page;
    $actionUrl = $isEdit ? route('admin.pages.update', $page->id) : route('admin.pages.store');
    $heroConfig = $isEdit ? ($page->hero_config ?? []) : [];
    $heroHeading = data_get($heroConfig, 'heading');
    $heroOverlay = data_get($heroConfig, 'overlay');
    $heroFocalPosition = data_get($heroConfig, 'focal_position', 'center center');
    $heroCtaLabel = data_get($heroConfig, 'cta.label');
    $heroCtaUrl = data_get($heroConfig, 'cta.url');
    $heroSliderMode = (bool) data_get($heroConfig, 'slider_mode', false);
    $publishedAtValue = $isEdit && $page->published_at ? $page->published_at->format('Y-m-d\TH:i') : '';
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit)@method('PUT')@endif

    <div class="row">
        <div class="col-md-8 mb-3"><label class="font-weight-bold">Page Title <span class="text-danger">*</span></label><input type="text" name="title" id="page_title" class="form-control" value="{{ old('title', $isEdit ? $page->title : '') }}" required maxlength="190" placeholder="e.g. About Us"><div class="invalid-feedback error-title"></div></div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Template <span class="text-danger">*</span></label><input type="text" name="template" class="form-control" value="{{ old('template', $isEdit ? $page->template : 'default') }}" required maxlength="100" pattern="[A-Za-z0-9_-]+" placeholder="default"><div class="invalid-feedback error-template"></div><small class="text-muted">Letters, numbers, underscore and hyphen only.</small></div>
        <div class="col-md-8 mb-3"><label class="font-weight-bold">Slug <span class="text-danger">*</span></label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text">/</span></div><input type="text" name="slug" id="page_slug" class="form-control" value="{{ old('slug', $isEdit ? $page->slug : '') }}" required maxlength="190" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" placeholder="about-us"></div><div class="invalid-feedback d-block error-slug"></div><small class="text-muted">Lowercase URL slug. Example: <code>about-us</code>.</small></div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Status <span class="text-danger">*</span></label><select name="status" id="page_status" class="form-control" required>@foreach(\App\Models\Page::STATUSES as $value => $label)<option value="{{ $value }}" {{ old('status', $isEdit ? $page->status : 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select><div class="invalid-feedback error-status"></div></div>
        <div class="col-md-12 mb-3"><label class="font-weight-bold">Excerpt</label><textarea name="excerpt" id="page_excerpt" class="form-control tinymce-editor" rows="8" data-editor-height="320" placeholder="Page summary or rich text content...">{{ old('excerpt', $isEdit ? $page->excerpt : '') }}</textarea><div class="invalid-feedback error-excerpt"></div><small class="text-muted">This database field is <code>TEXT</code>, so it uses the global TinyMCE editor.</small></div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Homepage <span class="text-danger">*</span></label><select name="is_homepage" class="form-control" required><option value="0" {{ old('is_homepage', $isEdit ? (int) $page->is_homepage : 0) == 0 ? 'selected' : '' }}>No</option><option value="1" {{ old('is_homepage', $isEdit ? (int) $page->is_homepage : 0) == 1 ? 'selected' : '' }}>Yes — Make Homepage</option></select><div class="invalid-feedback error-is_homepage"></div><small class="text-muted">Only one page can be the active homepage.</small></div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Show Header <span class="text-danger">*</span></label><select name="show_header" class="form-control" required><option value="1" {{ old('show_header', $isEdit ? (int) $page->show_header : 1) == 1 ? 'selected' : '' }}>Yes</option><option value="0" {{ old('show_header', $isEdit ? (int) $page->show_header : 1) == 0 ? 'selected' : '' }}>No</option></select><div class="invalid-feedback error-show_header"></div></div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Show Footer <span class="text-danger">*</span></label><select name="show_footer" class="form-control" required><option value="1" {{ old('show_footer', $isEdit ? (int) $page->show_footer : 1) == 1 ? 'selected' : '' }}>Yes</option><option value="0" {{ old('show_footer', $isEdit ? (int) $page->show_footer : 1) == 0 ? 'selected' : '' }}>No</option></select><div class="invalid-feedback error-show_footer"></div></div>
        <div class="col-md-6 mb-3"><label class="font-weight-bold">Published At</label><input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', $publishedAtValue) }}"><div class="invalid-feedback error-published_at"></div><small class="text-muted">Published pages without a date automatically receive the current time.</small></div>
    </div>

    <hr>
    <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-image mr-1"></i>Hero Configuration</h6>

    <div class="row">
        <div class="col-md-8 mb-3"><label class="font-weight-bold">Hero Heading</label><input type="text" name="hero_heading" class="form-control" value="{{ old('hero_heading', $heroHeading) }}" maxlength="190" placeholder="Hero headline"><div class="invalid-feedback error-hero_heading"></div></div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Overlay Opacity</label><input type="number" name="hero_overlay" class="form-control" value="{{ old('hero_overlay', $heroOverlay) }}" min="0" max="1" step="0.05" placeholder="0.45"><div class="invalid-feedback error-hero_overlay"></div><small class="text-muted">0 = transparent, 1 = fully dark.</small></div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Focal Position</label><input type="text" name="hero_focal_position" class="form-control" value="{{ old('hero_focal_position', $heroFocalPosition) }}" maxlength="50" placeholder="center center"><div class="invalid-feedback error-hero_focal_position"></div></div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">CTA Label</label><input type="text" name="hero_cta_label" class="form-control" value="{{ old('hero_cta_label', $heroCtaLabel) }}" maxlength="100" placeholder="Get Free Quote"><div class="invalid-feedback error-hero_cta_label"></div></div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Slider Mode <span class="text-danger">*</span></label><select name="hero_slider_mode" class="form-control" required><option value="0" {{ old('hero_slider_mode', (int) $heroSliderMode) == 0 ? 'selected' : '' }}>Disabled</option><option value="1" {{ old('hero_slider_mode', (int) $heroSliderMode) == 1 ? 'selected' : '' }}>Enabled</option></select><div class="invalid-feedback error-hero_slider_mode"></div></div>
        <div class="col-md-12 mb-3"><label class="font-weight-bold">CTA URL</label><input type="text" name="hero_cta_url" class="form-control" value="{{ old('hero_cta_url', $heroCtaUrl) }}" maxlength="500" placeholder="/quote or https://example.com/quote"><div class="invalid-feedback error-hero_cta_url"></div><small class="text-muted">Relative paths and HTTPS URLs are supported. Script/data URLs are blocked.</small></div>
    </div>

    <hr>
    <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-photo-video mr-1"></i>Hero Media</h6>
    <div class="alert alert-info py-2"><i class="fas fa-info-circle mr-1"></i>Uploading or selecting a new set replaces the existing collection. Maximum 10 images per desktop/mobile collection.</div>

    @foreach(['hero_desktop' => 'Desktop Hero Images', 'hero_mobile' => 'Mobile Hero Images'] as $collection => $label)
        @php
            $existingMedia = $isEdit ? $page->getMedia($collection) : collect();
        @endphp
        <div class="col-12 px-0 mb-4 hero-media-block" data-collection="{{ $collection }}">
            <div class="card border shadow-none mb-0"><div class="card-header bg-light py-2"><strong>{{ $label }}</strong></div><div class="card-body">
                <div class="d-flex flex-wrap align-items-center mb-2" style="gap:8px;"><input type="file" name="{{ $collection }}[]" id="{{ $collection }}" class="form-control-file hero-file-input" accept="image/jpeg,image/png,image/webp,image/gif" multiple>@if(auth('admin')->user()?->can('media_list'))<button type="button" class="btn btn-outline-primary btn-sm btn-choose-page-media" data-collection="{{ $collection }}" data-label="{{ $label }}"><i class="fas fa-images mr-1"></i>Media Picker</button>@endif <button type="button" class="btn btn-outline-danger btn-sm btn-clear-page-media" data-collection="{{ $collection }}"><i class="fas fa-times mr-1"></i>Clear</button></div>
                <input type="hidden" name="{{ $collection }}_media_ids" id="{{ $collection }}_media_ids" value="">
                <input type="hidden" name="{{ $collection }}_clear" id="{{ $collection }}_clear" value="0">
                <div class="text-danger small mb-2 error-{{ $collection }}"></div><div class="text-danger small mb-2 error-{{ $collection }}_media_ids"></div>
                <div class="d-flex flex-wrap media-preview-grid" id="{{ $collection }}_preview" style="gap:10px;">
                    @if($existingMedia->count() > 0)
                        @foreach($existingMedia as $media)<div class="border rounded bg-light p-1 text-center" style="width:110px;"><img src="{{ $media->getUrl() }}" alt="{{ $media->name }}" class="rounded" style="width:100px;height:72px;object-fit:cover;"><small class="text-muted d-block text-truncate mt-1" title="{{ $media->file_name }}">{{ $media->file_name }}</small></div>@endforeach
                    @else
                        <div class="text-muted small empty-media-message"><i class="far fa-images mr-1"></i>No images selected.</div>
                    @endif
                </div>
            </div></div>
        </div>
    @endforeach

    <div class="text-right border-top pt-3 mt-2 bg-white rounded-bottom"><button type="button" class="btn btn-light border font-weight-bold px-4 mr-2 shadow-sm" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary font-weight-bold px-5 shadow-sm"><i class="fas fa-save mr-2"></i>{{ $isEdit ? 'Update Page' : 'Save Page' }}</button></div>
</form>

<script>
(() => {
    const isEdit = @json($isEdit);
    let slugTouched = isEdit;

    function makeSlug(value) {
        return value.toString().toLowerCase().trim().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
    }

    $('#page_slug').on('input', function() { slugTouched = true; });
    $('#page_title').on('input', function() { if (!slugTouched) $('#page_slug').val(makeSlug($(this).val())); });

    function renderPickerPreview(collection, items) {
        const $preview = $('#' + collection + '_preview');
        $preview.empty();
        items.forEach(function(media) { $preview.append('<div class="border rounded bg-light p-1 text-center" style="width:110px;"><img src="' + media.url + '" alt="' + $('<div>').text(media.name || 'Media').html() + '" class="rounded" style="width:100px;height:72px;object-fit:cover;"><small class="text-muted d-block text-truncate mt-1">' + $('<div>').text(media.file_name || media.name || '').html() + '</small></div>'); });
        if (!items.length) $preview.html('<div class="text-muted small empty-media-message"><i class="far fa-images mr-1"></i>No images selected.</div>');
    }

    $('.btn-choose-page-media').on('click', function() {
        const collection = $(this).data('collection');
        const label = $(this).data('label');
        if (typeof MediaPicker === 'undefined') return Swal.fire('Error', 'Media Picker is not available on this page.', 'error');
        MediaPicker.open(function(selected) {
            const items = Array.isArray(selected) ? selected : [selected];
            const ids = items.map(function(item) { return item.id; });
            $('#' + collection).val('');
            $('#' + collection + '_media_ids').val(JSON.stringify(ids));
            $('#' + collection + '_clear').val('0');
            renderPickerPreview(collection, items);
        }, { type: 'image', multiple: true, max: 10, title: 'Choose ' + label });
    });

    $('.hero-file-input').on('change', function(event) {
        const collection = $(this).attr('id');
        const files = Array.from(event.target.files || []);
        $('#' + collection + '_media_ids').val('');
        $('#' + collection + '_clear').val('0');
        const $preview = $('#' + collection + '_preview').empty();
        if (!files.length) return $preview.html('<div class="text-muted small empty-media-message"><i class="far fa-images mr-1"></i>No images selected.</div>');
        files.slice(0, 10).forEach(function(file) { const reader = new FileReader(); reader.onload = function(e) { $preview.append('<div class="border rounded bg-light p-1 text-center" style="width:110px;"><img src="' + e.target.result + '" alt="Preview" class="rounded" style="width:100px;height:72px;object-fit:cover;"><small class="text-muted d-block text-truncate mt-1">' + $('<div>').text(file.name).html() + '</small></div>'); }; reader.readAsDataURL(file); });
    });

    $('.btn-clear-page-media').on('click', function() {
        const collection = $(this).data('collection');
        $('#' + collection).val('');
        $('#' + collection + '_media_ids').val('');
        $('#' + collection + '_clear').val('1');
        $('#' + collection + '_preview').html('<div class="text-muted small empty-media-message"><i class="far fa-images mr-1"></i>No images selected.</div>');
    });
})();
</script>
