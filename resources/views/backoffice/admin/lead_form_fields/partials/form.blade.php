@php
    $isEdit = isset($leadFormField);
    $actionUrl = $isEdit
        ? route('admin.lead_form_fields.update', $leadFormField->id)
        : route('admin.lead_form_fields.store');

    $options = old('options', $isEdit ? ($leadFormField->options ?? []) : ['']);
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-7 mb-3">
            <label class="font-weight-bold">Label <span class="text-danger">*</span></label>
            <input type="text" name="label" id="booking_field_label" class="form-control"
                   value="{{ old('label', $isEdit ? $leadFormField->label : '') }}"
                   maxlength="150" required placeholder="e.g. Size of House to Paint">
            <div class="invalid-feedback error-label"></div>
        </div>

        <div class="col-md-5 mb-3">
            <label class="font-weight-bold">Field Key <span class="text-danger">*</span></label>
            <input type="text" name="field_key" id="booking_field_key" class="form-control"
                   value="{{ old('field_key', $isEdit ? $leadFormField->field_key : '') }}"
                   maxlength="100" required pattern="[a-z][a-z0-9_]*"
                   {{ $isEdit ? 'readonly' : '' }}
                   placeholder="e.g. house_size">
            <div class="invalid-feedback error-field_key"></div>
            <small class="text-muted">{{ $isEdit ? 'Stable key: cannot be changed after creation.' : 'Lowercase letters, numbers and underscores only.' }}</small>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Placeholder</label>
            <input type="text" name="placeholder" class="form-control"
                   value="{{ old('placeholder', $isEdit ? $leadFormField->placeholder : '') }}"
                   maxlength="190" placeholder="e.g. Select house size">
            <div class="invalid-feedback error-placeholder"></div>
        </div>

        <div class="col-md-12 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="font-weight-bold mb-0">Select Options <span class="text-danger">*</span></label>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddOption">
                    <i class="fas fa-plus mr-1"></i>Add Option
                </button>
            </div>

            <div id="booking-options">
                @foreach($options as $option)
                    <div class="input-group mb-2 booking-option-row">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-grip-vertical"></i></span>
                        </div>
                        <input type="text" name="options[]" class="form-control" value="{{ $option }}" maxlength="190" required placeholder="Option value">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-danger btn-remove-option"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="text-danger small error-options"></div>
            <small class="text-muted">Public booking fields are single-select controls; option order is preserved.</small>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Required <span class="text-danger">*</span></label>
            <select name="is_required" class="form-control" required>
                <option value="0" {{ ! old('is_required', $isEdit ? $leadFormField->is_required : false) ? 'selected' : '' }}>No</option>
                <option value="1" {{ old('is_required', $isEdit ? $leadFormField->is_required : false) ? 'selected' : '' }}>Yes</option>
            </select>
            <div class="invalid-feedback error-is_required"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
            <select name="is_active" class="form-control" required>
                <option value="1" {{ old('is_active', $isEdit ? $leadFormField->is_active : true) ? 'selected' : '' }}>Active</option>
                <option value="0" {{ ! old('is_active', $isEdit ? $leadFormField->is_active : true) ? 'selected' : '' }}>Inactive</option>
            </select>
            <div class="invalid-feedback error-is_active"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" min="0"
                   value="{{ old('sort_order', $isEdit ? $leadFormField->sort_order : 0) }}">
            <div class="invalid-feedback error-sort_order"></div>
        </div>
    </div>

    <div class="text-right border-top pt-3 mt-2">
        <button type="button" class="btn btn-light border font-weight-bold px-4 mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-5">
            <i class="fas fa-save mr-2"></i>{{ $isEdit ? 'Update Field' : 'Save Field' }}
        </button>
    </div>
</form>

<script>
(function () {
    const $options = $('#booking-options');

    $('#btnAddOption').on('click', function () {
        $options.append(`
            <div class="input-group mb-2 booking-option-row">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-grip-vertical"></i></span>
                </div>
                <input type="text" name="options[]" class="form-control" maxlength="190" required placeholder="Option value">
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-danger btn-remove-option"><i class="fas fa-times"></i></button>
                </div>
            </div>
        `);
    });

    $options.on('click', '.btn-remove-option', function () {
        if ($options.find('.booking-option-row').length <= 1) {
            $(this).closest('.booking-option-row').find('input').val('').focus();
            return;
        }

        $(this).closest('.booking-option-row').remove();
    });

    @if(!$isEdit)
    $('#booking_field_label').on('input', function () {
        if ($('#booking_field_key').data('manual') === true) return;

        const key = String($(this).val())
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');

        $('#booking_field_key').val(key);
    });

    $('#booking_field_key').on('input', function () {
        $(this).data('manual', String($(this).val()).trim() !== '');
    });
    @endif
})();
</script>
