@php
    $isEdit = isset($pricingPackage) && $pricingPackage instanceof \App\Models\PricingPackage;
    $actionUrl = $isEdit ? route('admin.pricing_packages.update', $pricingPackage->id) : route('admin.pricing_packages.store');
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6 mb-2"><label class="font-weight-bold mb-1">Service</label><select name="service_id" class="form-control form-control-sm"><option value="">Global / No Service</option>
            @foreach($services as $service)
                <option value="{{ $service->id }}" {{ (string) old('service_id', $isEdit ? $pricingPackage->service_id : '') === (string) $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
            @endforeach
        </select><div class="invalid-feedback error-service_id"></div></div>
        <div class="col-md-6 mb-2"><label class="font-weight-bold mb-1">Location</label><select name="location_id" class="form-control form-control-sm"><option value="">Global / No Location</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" {{ (string) old('location_id', $isEdit ? $pricingPackage->location_id : '') === (string) $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
            @endforeach
        </select><div class="invalid-feedback error-location_id"></div></div>
        <div class="col-md-6 mb-2"><label class="font-weight-bold mb-1">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $isEdit ? $pricingPackage->name : '') }}" required maxlength="190" placeholder="e.g. Standard Painting Package"><div class="invalid-feedback error-name"></div></div>
        <div class="col-md-6 mb-2"><label class="font-weight-bold mb-1">Subtitle</label><input type="text" name="subtitle" class="form-control form-control-sm" value="{{ old('subtitle', $isEdit ? $pricingPackage->subtitle : '') }}" maxlength="190" placeholder="Optional subtitle"><div class="invalid-feedback error-subtitle"></div></div>
        <div class="col-md-6 mb-2"><label class="font-weight-bold mb-1">Badge</label><input type="text" name="badge" class="form-control form-control-sm" value="{{ old('badge', $isEdit ? $pricingPackage->badge : '') }}" maxlength="100" placeholder="e.g. Best Value"><div class="invalid-feedback error-badge"></div></div>
        <div class="col-md-2 mb-2"><label class="font-weight-bold mb-1">Currency <span class="text-danger">*</span></label><input type="text" name="currency" class="form-control form-control-sm text-uppercase" value="{{ old('currency', $isEdit ? $pricingPackage->currency : '') }}" required minlength="3" maxlength="3" placeholder="SGD"><div class="invalid-feedback error-currency"></div></div>
        <div class="col-md-2 mb-2"><label class="font-weight-bold mb-1">Featured <span class="text-danger">*</span></label><select name="is_featured" class="form-control form-control-sm" required><option value="1" {{ old('is_featured', $isEdit ? (int) $pricingPackage->is_featured : 0) == 1 ? 'selected' : '' }}>Yes</option><option value="0" {{ old('is_featured', $isEdit ? (int) $pricingPackage->is_featured : 0) == 0 ? 'selected' : '' }}>No</option></select><div class="invalid-feedback error-is_featured"></div></div>
        <div class="col-md-2 mb-2"><label class="font-weight-bold mb-1">Active <span class="text-danger">*</span></label><select name="is_active" class="form-control form-control-sm" required><option value="1" {{ old('is_active', $isEdit ? (int) $pricingPackage->is_active : 1) == 1 ? 'selected' : '' }}>Yes</option><option value="0" {{ old('is_active', $isEdit ? (int) $pricingPackage->is_active : 1) == 0 ? 'selected' : '' }}>No</option></select><div class="invalid-feedback error-is_active"></div></div>
        <div class="col-md-3 mb-2"><label class="font-weight-bold mb-1">Sort Order</label><input type="number" name="sort_order" class="form-control form-control-sm" value="{{ old('sort_order', $isEdit ? $pricingPackage->sort_order : 0) }}" min="0" step="1"><div class="invalid-feedback error-sort_order"></div></div>
        <div class="col-md-12 mb-2"><label class="font-weight-bold mb-1">Description</label><textarea name="description" id="pricing_package_description" class="form-control tinymce-editor" rows="6" data-editor-height="220" placeholder="Package description...">{{ old('description', $isEdit ? $pricingPackage->description : '') }}</textarea><div class="invalid-feedback error-description"></div><small class="text-muted">Stored as <code>TEXT</code>, therefore TinyMCE is used.</small></div>
    </div>

    <div class="d-flex justify-content-end border-top pt-2 mt-2"><button type="button" class="btn btn-light btn-sm mr-2" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i>{{ $isEdit ? 'Update Package' : 'Save Package' }}</button></div>
</form>
