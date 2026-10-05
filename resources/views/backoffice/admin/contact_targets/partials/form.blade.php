@php
    $isEdit = isset($contactTarget);
    $actionUrl = $isEdit
        ? route('admin.contact_targets.update', $contactTarget->id)
        : route('admin.contact_targets.store');

    $selectedType = old('target_type', $isEdit ? $contactTarget->target_type_key : 'page');
    $selectedTargetId = (string) old('targetable_id', $isEdit ? $contactTarget->targetable_id : '');
    $selectedChannelId = (string) old('contact_channel_id', $isEdit ? $contactTarget->contact_channel_id : '');
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Contact Channel <span class="text-danger">*</span></label>
            <select name="contact_channel_id" id="contact_target_channel" class="form-control select2" required>
                <option value="">Select contact channel</option>
                @foreach($contactChannelOptions as $channel)
                    <option value="{{ $channel->id }}" {{ $selectedChannelId === (string) $channel->id ? 'selected' : '' }}>
                        {{ $channel->label }} — {{ $channel->type_label }}{{ $channel->region ? ' — '.$channel->region : '' }}{{ ! $channel->is_active ? ' (Inactive)' : '' }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback error-contact_channel_id"></div>
            <small class="text-muted">Inactive channels may be prepared in advance; only active channels are rendered publicly.</small>
        </div>

        <div class="col-md-5 mb-3">
            <label class="font-weight-bold">Target Type <span class="text-danger">*</span></label>
            <select name="target_type" id="contact_target_type" class="form-control" required>
                @foreach($targetTypes as $key => $meta)
                    <option value="{{ $key }}" {{ $selectedType === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback error-target_type"></div>
        </div>

        <div class="col-md-7 mb-3">
            <label class="font-weight-bold">Target <span class="text-danger">*</span></label>
            <select name="targetable_id" id="contact_target_targetable_id" class="form-control select2" required>
                <option value="">Select target</option>
            </select>
            <div class="invalid-feedback error-targetable_id"></div>
        </div>

        <div class="col-md-12">
            <div class="alert alert-light border mb-0">
                <i class="fas fa-info-circle text-info mr-1"></i>
                A mapped channel is eligible for the selected Page, Service or Location. Channels without any active target mappings remain global fallback channels.
            </div>
        </div>
    </div>

    <div class="text-right border-top pt-3 mt-3 bg-white rounded-bottom">
        <button type="button" class="btn btn-light border font-weight-bold px-4 mr-2 shadow-sm" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-5 shadow-sm">
            <i class="fas fa-save mr-2"></i>{{ $isEdit ? 'Update Contact Target' : 'Save Contact Target' }}
        </button>
    </div>
</form>

<style>
    /* Keep Select2 controls aligned with Bootstrap inputs inside the AJAX modal. */
    #ajaxModal #ajax-form .select2-container { width: 100% !important; }
    #ajaxModal #ajax-form .select2-container--default .select2-selection--single {
        height: 38px !important;
        min-height: 38px !important;
        display: flex !important;
        align-items: center !important;
    }
    #ajaxModal #ajax-form .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: normal !important;
        padding-left: 12px !important;
        padding-right: 30px !important;
    }
    #ajaxModal #ajax-form .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        top: 1px !important;
    }
</style>

<script>
(function () {
    const options = {
        page: @json($pageOptions),
        service: @json($serviceOptions),
        location: @json($locationOptions)
    };

    const selectedTargetId = @json($selectedTargetId);
    const $type = $('#contact_target_type');
    const $target = $('#contact_target_targetable_id');

    function rebuildTargetOptions(keepSelected = false) {
        const type = $type.val();
        const current = keepSelected ? String(selectedTargetId || '') : '';
        const items = options[type] || {};

        $target.empty().append(new Option('Select target', '', false, false));

        Object.entries(items).forEach(function ([id, label]) {
            const selected = current !== '' && String(id) === current;
            $target.append(new Option(label, id, selected, selected));
        });

        $target.trigger('change.select2');
    }

    if ($('.select2').length) {
        $('.select2').select2({
            width: '100%',
            dropdownParent: $('#ajaxModal')
        });
    }

    rebuildTargetOptions(true);

    $type.on('change', function () {
        rebuildTargetOptions(false);
    });
})();
</script>
