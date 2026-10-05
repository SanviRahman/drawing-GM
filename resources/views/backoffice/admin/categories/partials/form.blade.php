@php
    $isEdit = isset($category) && $category instanceof \App\Models\Category;
    $actionUrl = $isEdit
        ? route('admin.categories.update', $category->id)
        : route('admin.categories.store');
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-8 mb-3">
            <label class="font-weight-bold">Category Name <span class="text-danger">*</span></label>
            <input type="text"
                   name="name"
                   id="category_name"
                   class="form-control"
                   value="{{ old('name', $isEdit ? $category->name : '') }}"
                   maxlength="190"
                   required
                   placeholder="e.g. Painting Tips">
            <div class="invalid-feedback error-name"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
            <select name="is_active" class="form-control" required>
                <option value="1" {{ old('is_active', $isEdit ? (int) $category->is_active : 1) == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('is_active', $isEdit ? (int) $category->is_active : 1) == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
            <div class="invalid-feedback error-is_active"></div>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Slug</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-link"></i></span>
                </div>
                <input type="text"
                       name="slug"
                       id="category_slug"
                       class="form-control"
                       value="{{ old('slug', $isEdit ? $category->slug : '') }}"
                       maxlength="190"
                       placeholder="Leave blank on create to generate automatically">
            </div>
            <div class="invalid-feedback error-slug d-block"></div>
            <small class="text-muted">
                On create, blank slug is generated from the category name. On edit, clearing this field keeps the existing slug.
            </small>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Description</label>
            <textarea name="description"
                      id="category_description"
                      class="form-control tinymce-editor"
                      rows="8"
                      data-editor-height="300"
                      placeholder="Category description...">{{ old('description', $isEdit ? $category->description : '') }}</textarea>
            <div class="invalid-feedback error-description d-block"></div>
            <small class="text-muted">
                This implementation stores category description as <code>TEXT</code>, so the global TinyMCE editor is used.
            </small>
        </div>
    </div>

    <div class="text-right border-top pt-3 mt-2 bg-white rounded-bottom">
        <button type="button" class="btn btn-light border font-weight-bold px-4 mr-2 shadow-sm" data-dismiss="modal">
            Cancel
        </button>
        <button type="submit" class="btn btn-primary font-weight-bold px-5 shadow-sm">
            <i class="fas fa-save mr-2"></i>{{ $isEdit ? 'Update Category' : 'Save Category' }}
        </button>
    </div>
</form>
