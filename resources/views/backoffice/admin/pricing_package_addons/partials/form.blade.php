@php
    $isEdit = isset($pricingPackageAddon) && $pricingPackageAddon instanceof \App\Models\PricingPackageAddon;
    $actionUrl = $isEdit
        ? route('admin.pricing_package_addons.update', $pricingPackageAddon->id)
        : route('admin.pricing_package_addons.store');

    $currentPackageId = old(
        'pricing_package_id',
        $isEdit ? $pricingPackageAddon->pricing_package_id : ($selectedPackageId ?? '')
    );

    $currentAddonId = old(
        'pricing_addon_id',
        $isEdit ? $pricingPackageAddon->pricing_addon_id : ($selectedAddonId ?? '')
    );

    $overrideJson = old('override_data');

    if ($overrideJson === null && $isEdit && $pricingPackageAddon->override_data !== null) {
        $overrideJson = json_encode(
            $pricingPackageAddon->override_data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold mb-1">Pricing Package <span class="text-danger">*</span></label>
            <select name="pricing_package_id" id="pricing_package_id" class="form-control form-control-sm" required>
                <option value="">-- Select Pricing Package --</option>
                @foreach($packages as $package)
                    @php
                        $scope = collect([
                            $package->service?->name,
                            $package->location?->name,
                        ])->filter()->implode(' / ');
                    @endphp
                    <option value="{{ $package->id }}" {{ (string) $currentPackageId === (string) $package->id ? 'selected' : '' }}>
                        {{ $package->name }}{{ $scope ? ' — ' . $scope : '' }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback error-pricing_package_id"></div>
        </div>

        <div class="col-md-6 mb-3">
            <label class="font-weight-bold mb-1">Pricing Add-on <span class="text-danger">*</span></label>
            <select name="pricing_addon_id" id="pricing_addon_id" class="form-control form-control-sm" required>
                <option value="">-- Select Pricing Add-on --</option>
                @foreach($addons as $addon)
                    @php
                        $imageUrl = $addon->getFirstMediaUrl(\App\Models\PricingAddon::MEDIA_COLLECTION);
                    @endphp
                    <option value="{{ $addon->id }}"
                            data-image-url="{{ $imageUrl }}"
                            data-display-price="{{ $addon->display_price }}"
                            {{ (string) $currentAddonId === (string) $addon->id ? 'selected' : '' }}>
                        {{ $addon->name }} — {{ $addon->display_price }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback error-pricing_addon_id"></div>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold mb-1">Selected Add-on Preview</label>
            <div class="border rounded bg-light p-3">
                <div id="pricing_package_addon_image_preview" class="d-flex align-items-center" style="gap:12px;">
                    <div class="text-muted small"><i class="far fa-image mr-1"></i>Select an add-on to preview its image.</div>
                </div>
                <small class="text-muted d-block mt-2">
                    The image belongs to <strong>Pricing Add-ons</strong>. This mapping table does not own an image field, so upload/remove remains in the Pricing Add-ons module.
                </small>
            </div>
        </div>

        <div class="col-md-12 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="font-weight-bold mb-0">Override Data (JSON)</label>
                <button type="button" class="btn btn-outline-secondary btn-xs" id="btnFormatOverrideJson">
                    <i class="fas fa-code mr-1"></i>Format JSON
                </button>
            </div>
            <textarea name="override_data"
                      id="pricing_package_addon_override_data"
                      class="form-control text-monospace"
                      rows="10"
                      spellcheck="false"
                      placeholder='{"amount": 120.00}'>{{ $overrideJson }}</textarea>
            <div class="invalid-feedback error-override_data"></div>
            <small class="text-muted">
                This database field is <code>JSON</code>, not TEXT/LONGTEXT; therefore TinyMCE is intentionally not used. Leave blank when this package uses the add-on defaults.
            </small>
        </div>
    </div>

    <div class="d-flex justify-content-end border-top pt-2 mt-2">
        <button type="button" class="btn btn-light btn-sm mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-save mr-1"></i>{{ $isEdit ? 'Update Mapping' : 'Save Mapping' }}
        </button>
    </div>
</form>

<script>
(function() {
    function renderAddonPreview() {
        const $option = $('#pricing_addon_id option:selected');
        const url = $option.data('image-url') || '';
        const price = $option.data('display-price') || '';
        const label = $option.text().trim();
        const $preview = $('#pricing_package_addon_image_preview').empty();

        if (!$('#pricing_addon_id').val()) {
            $preview.html('<div class="text-muted small"><i class="far fa-image mr-1"></i>Select an add-on to preview its image.</div>');
            return;
        }

        const $meta = $('<div>').addClass('flex-grow-1');
        $meta.append($('<div>').addClass('font-weight-bold').text(label));
        if (price) {
            $meta.append($('<small>').addClass('text-muted d-block').text(price));
        }

        if (url) {
            const $img = $('<img>')
                .attr({src: url, alt: label || 'Pricing add-on image'})
                .addClass('rounded border bg-white')
                .css({width:'140px', height:'95px', objectFit:'cover'});
            $preview.append($img, $meta);
            return;
        }

        const $empty = $('<div>')
            .addClass('rounded border bg-white text-muted d-flex align-items-center justify-content-center')
            .css({width:'140px', height:'95px'})
            .html('<i class="far fa-image fa-2x"></i>');

        $preview.append($empty, $meta);
    }

    $('#pricing_addon_id')
        .off('change.pricingPackageAddon')
        .on('change.pricingPackageAddon', renderAddonPreview);

    $('#btnFormatOverrideJson')
        .off('click.pricingPackageAddon')
        .on('click.pricingPackageAddon', function() {
            const $textarea = $('#pricing_package_addon_override_data');
            const value = $textarea.val().trim();

            if (!value) {
                return;
            }

            try {
                const parsed = JSON.parse(value);
                if (Array.isArray(parsed) || parsed === null || typeof parsed !== 'object') {
                    Swal.fire('Invalid JSON', 'Override data must be a JSON object.', 'error');
                    return;
                }
                $textarea.val(JSON.stringify(parsed, null, 2));
            } catch (error) {
                Swal.fire('Invalid JSON', 'Please fix the JSON syntax before saving.', 'error');
            }
        });

    renderAddonPreview();
})();
</script>
