@php
    $isEdit = isset($pricingItem) && $pricingItem instanceof \App\Models\PricingItem;
    $actionUrl = $isEdit ? route('admin.pricing_items.update', $pricingItem->id) : route('admin.pricing_items.store');
    $selectedPackage = old('pricing_package_id', $isEdit ? $pricingItem->pricing_package_id : ($selectedPackageId ?? ''));
    $selectedType = old('price_type', $isEdit ? $pricingItem->price_type : 'fixed');
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST">
    @csrf

    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-7 mb-2">
            <label class="font-weight-bold mb-1">Pricing Package <span class="text-danger">*</span></label>
            <select name="pricing_package_id" class="form-control form-control-sm" required>
                <option value="">-- Select Pricing Package --</option>
                @foreach($packages as $package)
                    <option value="{{ $package->id }}" {{ (string) $selectedPackage === (string) $package->id ? 'selected' : '' }}>{{ $package->name }} ({{ $package->currency }})</option>
                @endforeach
            </select>
            <div class="invalid-feedback error-pricing_package_id"></div>
        </div>

        <div class="col-md-5 mb-2">
            <label class="font-weight-bold mb-1">Price Type <span class="text-danger">*</span></label>
            <select name="price_type" id="pricing_item_price_type" class="form-control form-control-sm" required>
                <option value="fixed" {{ $selectedType === 'fixed' ? 'selected' : '' }}>Fixed</option>
                <option value="from" {{ $selectedType === 'from' ? 'selected' : '' }}>From</option>
                <option value="range" {{ $selectedType === 'range' ? 'selected' : '' }}>Range</option>
                <option value="call" {{ $selectedType === 'call' ? 'selected' : '' }}>Call for Price</option>
            </select>
            <div class="invalid-feedback error-price_type"></div>
        </div>

        <div class="col-md-12 mb-2">
            <label class="font-weight-bold mb-1">Label <span class="text-danger">*</span></label>
            <input type="text" name="label" class="form-control form-control-sm" value="{{ old('label', $isEdit ? $pricingItem->label : '') }}" maxlength="190" required placeholder="e.g. 4-Room HDB">
            <div class="invalid-feedback error-label"></div>
        </div>

        <div class="col-md-6 mb-2 pricing-amount-wrap">
            <label class="font-weight-bold mb-1">Amount <span class="text-danger pricing-amount-required">*</span></label>
            <input type="number" name="amount" class="form-control form-control-sm" value="{{ old('amount', $isEdit ? $pricingItem->amount : '') }}" min="0" max="9999999999.99" step="0.01" placeholder="0.00">
            <div class="invalid-feedback error-amount"></div>
        </div>

        <div class="col-md-6 mb-2 pricing-amount-max-wrap">
            <label class="font-weight-bold mb-1">Maximum Amount <span class="text-danger">*</span></label>
            <input type="number" name="amount_max" class="form-control form-control-sm" value="{{ old('amount_max', $isEdit ? $pricingItem->amount_max : '') }}" min="0" max="9999999999.99" step="0.01" placeholder="0.00">
            <div class="invalid-feedback error-amount_max"></div>
        </div>

        <div class="col-md-4 mb-2">
            <label class="font-weight-bold mb-1">Unit</label>
            <input type="text" name="unit" class="form-control form-control-sm" value="{{ old('unit', $isEdit ? $pricingItem->unit : '') }}" maxlength="80" placeholder="room / property / sq ft / job">
            <div class="invalid-feedback error-unit"></div>
        </div>

        <div class="col-md-4 mb-2">
            <label class="font-weight-bold mb-1">Prefix</label>
            <input type="text" name="prefix" class="form-control form-control-sm" value="{{ old('prefix', $isEdit ? $pricingItem->prefix : '') }}" maxlength="40" placeholder="e.g. Nett">
            <div class="invalid-feedback error-prefix"></div>
        </div>

        <div class="col-md-4 mb-2">
            <label class="font-weight-bold mb-1">Suffix</label>
            <input type="text" name="suffix" class="form-control form-control-sm" value="{{ old('suffix', $isEdit ? $pricingItem->suffix : '') }}" maxlength="40" placeholder="e.g. onwards">
            <div class="invalid-feedback error-suffix"></div>
        </div>

        <div class="col-md-6 mb-2">
            <label class="font-weight-bold mb-1">Sort Order</label>
            <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ old('sort_order', $isEdit ? $pricingItem->sort_order : '') }}" min="0" step="1" placeholder="Auto">
            <div class="invalid-feedback error-sort_order"></div>
        </div>

        <div class="col-md-6 mb-2">
            <label class="font-weight-bold mb-1">Active <span class="text-danger">*</span></label>
            <select name="is_active" class="form-control form-control-sm" required>
                <option value="1" {{ old('is_active', $isEdit ? (int) $pricingItem->is_active : 1) == 1 ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ old('is_active', $isEdit ? (int) $pricingItem->is_active : 1) == 0 ? 'selected' : '' }}>No</option>
            </select>
            <div class="invalid-feedback error-is_active"></div>
        </div>
    </div>

    <div class="d-flex justify-content-end border-top pt-2 mt-2"><button type="button" class="btn btn-light btn-sm mr-2" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i>{{ $isEdit ? 'Update Pricing Item' : 'Save Pricing Item' }}</button></div>
</form>

<script>
(function() {
    function syncPricingFields() {
        const type = $('#pricing_item_price_type').val();
        const isCall = type === 'call';
        const isRange = type === 'range';

        $('.pricing-amount-wrap').toggle(!isCall);
        $('.pricing-amount-max-wrap').toggle(isRange);
        $('[name="amount"]').prop('required', !isCall);
        $('[name="amount_max"]').prop('required', isRange);

        if (isCall) {
            $('[name="amount"]').val('');
            $('[name="amount_max"]').val('');
        } else if (!isRange) {
            $('[name="amount_max"]').val('');
        }
    }

    $('#pricing_item_price_type').off('change.pricingItem').on('change.pricingItem', syncPricingFields);
    syncPricingFields();
})();
</script>
