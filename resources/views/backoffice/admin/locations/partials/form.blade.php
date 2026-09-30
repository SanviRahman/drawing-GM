@php
    $isEdit = isset($location) && $location instanceof \App\Models\Location;
    $actionUrl = $isEdit ? route('admin.locations.update', $location->id) : route('admin.locations.store');
    $heroConfig = $isEdit ? ($location->hero_config ?? []) : [];
    $heroHeading = data_get($heroConfig, 'heading');
    $heroOverlay = data_get($heroConfig, 'overlay');
    $heroFocalPosition = data_get($heroConfig, 'focal_position', 'center center');
    $heroCtaLabel = data_get($heroConfig, 'cta.label');
    $heroCtaUrl = data_get($heroConfig, 'cta.url');
    $publishedAtValue = $isEdit && $location->published_at ? $location->published_at->format('Y-m-d\TH:i') : '';
    $postalCodesValue = old('postal_codes', $isEdit && is_array($location->postal_codes) ? implode(PHP_EOL, $location->postal_codes) : '');
    $mediaCollections = [
        'hero_desktop' => ['label' => 'Desktop Hero', 'multiple' => false, 'max' => 1],
        'hero_mobile' => ['label' => 'Mobile Hero', 'multiple' => false, 'max' => 1],
        'gallery' => ['label' => 'Gallery', 'multiple' => true, 'max' => 20],
    ];
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data">
    @csrf

    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6 mb-2">
            <label class="font-weight-bold mb-1">Location Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="location_name" class="form-control form-control-sm" value="{{ old('name', $isEdit ? $location->name : '') }}" required maxlength="190" placeholder="e.g. Tampines">
            <div class="invalid-feedback error-name"></div>
        </div>

        <div class="col-md-3 mb-2">
            <label class="font-weight-bold mb-1">Region</label>
            <input type="text" name="region" class="form-control form-control-sm" value="{{ old('region', $isEdit ? $location->region : '') }}" maxlength="120" placeholder="e.g. East">
            <div class="invalid-feedback error-region"></div>
        </div>

        <div class="col-md-3 mb-2">
            <label class="font-weight-bold mb-1">Status <span class="text-danger">*</span></label>
            <select name="status" id="location_status" class="form-control form-control-sm" required>
                @foreach(\App\Models\Location::STATUSES as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $isEdit ? $location->status : 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback error-status"></div>
        </div>

        <div class="col-md-8 mb-2">
            <label class="font-weight-bold mb-1">Slug <span class="text-danger">*</span></label>
            <div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">/locations/</span></div><input type="text" name="slug" id="location_slug" class="form-control" value="{{ old('slug', $isEdit ? $location->slug : '') }}" required maxlength="190" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" placeholder="tampines"></div>
            <div class="invalid-feedback d-block error-slug"></div>
        </div>

        <div class="col-md-2 mb-2">
            <label class="font-weight-bold mb-1">Sort Order</label>
            <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ old('sort_order', $isEdit ? $location->sort_order : '') }}" min="0" step="1" placeholder="Auto">
            <div class="invalid-feedback error-sort_order"></div>
        </div>

        <div class="col-md-2 mb-2">
            <label class="font-weight-bold mb-1">Published At</label>
            <input type="datetime-local" name="published_at" class="form-control form-control-sm" value="{{ old('published_at', $publishedAtValue) }}">
            <div class="invalid-feedback error-published_at"></div>
        </div>

        <div class="col-md-12 mb-2">
            <label class="font-weight-bold mb-1">Postal Codes</label>
            <textarea name="postal_codes" class="form-control form-control-sm text-monospace" rows="3" placeholder="Enter one per line or separate with commas">{{ $postalCodesValue }}</textarea>
            <div class="invalid-feedback error-postal_codes"></div>
            <small class="text-muted">Stored as JSON. This is not a TEXT/LONGTEXT content field, so TinyMCE is intentionally not used.</small>
        </div>

        <div class="col-md-12 mb-2">
            <label class="font-weight-bold mb-1">Summary</label>
            <textarea name="summary" id="location_summary" class="form-control tinymce-editor" rows="6" data-editor-height="260" placeholder="Short location summary...">{{ old('summary', $isEdit ? $location->summary : '') }}</textarea>
            <div class="invalid-feedback error-summary"></div>
            <small class="text-muted">Stored as <code>TEXT</code>, therefore it uses TinyMCE.</small>
        </div>

        <div class="col-md-12 mb-2">
            <label class="font-weight-bold mb-1">Content</label>
            <textarea name="content" id="location_content" class="form-control tinymce-editor" rows="10" data-editor-height="380" placeholder="Full location content...">{{ old('content', $isEdit ? $location->content : '') }}</textarea>
            <div class="invalid-feedback error-content"></div>
            <small class="text-muted">Stored as <code>LONGTEXT</code>, therefore it uses TinyMCE.</small>
        </div>
    </div>

    <hr>

    <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-image mr-1"></i>Hero Configuration</h6>

    <div class="row">
        <div class="col-md-8 mb-2"><label class="font-weight-bold mb-1">Hero Heading</label><input type="text" name="hero_heading" class="form-control form-control-sm" value="{{ old('hero_heading', $heroHeading) }}" maxlength="190" placeholder="Hero headline"><div class="invalid-feedback error-hero_heading"></div></div>
        <div class="col-md-4 mb-2"><label class="font-weight-bold mb-1">Overlay Opacity</label><input type="number" name="hero_overlay" class="form-control form-control-sm" value="{{ old('hero_overlay', $heroOverlay) }}" min="0" max="1" step="0.05" placeholder="0.45"><div class="invalid-feedback error-hero_overlay"></div></div>
        <div class="col-md-4 mb-2"><label class="font-weight-bold mb-1">Focal Position</label><input type="text" name="hero_focal_position" class="form-control form-control-sm" value="{{ old('hero_focal_position', $heroFocalPosition) }}" maxlength="50" placeholder="center center"><div class="invalid-feedback error-hero_focal_position"></div></div>
        <div class="col-md-4 mb-2"><label class="font-weight-bold mb-1">CTA Label</label><input type="text" name="hero_cta_label" class="form-control form-control-sm" value="{{ old('hero_cta_label', $heroCtaLabel) }}" maxlength="100" placeholder="Get Free Quote"><div class="invalid-feedback error-hero_cta_label"></div></div>
        <div class="col-md-4 mb-2"><label class="font-weight-bold mb-1">CTA URL</label><input type="text" name="hero_cta_url" class="form-control form-control-sm" value="{{ old('hero_cta_url', $heroCtaUrl) }}" maxlength="500" placeholder="/quote"><div class="invalid-feedback error-hero_cta_url"></div></div>
    </div>

    <hr>

    <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-photo-video mr-1"></i>Location Media</h6>
    <div class="alert alert-info py-2"><i class="fas fa-info-circle mr-1"></i>Desktop and mobile hero are single-image collections. Gallery supports up to 20 images. Every image has preview and remove controls.</div>

    @foreach($mediaCollections as $collection => $config)
        @php
            $existingMedia = $isEdit ? $location->getMedia($collection) : collect();
        @endphp

        <div class="card border shadow-none mb-3 media-block" data-collection="{{ $collection }}">
            <div class="card-header bg-light py-2"><strong>{{ $config['label'] }}</strong></div>
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center mb-2" style="gap:8px;">
                    @if($config['multiple'])
                        <input type="file" name="{{ $collection }}[]" id="{{ $collection }}" class="form-control-file location-media-file" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                    @else
                        <input type="file" name="{{ $collection }}" id="{{ $collection }}" class="form-control-file location-media-file" accept="image/jpeg,image/png,image/webp,image/gif">
                    @endif

                    @if(auth('admin')->user()?->can('media_list'))
                        <button type="button" class="btn btn-outline-primary btn-sm btn-choose-location-media" data-collection="{{ $collection }}" data-label="{{ $config['label'] }}" data-multiple="{{ $config['multiple'] ? 1 : 0 }}" data-max="{{ $config['max'] }}"><i class="fas fa-images mr-1"></i>Media Picker</button>
                    @endif

                    <button type="button" class="btn btn-outline-danger btn-sm btn-clear-location-media" data-collection="{{ $collection }}"><i class="fas fa-trash-alt mr-1"></i>Remove All</button>
                </div>

                <input type="hidden" name="{{ $collection }}_media_ids" id="{{ $collection }}_media_ids" value="">
                <input type="hidden" name="{{ $collection }}_clear" id="{{ $collection }}_clear" value="0">
                <input type="hidden" name="{{ $collection }}_remove_ids" id="{{ $collection }}_remove_ids" value="[]">

                <div class="text-danger small mb-2 error-{{ $collection }}"></div>
                <div class="text-danger small mb-2 error-{{ $collection }}_media_ids"></div>
                <div class="text-danger small mb-2 error-{{ $collection }}_remove_ids"></div>

                <div class="d-flex flex-wrap media-preview-grid" id="{{ $collection }}_preview" style="gap:10px;">
                    @if($existingMedia->count() > 0)
                        @foreach($existingMedia as $media)
                            @php
                                try {
                                    $mediaUrl = $media->getUrl();
                                } catch (\Throwable) {
                                    $mediaUrl = null;
                                }
                            @endphp

                            @if($mediaUrl)
                                <div class="border rounded bg-light p-1 text-center position-relative location-media-preview-item" data-source="existing" data-media-id="{{ $media->id }}" style="width:112px;"><button type="button" class="btn btn-danger btn-sm btn-remove-location-media position-absolute" data-collection="{{ $collection }}" data-source="existing" data-media-id="{{ $media->id }}" title="Remove image" style="right:3px;top:3px;padding:1px 5px;z-index:2;"><i class="fas fa-times"></i></button><img src="{{ $mediaUrl }}" alt="{{ $media->name }}" class="rounded" style="width:100px;height:72px;object-fit:cover;"><small class="text-muted d-block text-truncate mt-1" title="{{ $media->file_name }}">{{ $media->file_name }}</small></div>
                            @endif
                        @endforeach
                    @else
                        <div class="text-muted small empty-media-message"><i class="far fa-images mr-1"></i>No images selected.</div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-end border-top pt-2 mt-3"><button type="button" class="btn btn-light btn-sm mr-2" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i>{{ $isEdit ? 'Update Location' : 'Save Location' }}</button></div>
