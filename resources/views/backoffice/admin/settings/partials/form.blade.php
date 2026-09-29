@php
    $isEdit = isset($setting);
    $actionUrl = $isEdit ? route('admin.settings.update', $setting->id) : route('admin.settings.store');
    $commonGroups = ['branding', 'contact', 'footer', 'consent', 'general', 'integration', 'widget', 'registration'];
    $currentType = $isEdit ? $setting->value_type : 'string';
    $isRichText = in_array($currentType, ['string', 'html'], true);
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Group Name <span class="text-danger">*</span></label>
            <input type="text" name="group_name" id="group_name" class="form-control" list="setting_groups"
                   value="{{ $isEdit ? $setting->group_name : old('group_name') }}" required
                   placeholder="e.g. branding" maxlength="80">
            <datalist id="setting_groups">
                @foreach($commonGroups as $group)
                    <option value="{{ $group }}"></option>
                @endforeach
            </datalist>
            <div class="invalid-feedback error-group_name"></div>
        </div>

        <div class="col-md-5 mb-3">
            <label class="font-weight-bold">Setting Key <span class="text-danger">*</span></label>
            <input type="text" name="setting_key" class="form-control"
                   value="{{ $isEdit ? $setting->setting_key : old('setting_key') }}" required
                   placeholder="e.g. site.name, footer.description" maxlength="150">
            <div class="invalid-feedback error-setting_key"></div>
            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Lowercase dot notation, unique key.</small>
        </div>

        <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Value Type <span class="text-danger">*</span></label>
            <select name="value_type" class="form-control" id="value_type" required>
                @foreach(\App\Models\SiteSetting::VALUE_TYPES as $type)
                    <option value="{{ $type }}" {{ $currentType === $type ? 'selected' : '' }}>
                        {{ ucfirst($type) }} {{ $type === 'html' ? '(Rich Text)' : '' }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback error-value_type"></div>
        </div>

        <div class="col-md-12 mb-3" id="wrapper-setting_value">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="font-weight-bold mb-0">Setting Value (Longtext)</label>
                <span id="editor-indicator" class="badge badge-info {{ $isRichText ? '' : 'd-none' }}">
                    <i class="fas fa-magic mr-1"></i>TinyMCE Active
                </span>
            </div>
            <textarea name="setting_value" id="setting_value"
                      class="form-control {{ $isRichText ? 'tinymce-editor' : '' }}"
                      rows="6"
                      data-editor-height="280"
                      placeholder="{{ ($isEdit && $setting->value_type === 'encrypted') ? '•••••••• — leave blank to keep current value' : 'Enter setting value...' }}">{{ ($isEdit && $setting->value_type === 'encrypted') ? '' : old('setting_value', $isEdit ? $setting->setting_value : '') }}</textarea>
            <div class="invalid-feedback error-setting_value"></div>
            <small class="text-muted" id="setting_value_hint">
                <i class="fas fa-info-circle mr-1"></i>Supports HTML & Rich Text formatting via TinyMCE editor.
            </small>
        </div>

        <div class="col-md-12">
            <hr>
            <h6 class="font-weight-bold text-primary mb-3">
                <i class="fas fa-images mr-1"></i>Branding Media
            </h6>
        </div>

        @php
            $mediaFields = [
                ['field' => 'site_logo', 'label' => 'Site Logo', 'accept' => 'image/jpeg,image/png,image/jpg,image/webp,image/svg+xml'],
                ['field' => 'site_favicon', 'label' => 'Site Favicon', 'accept' => 'image/ico,image/x-icon,image/png,image/jpeg,image/webp,image/svg+xml'],
                ['field' => 'default_hero', 'label' => 'Default Hero', 'accept' => 'image/jpeg,image/png,image/jpg,image/webp'],
            ];
        @endphp

        @foreach($mediaFields as $mf)
            <div class="col-md-4 mb-3">
                <label class="font-weight-bold">{{ $mf['label'] }}</label>
                <div class="d-flex align-items-center mb-1" style="gap: 10px;">
                    <img id="{{ $mf['field'] }}-preview"
                         src="{{ $isEdit ? $setting->collectionUrl($mf['field']) : asset('images/no-image.png') }}"
                         class="rounded border bg-light"
                         style="width: 44px; height: 44px; object-fit: contain;" alt="{{ $mf['label'] }}">
                    <div class="flex-grow-1">
                        <input type="file" name="{{ $mf['field'] }}" id="{{ $mf['field'] }}" class="form-control-file" accept="{{ $mf['accept'] }}">
                        @can('media_list')
                            <button type="button" class="btn btn-outline-primary btn-sm mt-1 btn-choose-media" data-target="{{ $mf['field'] }}" data-label="{{ $mf['label'] }}">
                                <i class="fas fa-photo-video mr-1"></i> Media Picker
                            </button>
                        @endcan
                    </div>
                </div>
                <input type="hidden" name="{{ $mf['field'] }}_media_id" id="{{ $mf['field'] }}_media_id" value="">
                <div class="invalid-feedback error-{{ $mf['field'] }}"></div>
                <div class="text-danger small mt-1 error-{{ $mf['field'] }}_media_id"></div>
            </div>
        @endforeach

        <div class="col-md-12 mt-2">
            <hr>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Public Visibility <span class="text-danger">*</span></label>
            <select name="is_public" class="form-control" required>
                <option value="1" {{ (!$isEdit || $setting->is_public == 1) ? 'selected' : '' }}>Public — Safe for frontend/API payloads</option>
                <option value="0" {{ ($isEdit && $setting->is_public == 0) ? 'selected' : '' }}>Private — Backoffice access only</option>
            </select>
            <div class="invalid-feedback error-is_public"></div>
        </div>
    </div>

    <div class="text-right border-top pt-3 mt-2 bg-white rounded-bottom">
        <button type="button" class="btn btn-light border font-weight-bold px-4 mr-2 shadow-sm" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-5 shadow-sm">
            <i class="fas fa-save mr-2"></i> {{ $isEdit ? 'Update Setting' : 'Save Setting' }}
        </button>
    </div>
</form>

<script>
    (() => {
        // Value Type পরিবর্তন হলে TinyMCE স্বয়ংক্রিয়ভাবে নিয়ন্ত্রণ করা
        $('#value_type').on('change', function () {
            const selectedType = $(this).val();
            const textarea = document.getElementById('setting_value');
            const indicator = document.getElementById('editor-indicator');
            const hint = document.getElementById('setting_value_hint');

            if (['boolean', 'integer', 'json', 'encrypted'].includes(selectedType)) {
                indicator.classList.add('d-none');
                if (window.tinymce && window.tinymce.get('setting_value')) {
                    window.tinymce.triggerSave();
                    window.tinymce.get('setting_value').destroy();
                }
                textarea.classList.remove('tinymce-editor');
                delete textarea.dataset.tinymceInitialized;

                if (selectedType === 'json') {
                    hint.innerHTML = '<i class="fas fa-code mr-1"></i>Enter raw valid JSON format string.';
                } else if (selectedType === 'encrypted') {
                    hint.innerHTML = '<i class="fas fa-shield-alt mr-1"></i>Encrypted text is stored securely.';
                } else {
                    hint.innerHTML = '<i class="fas fa-info-circle mr-1"></i>Plain scalar value input.';
                }
            } else {
                indicator.classList.remove('d-none');
                hint.innerHTML = '<i class="fas fa-info-circle mr-1"></i>Supports HTML & Rich Text formatting via TinyMCE editor.';
                if (!textarea.classList.contains('tinymce-editor')) {
                    textarea.classList.add('tinymce-editor');
                    if (window.initializeTinyMce) {
                        window.initializeTinyMce(document.getElementById('ajaxModal'));
                    }
                }
            }
        });

        @foreach($mediaFields as $mf)
            $('#{{ $mf['field'] }}').on('change', function (e) {
                $('#{{ $mf['field'] }}_media_id').val('');
                if (e.target.files && e.target.files[0]) {
                    let reader = new FileReader();
                    reader.onload = function (ev) { $('#{{ $mf['field'] }}-preview').attr('src', ev.target.result); };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });

            $('.btn-choose-media[data-target="{{ $mf['field'] }}"]').on('click', function () {
                if (typeof MediaPicker === 'undefined') {
                    Swal.fire('Error', 'Media Picker is not available on this page.', 'error');
                    return;
                }
                MediaPicker.open(function (media) {
                    $('#{{ $mf['field'] }}').val('');
                    $('#{{ $mf['field'] }}_media_id').val(media.id);
                    $('#{{ $mf['field'] }}-preview').attr('src', media.url);
                }, { type: 'image', multiple: false, title: 'Choose {{ $mf['label'] }}' });
            });
        @endforeach
    })();
</script>