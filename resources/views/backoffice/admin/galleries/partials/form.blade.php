@php
$isEdit = isset($gallery);
$actionUrl = $isEdit ? route('admin.galleries.update',$gallery->id) : route('admin.galleries.store');
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST">
    @csrf

    @if($isEdit)
    @method('PUT')
    @endif
    <div class="row">
        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Gallery Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ $isEdit ? $gallery->name : old('name') }}" required placeholder="Enter gallery name">
            <div class="invalid-feedback error-name"></div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Layout</label>
            <select name="layout" class="form-control">
                <option value="grid" {{ ($isEdit && $gallery->layout=='grid') ? 'selected' : '' }}>Grid</option>
                <option value="slider" {{ ($isEdit && $gallery->layout=='slider') ? 'selected' : '' }}>Slider</option>
                <option value="masonry" {{ ($isEdit && $gallery->layout=='masonry') ? 'selected' : '' }}>Masonry</option>
            </select>
            <div class="invalid-feedback error-layout"></div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
            <select name="is_active" class="form-control" required>
                <option value="1" {{ (!$isEdit || $gallery->is_active) ? 'selected' : '' }}>Active</option>
                <option value="0" {{ ($isEdit && !$gallery->is_active) ? 'selected' : '' }}>Inactive</option>
            </select>
            <div class="invalid-feedback error-is_active"></div>
        </div>
        <div class="col-md-12 mb-3">
            <div class="card bg-light border-0 shadow-sm">
                <div class="card-body py-3">
                    <h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-info-circle mr-1"></i>Gallery Information</h6>
                    <p class="text-muted small mb-0">After creating gallery, you can add gallery items from Gallery Items module.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="text-right border-top pt-3 mt-2 bg-white rounded-bottom">
        <button type="button" class="btn btn-light border font-weight-bold px-4 mr-2 shadow-sm" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-5 shadow-sm"><i class="fas fa-save mr-2"></i>{{ $isEdit ? 'Update Gallery' : 'Save Gallery' }}</button>
    </div>
</form>