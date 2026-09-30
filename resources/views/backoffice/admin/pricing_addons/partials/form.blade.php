@php
    $isEdit = isset($pricingAddon) && $pricingAddon instanceof \App\Models\PricingAddon;
    $actionUrl = $isEdit ? route('admin.pricing_addons.update', $pricingAddon->id) : route('admin.pricing_addons.store');
    $selectedType = old('price_type', $isEdit ? $pricingAddon->price_type : 'fixed');
    $imageMedia = $isEdit ? $pricingAddon->getFirstMedia(\App\Models\PricingAddon::MEDIA_COLLECTION) : null;
    $imageUrl = $imageMedia ? $imageMedia->getUrl() : null;
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-8 mb-2"><label class="font-weight-bold mb-1">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $isEdit ? $pricingAddon->name : '') }}" maxlength="190" required placeholder="e.g. Ceiling Painting"><div class="invalid-feedback error-name"></div></div>
        <div class="col-md-4 mb-2"><label class="font-weight-bold mb-1">Price Type <span class="text-danger">*</span></label><select name="price_type" id="pricing_addon_price_type" class="form-control form-control-sm" required><option value="fixed" {{ $selectedType === 'fixed' ? 'selected' : '' }}>Fixed</option><option value="from" {{ $selectedType === 'from' ? 'selected' : '' }}>From</option><option value="range" {{ $selectedType === 'range' ? 'selected' : '' }}>Range</option><option value="call" {{ $selectedType === 'call' ? 'selected' : '' }}>Call for Price</option></select><div class="invalid-feedback error-price_type"></div></div>

        <div class="col-md-4 mb-2 pricing-addon-amount-wrap"><label class="font-weight-bold mb-1">Amount <span class="text-danger pricing-addon-amount-required">*</span></label><input type="number" name="amount" class="form-control form-control-sm" value="{{ old('amount', $isEdit ? $pricingAddon->amount : '') }}" min="0" max="9999999999.99" step="0.01" placeholder="0.00"><div class="invalid-feedback error-amount"></div></div>
        <div class="col-md-4 mb-2 pricing-addon-amount-max-wrap"><label class="font-weight-bold mb-1">Maximum Amount <span class="text-danger">*</span></label><input type="number" name="amount_max" class="form-control form-control-sm" value="{{ old('amount_max', $isEdit ? $pricingAddon->amount_max : '') }}" min="0" max="9999999999.99" step="0.01" placeholder="0.00"><div class="invalid-feedback error-amount_max"></div></div>
        <div class="col-md-4 mb-2"><label class="font-weight-bold mb-1">Unit</label><input type="text" name="unit" class="form-control form-control-sm" value="{{ old('unit', $isEdit ? $pricingAddon->unit : '') }}" maxlength="80" placeholder="room / door / sq ft / job"><div class="invalid-feedback error-unit"></div></div>

        <div class="col-md-6 mb-2"><label class="font-weight-bold mb-1">Sort Order</label><input type="number" name="sort_order" class="form-control form-control-sm" value="{{ old('sort_order', $isEdit ? $pricingAddon->sort_order : '') }}" min="0" max="4294967295" step="1" placeholder="Auto"><div class="invalid-feedback error-sort_order"></div></div>
        <div class="col-md-6 mb-2"><label class="font-weight-bold mb-1">Active <span class="text-danger">*</span></label><select name="is_active" class="form-control form-control-sm" required><option value="1" {{ old('is_active', $isEdit ? (int) $pricingAddon->is_active : 1) == 1 ? 'selected' : '' }}>Yes</option><option value="0" {{ old('is_active', $isEdit ? (int) $pricingAddon->is_active : 1) == 0 ? 'selected' : '' }}>No</option></select><div class="invalid-feedback error-is_active"></div></div>

        <div class="col-md-12 mb-3"><label class="font-weight-bold mb-1">Description</label><textarea name="description" id="pricing_addon_description" class="form-control tinymce-editor" rows="7" data-editor-height="260" placeholder="Pricing add-on description...">{{ old('description', $isEdit ? $pricingAddon->description : '') }}</textarea><div class="invalid-feedback error-description"></div><small class="text-muted">Stored as <code>TEXT</code>, therefore the global TinyMCE editor is used.</small></div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold mb-1">Add-on Image</label>
            <div class="border rounded p-3 bg-light">
                <div class="d-flex flex-wrap align-items-center mb-2" style="gap:8px;"><input type="file" name="image" id="pricing_addon_image" class="form-control-file" accept="image/jpeg,image/png,image/webp,image/gif">
                    @can('media_list')
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnChoosePricingAddonImage"><i class="fas fa-photo-video mr-1"></i>Choose from Media</button>
                    @endcan
                    <button type="button" class="btn btn-outline-danger btn-sm" id="btnRemovePricingAddonImage"><i class="fas fa-trash-alt mr-1"></i>Remove Image</button>
                </div>
                <input type="hidden" name="image_media_id" id="pricing_addon_image_media_id" value="">
                <input type="hidden" name="image_remove" id="pricing_addon_image_remove" value="0">
                <div class="text-danger small mb-2 error-image"></div>
                <div class="text-danger small mb-2 error-image_media_id"></div>
                <div id="pricing_addon_image_preview" class="d-flex align-items-center">
                    @if($imageUrl)
                        <div class="border rounded bg-white p-1 text-center pricing-addon-image-preview-item"><img src="{{ $imageUrl }}" alt="{{ $pricingAddon->name }}" class="rounded" style="width:160px;height:110px;object-fit:cover;"><small class="text-muted d-block text-truncate mt-1" style="max-width:160px;">{{ $imageMedia->file_name }}</small></div>
                    @else
                        <div class="text-muted small pricing-addon-image-empty"><i class="far fa-image mr-1"></i>No image selected.</div>
                    @endif
                </div>
                <small class="text-muted d-block mt-2">JPG, JPEG, PNG, WEBP or GIF. Maximum 10 MB.</small>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end border-top pt-2 mt-2"><button type="button" class="btn btn-light btn-sm mr-2" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i>{{ $isEdit ? 'Update Pricing Add-on' : 'Save Pricing Add-on' }}</button></div>
