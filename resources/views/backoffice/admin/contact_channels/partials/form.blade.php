@php
    $isEdit = isset($contactChannel) && $contactChannel instanceof \App\Models\ContactChannel;
    $actionUrl = $isEdit
        ? route('admin.contact_channels.update', $contactChannel->id)
        : route('admin.contact_channels.store');

    $selectedType = old('type', $isEdit ? $contactChannel->type : 'whatsapp');
    $selectedIcon = old('icon', $isEdit ? $contactChannel->icon : 'whatsapp');
    $selectedColour = old('colour', $isEdit ? $contactChannel->colour : 'whatsapp');
    $iconClass = $iconOptions[$selectedIcon]['class'] ?? 'fas fa-link';
    $previewColour = $colorTokens[$selectedColour] ?? $selectedColour;
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Channel Type <span class="text-danger">*</span></label>
            <select name="type" id="contact_channel_type" class="form-control" required>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" {{ $selectedType === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback error-type"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Display Label <span class="text-danger">*</span></label>
            <input type="text"
                   name="label"
                   class="form-control"
                   maxlength="120"
                   required
                   value="{{ old('label', $isEdit ? $contactChannel->label : '') }}"
                   placeholder="e.g. WhatsApp Sales 1">
            <div class="invalid-feedback error-label"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Region / Team</label>
            <input type="text"
                   name="region"
                   class="form-control"
                   maxlength="120"
                   value="{{ old('region', $isEdit ? $contactChannel->region : '') }}"
                   placeholder="e.g. East / Sales Team">
            <div class="invalid-feedback error-region"></div>
        </div>

        <div class="col-md-8 mb-3">
            <label class="font-weight-bold">Value <span class="text-danger">*</span></label>
            <input type="text"
                   name="value"
                   id="contact_channel_value"
                   class="form-control"
                   maxlength="255"
                   required
                   value="{{ old('value', $isEdit ? $contactChannel->value : '') }}"
                   placeholder="+6591234567">
            <div class="invalid-feedback error-value"></div>
            <small class="text-muted" id="contact-value-help">
                Phone/WhatsApp values must use E.164 format, e.g. <code>+6591234567</code>.
            </small>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Display Value</label>
            <input type="text"
                   name="display_value"
                   class="form-control"
                   maxlength="120"
                   value="{{ old('display_value', $isEdit ? $contactChannel->display_value : '') }}"
                   placeholder="e.g. +65 9123 4567">
            <div class="invalid-feedback error-display_value"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Availability</label>
            <input type="text"
                   name="availability_text"
                   class="form-control"
                   maxlength="120"
                   value="{{ old('availability_text', $isEdit ? $contactChannel->availability_text : '') }}"
                   placeholder="e.g. Mon-Sun 8am-10pm">
            <div class="invalid-feedback error-availability_text"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Icon <span class="text-danger">*</span></label>
            <select name="icon" id="contact_channel_icon" class="form-control" required>
                @foreach($iconOptions as $key => $option)
                    <option value="{{ $key }}"
                            data-icon-class="{{ $option['class'] }}"
                            {{ $selectedIcon === $key ? 'selected' : '' }}>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback error-icon"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Colour <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="text"
                       name="colour"
                       id="contact_channel_colour"
                       class="form-control"
                       maxlength="20"
                       required
                       list="contact-colour-tokens"
                       value="{{ $selectedColour }}"
                       placeholder="#25D366 or whatsapp">
                <div class="input-group-append">
                    <span class="input-group-text p-1">
                        <span id="contact-colour-preview"
                              style="width:24px;height:24px;border-radius:4px;border:1px solid #ced4da;background:{{ $previewColour }};"></span>
                    </span>
                </div>
            </div>
            <datalist id="contact-colour-tokens">
                @foreach($colorTokens as $token => $hex)
                    <option value="{{ $token }}">{{ $hex }}</option>
                @endforeach
            </datalist>
            <div class="invalid-feedback error-colour d-block"></div>
            <small class="text-muted">Use <code>#RRGGBB</code> or an approved token such as <code>whatsapp</code>.</small>
        </div>

        <div class="col-md-12 mb-3">
            <div class="border rounded bg-light p-3 d-flex align-items-center">
                <span id="contact-icon-preview"
                      class="contact-channel-icon mr-3"
                      style="background: {{ $previewColour }};">
                    <i class="{{ $iconClass }}"></i>
                </span>
                <div>
                    <strong class="d-block">Icon / Colour Preview</strong>
                    <small class="text-muted">This is an icon preview, not an uploaded image.</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
            <select name="is_active" class="form-control" required>
                <option value="1" {{ old('is_active', $isEdit ? (int) $contactChannel->is_active : 1) == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('is_active', $isEdit ? (int) $contactChannel->is_active : 1) == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
            <div class="invalid-feedback error-is_active"></div>
        </div>

        <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Default <span class="text-danger">*</span></label>
            <select name="is_default" class="form-control" required>
                <option value="0" {{ old('is_default', $isEdit ? (int) $contactChannel->is_default : 0) == 0 ? 'selected' : '' }}>No</option>
                <option value="1" {{ old('is_default', $isEdit ? (int) $contactChannel->is_default : 0) == 1 ? 'selected' : '' }}>Yes</option>
            </select>
            <div class="invalid-feedback error-is_default"></div>
            <small class="text-muted">Default channels must be active.</small>
        </div>

        <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Track Clicks <span class="text-danger">*</span></label>
            <select name="track_clicks" class="form-control" required>
                <option value="1" {{ old('track_clicks', $isEdit ? (int) $contactChannel->track_clicks : 1) == 1 ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ old('track_clicks', $isEdit ? (int) $contactChannel->track_clicks : 1) == 0 ? 'selected' : '' }}>No</option>
            </select>
            <div class="invalid-feedback error-track_clicks"></div>
        </div>

        <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Sort Order</label>
            <input type="number"
                   name="sort_order"
                   class="form-control"
                   min="0"
                   max="2147483647"
                   step="1"
                   value="{{ old('sort_order', $isEdit ? $contactChannel->sort_order : '') }}"
                   placeholder="Auto">
            <div class="invalid-feedback error-sort_order"></div>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Message Template</label>
            <textarea name="message_template"
                      id="contact_channel_message_template"
                      class="form-control tinymce-editor"
                      rows="8"
                      data-editor-height="300"
                      placeholder="e.g. Hello, I would like a quotation for {service}...">{{ old('message_template', $isEdit ? $contactChannel->message_template : '') }}</textarea>
            <div class="invalid-feedback error-message_template d-block"></div>
            <small class="text-muted">
                Stored as <code>TEXT</code>, so TinyMCE is used by project rule. Safe placeholders such as <code>{page_title}</code>, <code>{service}</code> and <code>{location}</code> can be preserved for the future resolver.
            </small>
        </div>
    </div>

    <div class="text-right border-top pt-3 mt-2 bg-white rounded-bottom">
        <button type="button" class="btn btn-light border font-weight-bold px-4 mr-2 shadow-sm" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-5 shadow-sm">
            <i class="fas fa-save mr-2"></i>{{ $isEdit ? 'Update Contact Channel' : 'Save Contact Channel' }}
        </button>
    </div>
</form>

<script>
(function () {
    const colorTokens = @json($colorTokens);
    const $type = $('#contact_channel_type');
    const $value = $('#contact_channel_value');
    const $help = $('#contact-value-help');
    const $icon = $('#contact_channel_icon');
    const $colour = $('#contact_channel_colour');
    const $preview = $('#contact-icon-preview');
    const $swatch = $('#contact-colour-preview');

    function updateValueHelp() {
        const type = $type.val();

        if (type === 'whatsapp' || type === 'phone') {
            $value.attr('placeholder', '+6591234567');
            $help.html('Phone/WhatsApp values must use E.164 format, e.g. <code>+6591234567</code>.');
        } else if (type === 'email') {
            $value.attr('placeholder', 'sales@example.com');
            $help.text('Enter a valid email address.');
        } else {
            $value.attr('placeholder', 'https://example.com/contact');
            $help.text('Custom values must be full http:// or https:// URLs.');
        }
    }

    function updatePreview() {
        const iconClass = $icon.find(':selected').data('icon-class') || 'fas fa-link';
        const rawColour = String($colour.val() || '').trim();
        const cssColour = colorTokens[rawColour] || (/^#[0-9A-Fa-f]{6}$/.test(rawColour) ? rawColour : '#6c757d');

        $preview.css('background', cssColour).find('i').attr('class', iconClass);
        $swatch.css('background', cssColour);
    }

    $type.on('change', updateValueHelp);
    $icon.on('change', updatePreview);
    $colour.on('input change', updatePreview);

    updateValueHelp();
    updatePreview();
})();
</script>
