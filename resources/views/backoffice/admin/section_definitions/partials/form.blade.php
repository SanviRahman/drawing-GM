@php
    $isEdit = isset($sectionDefinition);
    $model = $sectionDefinition ?? null;
    $schemaValue = $isEdit ? json_encode($model->schema_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : "{\n    \"fields\": []\n}";
@endphp

<form id="ajax-form" action="{{ $isEdit ? route('admin.section_definitions.update', $model->id) : route('admin.section_definitions.store') }}" method="POST">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <div class="col-md-5">
            <div class="card border-0 bg-light h-100"><div class="card-body">
                <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-cog text-primary mr-1"></i>Registry Settings</h6>

                <div class="form-group">
                    <label for="section_key">Section Key <span class="text-danger">*</span></label>
                    @if($isEdit)
                        <input type="text" class="form-control" value="{{ $model->key }}" readonly>
                        <input type="hidden" name="key" value="{{ $model->key }}">
                        <small class="form-text text-muted">The registry key is immutable after creation.</small>
                    @else
                        <select name="key" id="section_key" class="form-control" required><option value="">Select section type</option>@foreach(\App\Models\SectionDefinition::ALLOWED_KEYS as $key => $label)<option value="{{ $key }}" data-name="{{ $label }}" @selected(old('key') === $key)>{{ $label }} ({{ $key }})</option>@endforeach</select>
                    @endif
                    <span class="invalid-feedback error-key d-block"></span>
                </div>

                <div class="form-group"><label for="section_name">Name <span class="text-danger">*</span></label><input type="text" name="name" id="section_name" class="form-control" maxlength="190" value="{{ old('name', $model?->name) }}" required><span class="invalid-feedback error-name d-block"></span></div>
                <div class="form-group"><label for="is_active">Status <span class="text-danger">*</span></label><select name="is_active" id="is_active" class="form-control" required><option value="1" @selected((string) old('is_active', $isEdit ? (int) $model->is_active : 1) === '1')>Active</option><option value="0" @selected((string) old('is_active', $isEdit ? (int) $model->is_active : 1) === '0')>Inactive</option></select><span class="invalid-feedback error-is_active d-block"></span></div>

                <div class="alert alert-info small mb-0"><i class="fas fa-shield-alt mr-1"></i>Only allowlisted keys from the database specification can be saved. Unknown dynamic component names are rejected.</div>
            </div></div>
        </div>

        <div class="col-md-7 mt-3 mt-md-0">
            <div class="form-group"><label for="section_description">Description</label><textarea name="description" id="section_description" class="form-control tinymce-editor" rows="8" data-editor-height="300">{{ old('description', $model?->description) }}</textarea><span class="invalid-feedback error-description d-block"></span><small class="form-text text-muted">This column is TEXT, therefore it uses the global TinyMCE editor.</small></div>

            <div class="form-group mb-0">
                <div class="d-flex justify-content-between align-items-center mb-1"><label for="schema_json" class="mb-0">Validation Schema JSON <span class="text-danger">*</span></label><button type="button" class="btn btn-outline-secondary btn-xs" id="btnFormatSchema"><i class="fas fa-code mr-1"></i>Format JSON</button></div>
                <textarea name="schema_json" id="schema_json" class="form-control text-monospace" rows="15" spellcheck="false" required>{{ old('schema_json', $schemaValue) }}</textarea><span class="invalid-feedback error-schema_json d-block"></span><small class="form-text text-muted">JSON stays a code textarea; TinyMCE is only for TEXT/LONGTEXT rich-content columns.</small>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end border-top pt-3 mt-4"><button type="button" class="btn btn-light mr-2" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>{{ $isEdit ? 'Update Section' : 'Create Section' }}</button></div>
</form>

<script>
(function() {
    const keySelect = document.getElementById('section_key');
    const nameInput = document.getElementById('section_name');
    if (keySelect && nameInput) keySelect.addEventListener('change', function() { const option = this.options[this.selectedIndex]; if (option && option.dataset.name && nameInput.value.trim() === '') nameInput.value = option.dataset.name; });
    const formatButton = document.getElementById('btnFormatSchema');
    const schemaTextarea = document.getElementById('schema_json');
    if (formatButton && schemaTextarea) formatButton.addEventListener('click', function() { try { schemaTextarea.value = JSON.stringify(JSON.parse(schemaTextarea.value), null, 4); schemaTextarea.classList.remove('is-invalid'); } catch (error) { schemaTextarea.classList.add('is-invalid'); if (window.Swal) Swal.fire('Invalid JSON', 'Fix the JSON syntax before formatting.', 'error'); } });
})();
</script>