</form>

<script>
(function() {
    function syncPriceFields() {
        const type = $('#pricing_addon_price_type').val();
        const isCall = type === 'call';
        const isRange = type === 'range';
        $('.pricing-addon-amount-wrap').toggle(!isCall);
        $('.pricing-addon-amount-max-wrap').toggle(isRange);
        $('.pricing-addon-amount-required').toggle(!isCall);
        $('[name="amount"]').prop('required', !isCall);
        $('[name="amount_max"]').prop('required', isRange);
    }

    function renderImagePreview(url, label) {
        const $preview = $('#pricing_addon_image_preview').empty();
        if (!url) {
            $preview.html('<div class="text-muted small pricing-addon-image-empty"><i class="far fa-image mr-1"></i>No image selected.</div>');
            return;
        }
        const $box = $('<div>').addClass('border rounded bg-white p-1 text-center pricing-addon-image-preview-item');
        const $img = $('<img>').attr({src: url, alt: label || 'Pricing add-on image'}).addClass('rounded').css({width:'160px', height:'110px', objectFit:'cover'});
        const $label = $('<small>').addClass('text-muted d-block text-truncate mt-1').css('max-width', '160px').text(label || 'Image');
        $preview.append($box.append($img, $label));
    }

    $('#pricing_addon_price_type').off('change.pricingAddon').on('change.pricingAddon', syncPriceFields);
    syncPriceFields();

    $('#pricing_addon_image').off('change.pricingAddon').on('change.pricingAddon', function() {
        $('#pricing_addon_image_media_id').val('');
        $('#pricing_addon_image_remove').val('0');
        const file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) {
            renderImagePreview(null, null);
            return;
        }
        const reader = new FileReader();
        reader.onload = function(event) { renderImagePreview(event.target.result, file.name); };
        reader.readAsDataURL(file);
    });

    $('#btnChoosePricingAddonImage').off('click.pricingAddon').on('click.pricingAddon', function() {
        if (typeof MediaPicker === 'undefined') {
            Swal.fire('Error', 'Media Picker is not available on this page.', 'error');
            return;
        }
        MediaPicker.open(function(media) {
            if (!media || !media.id || !media.url) return;
            $('#pricing_addon_image').val('');
            $('#pricing_addon_image_media_id').val(media.id);
            $('#pricing_addon_image_remove').val('0');
            renderImagePreview(media.url, media.file_name || media.name || 'Media image');
        }, {type:'image', multiple:false, max:1, title:'Choose Pricing Add-on Image'});
    });

    $('#btnRemovePricingAddonImage').off('click.pricingAddon').on('click.pricingAddon', function() {
        $('#pricing_addon_image').val('');
        $('#pricing_addon_image_media_id').val('');
        $('#pricing_addon_image_remove').val('1');
        renderImagePreview(null, null);
    });
})();
</script>
