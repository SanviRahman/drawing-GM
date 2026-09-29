@php
    $isEdit = isset($pageSection);
    $model = $pageSection ?? null;
    $payloadValue = $isEdit ? json_encode($model->payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : "{}";
    $currentPageId = old('page_id', $isEdit ? $model->page_id : $selectedPageId);
    $currentDefinitionId = old('section_definition_id', $isEdit ? $model->section_definition_id : null);
    $startsAt = old('starts_at', $isEdit && $model->starts_at ? $model->starts_at->format('Y-m-d\TH:i') : '');
    $endsAt = old('ends_at', $isEdit && $model->ends_at ? $model->ends_at->format('Y-m-d\TH:i') : '');
@endphp

<form id="ajax-form" action="{{ $isEdit ? route('admin.page_sections.update', $model->id) : route('admin.page_sections.store') }}" method="POST">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <div class="col-md-5">
            <div class="card border-0 bg-light h-100"><div class="card-body">
                <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-link text-primary mr-1"></i>Section Placement</h6>
                <div class="form-group"><label for="page_id">Page <span class="text-danger">*</span></label><select name="page_id" id="page_id" class="form-control" required><option value="">Select page</option>@foreach($pages as $page)<option value="{{ $page->id }}" @selected((string) $currentPageId === (string) $page->id)>{{ $page->title }} (/{{ $page->slug }})</option>@endforeach</select><span class="invalid-feedback error-page_id d-block"></span></div>
                <div class="form-group"><label for="section_definition_id">Section Definition <span class="text-danger">*</span></label><select name="section_definition_id" id="section_definition_id" class="form-control" required><option value="">Select section type</option>@foreach($sectionDefinitions as $definition)<option value="{{ $definition->id }}" @selected((string) $currentDefinitionId === (string) $definition->id)>{{ $definition->name }} ({{ $definition->key }})@if(isset($definition->is_active) && !$definition->is_active) - Inactive @endif</option>@endforeach</select><span class="invalid-feedback error-section_definition_id d-block"></span></div>
                <div class="form-group"><label for="theme">Theme <span class="text-danger">*</span></label><input type="text" name="theme" id="theme" class="form-control" maxlength="80" value="{{ old('theme', $isEdit ? $model->theme : 'default') }}" placeholder="default" required><span class="invalid-feedback error-theme d-block"></span><small class="form-text text-muted">Use a controlled token such as <code>default</code>, <code>light</code> or your project-specific theme key.</small></div>
                <div class="form-group"><label for="sort_order">Sort Order</label><input type="number" name="sort_order" id="sort_order" class="form-control" min="0" step="1" value="{{ old('sort_order', $isEdit ? $model->sort_order : '') }}" placeholder="Auto"><span class="invalid-feedback error-sort_order d-block"></span><small class="form-text text-muted">Leave blank on create to append after existing sections on the selected page.</small></div>
                <div class="form-group mb-0"><label for="is_active">Status <span class="text-danger">*</span></label><select name="is_active" id="is_active" class="form-control" required><option value="1" @selected((string) old('is_active', $isEdit ? (int) $model->is_active : 1) === '1')>Active</option><option value="0" @selected((string) old('is_active', $isEdit ? (int) $model->is_active : 1) === '0')>Inactive</option></select><span class="invalid-feedback error-is_active d-block"></span></div>
            </div></div>
        </div>

        <div class="col-md-7 mt-3 mt-md-0">
            <div class="form-group"><label for="heading">Heading</label><input type="text" name="heading" id="heading" class="form-control" maxlength="190" value="{{ old('heading', $model?->heading) }}"><span class="invalid-feedback error-heading d-block"></span></div>
            <div class="form-group"><label for="subheading">Subheading</label><input type="text" name="subheading" id="subheading" class="form-control" maxlength="255" value="{{ old('subheading', $model?->subheading) }}"><span class="invalid-feedback error-subheading d-block"></span></div>

            <div class="row">
                <div class="col-md-6"><div class="form-group"><label for="starts_at">Starts At</label><input type="datetime-local" name="starts_at" id="starts_at" class="form-control" value="{{ $startsAt }}"><span class="invalid-feedback error-starts_at d-block"></span></div></div>
                <div class="col-md-6"><div class="form-group"><label for="ends_at">Ends At</label><input type="datetime-local" name="ends_at" id="ends_at" class="form-control" value="{{ $endsAt }}"><span class="invalid-feedback error-ends_at d-block"></span></div></div>
            </div>

            <div class="form-group mb-0">
                <div class="d-flex justify-content-between align-items-center mb-1"><label for="payload" class="mb-0">Payload JSON <span class="text-danger">*</span></label><button type="button" class="btn btn-outline-secondary btn-xs" id="btnFormatPayload"><i class="fas fa-code mr-1"></i>Format JSON</button></div>
                <textarea name="payload" id="payload" class="form-control text-monospace" rows="15" spellcheck="false" required>{{ old('payload', $payloadValue) }}</textarea><span class="invalid-feedback error-payload d-block"></span><small class="form-text text-muted">This is a JSON column, so TinyMCE is not used here. This table has no TEXT/LONGTEXT columns.</small>
            </div>
        </div>
    </div>

    <div class="alert alert-info small mt-3 mb-0"><i class="fas fa-info-circle mr-1"></i>Payload is validated as JSON in this module. Field-level payload rules should continue to come from the selected Section Definition registry schema when section-specific editors/renderers are implemented.</div>
    <div class="d-flex justify-content-end border-top pt-3 mt-4"><button type="button" class="btn btn-light mr-2" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>{{ $isEdit ? 'Update Page Section' : 'Create Page Section' }}</button></div>
</form>

<script>
(function() {
    const button = document.getElementById('btnFormatPayload');
    const textarea = document.getElementById('payload');
    if (button && textarea) button.addEventListener('click', function() { try { textarea.value = JSON.stringify(JSON.parse(textarea.value), null, 4); textarea.classList.remove('is-invalid'); } catch (error) { textarea.classList.add('is-invalid'); if (window.Swal) Swal.fire('Invalid JSON', 'Fix the payload JSON syntax before formatting.', 'error'); } });
})();
</script>
