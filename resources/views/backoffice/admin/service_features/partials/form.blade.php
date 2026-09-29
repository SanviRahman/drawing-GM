@php
    $isEdit = isset($serviceFeature) && $serviceFeature instanceof \App\Models\ServiceFeature;
    $actionUrl = $isEdit ? route('admin.service_features.update', $serviceFeature->id) : route('admin.service_features.store');
    $selectedService = old('service_id', $isEdit ? $serviceFeature->service_id : ($selectedServiceId ?? ''));
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST">
    @csrf

    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6 mb-2">
            <label class="font-weight-bold mb-1">Service <span class="text-danger">*</span></label>
            <select name="service_id" class="form-control form-control-sm" required>
                <option value="">-- Select Service --</option>

                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ (string) $selectedService === (string) $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback error-service_id"></div>
        </div>

        <div class="col-md-6 mb-2">
            <label class="font-weight-bold mb-1">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control form-control-sm" value="{{ old('title', $isEdit ? $serviceFeature->title : '') }}" required maxlength="190" placeholder="e.g. Free Colour Consultation">
            <div class="invalid-feedback error-title"></div>
        </div>

        <div class="col-md-12 mb-2">
            <label class="font-weight-bold mb-1">Description</label>
            <textarea name="description" id="service_feature_description" class="form-control tinymce-editor" rows="6" data-editor-height="220" placeholder="Feature description...">{{ old('description', $isEdit ? $serviceFeature->description : '') }}</textarea>
            <div class="invalid-feedback error-description"></div>
            <small class="text-muted">Rich text description.</small>
        </div>

        <div class="col-md-4 mb-2">
            <label class="font-weight-bold mb-1">Icon</label>
            <input type="text" name="icon" class="form-control form-control-sm" value="{{ old('icon', $isEdit ? $serviceFeature->icon : '') }}" maxlength="100" placeholder="check-circle">
            <div class="invalid-feedback error-icon"></div>
            <small class="text-muted">Controlled icon key only.</small>
        </div>

        <div class="col-md-4 mb-2">
            <label class="font-weight-bold mb-1">Sort Order</label>
            <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ old('sort_order', $isEdit ? $serviceFeature->sort_order : '') }}" min="0" step="1" placeholder="Auto">
            <div class="invalid-feedback error-sort_order"></div>
            <small class="text-muted">Blank = auto append.</small>
        </div>

        <div class="col-md-4 mb-2">
            <label class="font-weight-bold mb-1">Active <span class="text-danger">*</span></label>
            <select name="is_active" class="form-control form-control-sm" required>
                <option value="1" {{ old('is_active', $isEdit ? (int) $serviceFeature->is_active : 1) == 1 ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ old('is_active', $isEdit ? (int) $serviceFeature->is_active : 1) == 0 ? 'selected' : '' }}>No</option>
            </select>
            <div class="invalid-feedback error-is_active"></div>
        </div>
    </div>

    <div class="d-flex justify-content-end border-top pt-2 mt-2">
        <button type="button" class="btn btn-light btn-sm mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i>{{ $isEdit ? 'Update Feature' : 'Save Feature' }}</button>
    </div>
</form>