</form>

<script>
(function() {
    const isEdit = @json($isEdit);
    let slugTouched = isEdit;

    function makeSlug(value) {
        return String(value || '').toLowerCase().trim().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
    }

    function safeText(value) {
        return $('<div>').text(value || '').html();
    }

    function emptyPreview(collection) {
        $('#' + collection + '_preview').html('<div class="text-muted small empty-media-message"><i class="far fa-images mr-1"></i>No images selected.</div>');
    }

    function getJsonIds(fieldId) {
        try {
            const value = JSON.parse($('#' + fieldId).val() || '[]');
            return Array.isArray(value) ? value.map(Number).filter(Boolean) : [];
        } catch (e) {
            return [];
        }
    }

    function setJsonIds(fieldId, ids) {
        $('#' + fieldId).val(JSON.stringify(Array.from(new Set(ids.map(Number).filter(Boolean)))));
    }

    function makePreviewItem(collection, source, key, url, label) {
        const $item = $('<div>').addClass('border rounded bg-light p-1 text-center position-relative location-media-preview-item').attr({'data-source': source, 'data-key': key}).css('width', '112px');
        const $remove = $('<button>').attr({type: 'button', title: 'Remove image'}).addClass('btn btn-danger btn-sm btn-remove-location-media position-absolute').attr({'data-collection': collection, 'data-source': source, 'data-key': key}).css({right: '3px', top: '3px', padding: '1px 5px', zIndex: 2}).html('<i class="fas fa-times"></i>');
        const $img = $('<img>').attr({src: url, alt: label || 'Image'}).addClass('rounded').css({width: '100px', height: '72px', objectFit: 'cover'});
        const $label = $('<small>').addClass('text-muted d-block text-truncate mt-1').attr('title', label || '').text(label || 'Image');
        return $item.append($remove, $img, $label);
    }

    function renderPickerPreview(collection, items) {
        const $preview = $('#' + collection + '_preview').empty();

        items.forEach(function(media) {
            if (!media || !media.id || !media.url) return;
            $preview.append(makePreviewItem(collection, 'picker', media.id, media.url, media.file_name || media.name || 'Media'));
        });

        if (!$preview.children().length) emptyPreview(collection);
    }

    function renderUploadPreview(collection) {
        const input = document.getElementById(collection);
        const files = Array.from(input?.files || []);
        const $preview = $('#' + collection + '_preview').empty();

        if (!files.length) {
            emptyPreview(collection);
            return;
        }

        files.forEach(function(file, index) {
            const reader = new FileReader();
            reader.onload = function(event) { $preview.append(makePreviewItem(collection, 'upload', index, event.target.result, file.name)); };
            reader.readAsDataURL(file);
        });
    }

    function removeUploadFile(collection, index) {
        const input = document.getElementById(collection);
        if (!input || !input.files) return;

        const transfer = new DataTransfer();
        Array.from(input.files).forEach(function(file, fileIndex) {
            if (fileIndex !== index) transfer.items.add(file);
        });
        input.files = transfer.files;
        renderUploadPreview(collection);

        if (!input.files.length) $('#' + collection + '_clear').val('1');
    }

    $('#location_slug').on('input', function() {
        slugTouched = true;
    });

    $('#location_name').on('input', function() {
        if (!slugTouched) $('#location_slug').val(makeSlug($(this).val()));
    });

    $('.btn-choose-location-media').on('click', function() {
        const collection = $(this).data('collection');
        const label = $(this).data('label');
        const multiple = Number($(this).data('multiple')) === 1;
        const max = Number($(this).data('max')) || 1;

        if (typeof MediaPicker === 'undefined') {
            Swal.fire('Error', 'Media Picker is not available on this page.', 'error');
            return;
        }

        MediaPicker.open(function(selected) {
            const items = (Array.isArray(selected) ? selected : [selected]).filter(Boolean).slice(0, max);
            if (!items.length) return;

            $('#' + collection).val('');
            setJsonIds(collection + '_media_ids', items.map(function(item) { return item.id; }));
            $('#' + collection + '_remove_ids').val('[]');
            $('#' + collection + '_clear').val('0');
            renderPickerPreview(collection, items);
        }, {type: 'image', multiple: multiple, max: max, title: 'Choose ' + label});
    });

    $('.location-media-file').on('change', function() {
        const collection = $(this).attr('id');
        const files = Array.from(this.files || []);

        $('#' + collection + '_media_ids').val('');
        $('#' + collection + '_remove_ids').val('[]');
        $('#' + collection + '_clear').val(files.length ? '0' : '0');
        renderUploadPreview(collection);
    });

    $('.media-preview-grid').on('click', '.btn-remove-location-media', function() {
        const collection = $(this).data('collection');
        const source = $(this).data('source');
        const key = Number($(this).data('key'));
        const mediaId = Number($(this).data('media-id'));

        if (source === 'existing' && mediaId) {
            const removeIds = getJsonIds(collection + '_remove_ids');
            removeIds.push(mediaId);
            setJsonIds(collection + '_remove_ids', removeIds);
            $(this).closest('.location-media-preview-item').remove();
        }

        if (source === 'picker' && key) {
            const ids = getJsonIds(collection + '_media_ids').filter(function(id) { return id !== key; });
            setJsonIds(collection + '_media_ids', ids);
            $(this).closest('.location-media-preview-item').remove();
            if (!ids.length) $('#' + collection + '_clear').val('1');
        }

        if (source === 'upload' && Number.isInteger(key)) {
            removeUploadFile(collection, key);
        }

        const $preview = $('#' + collection + '_preview');
        if (!$preview.find('.location-media-preview-item').length) emptyPreview(collection);
    });

    $('.btn-clear-location-media').on('click', function() {
        const collection = $(this).data('collection');
        $('#' + collection).val('');
        $('#' + collection + '_media_ids').val('');
        $('#' + collection + '_remove_ids').val('[]');
        $('#' + collection + '_clear').val('1');
        emptyPreview(collection);
    });

    document.dispatchEvent(new CustomEvent('admin:content-updated'));
})();
</script>
