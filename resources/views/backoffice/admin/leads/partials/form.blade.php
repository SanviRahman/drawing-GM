@php
    $isEdit = isset($lead);
    $actionUrl = $isEdit ? route('admin.leads.update', $lead->id) : route('admin.leads.store');

    $selectedServices = old(
        'service_ids',
        $isEdit ? $lead->serviceLinks->whereNull('deleted_at')->pluck('service_id')->map(fn($id) => (string) $id)->all() : []
    );

    $serviceLinks = $isEdit ? $lead->serviceLinks->keyBy('service_id') : collect();

    $json = fn($value) => $value ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        @if($isEdit)
            <div class="col-md-12 mb-3">
                <div class="alert alert-light border mb-0">
                    <strong>Reference:</strong> {{ $lead->reference }}
                    <span class="mx-2">•</span>
                    Dynamic booking answers are historical snapshots and are not rewritten from the edit form.
                </div>
            </div>
        @endif

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Client Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" maxlength="150" required
                   value="{{ old('name', $isEdit ? $lead->name : '') }}">
            <div class="invalid-feedback error-name"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Phone / WhatsApp <span class="text-danger">*</span></label>
            <input type="text" name="phone" class="form-control" maxlength="32" required
                   value="{{ old('phone', $isEdit ? $lead->phone : '') }}"
                   placeholder="+6591234567">
            <div class="invalid-feedback error-phone"></div>
            <small class="text-muted">International E.164 format.</small>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Email</label>
            <input type="email" name="email" class="form-control" maxlength="190"
                   value="{{ old('email', $isEdit ? $lead->email : '') }}">
            <div class="invalid-feedback error-email"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Customer Account</label>
            <select name="user_id" class="form-control select2">
                <option value="">Guest / No linked account</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ (string) old('user_id', $isEdit ? $lead->user_id : '') === (string) $user->id ? 'selected' : '' }}>
                        {{ $user->name }} — {{ $user->email }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback error-user_id"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Location</label>
            <select name="location_id" class="form-control select2">
                <option value="">No location</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}" {{ (string) old('location_id', $isEdit ? $lead->location_id : '') === (string) $location->id ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback error-location_id"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Assigned To</label>
            <select name="assigned_to" class="form-control select2">
                <option value="">Unassigned</option>
                @foreach($assignees as $admin)
                    <option value="{{ $admin->id }}" {{ (string) old('assigned_to', $isEdit ? $lead->assigned_to : '') === (string) $admin->id ? 'selected' : '' }}>
                        {{ $admin->name }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback error-assigned_to"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-control" required>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" {{ old('status', $isEdit ? $lead->status : 'new') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback error-status"></div>
        </div>

        <div class="col-md-8 mb-3">
            <label class="font-weight-bold">Status Change Reason</label>
            <input type="text" name="status_reason" class="form-control" maxlength="1000" placeholder="Optional audit reason for a status change">
            <div class="invalid-feedback error-status_reason"></div>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Message</label>
            <textarea name="message" id="lead_message" class="form-control tinymce-editor" rows="8" data-editor-height="280"
                      placeholder="Customer message or enquiry details...">{{ old('message', $isEdit ? $lead->message : '') }}</textarea>
            <div class="invalid-feedback error-message"></div>
            <small class="text-muted">Stored as <code>TEXT</code>, so TinyMCE is used.</small>
        </div>

        @if(!$isEdit && $bookingFields->isNotEmpty())
            <div class="col-md-12">
                <hr>
                <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-list-alt mr-1"></i>Booking Answers</h6>
                <div class="row">
                    @foreach($bookingFields as $field)
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">
                                {{ $field->label }}
                                @if($field->is_required)<span class="text-danger">*</span>@endif
                            </label>
                            <select name="dynamic_answers[{{ $field->field_key }}]" class="form-control" {{ $field->is_required ? 'required' : '' }}>
                                <option value="">{{ $field->placeholder ?: 'Select an option' }}</option>
                                @foreach($field->options ?? [] as $option)
                                    <option value="{{ $option }}" {{ old('dynamic_answers.'.$field->field_key) === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback error-dynamic_answers-{{ $field->field_key }}"></div>
                        </div>
                    @endforeach
                </div>
                <small class="text-muted d-block mb-3">
                    These values are stored as immutable LeadFormAnswer snapshots. Although <code>answer</code> is a TEXT column, the source specification requires single-select input, so TinyMCE is intentionally not used here.
                </small>
            </div>
        @endif

        <div class="col-md-12">
            <hr>
            <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-tools mr-1"></i>Requested Services</h6>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Services</label>
            <select name="service_ids[]" id="lead_service_ids" class="form-control select2" multiple data-placeholder="Select requested services">
                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ in_array((string) $service->id, array_map('strval', $selectedServices), true) ? 'selected' : '' }}>
                        {{ $service->name }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback error-service_ids"></div>
        </div>

        <div class="col-md-12" id="service-notes-container">
            @foreach($services as $service)
                @php
                    $existingNote = old('service_notes.'.$service->id, $serviceLinks->get($service->id)?->notes);
                @endphp
                <div class="service-note-panel mb-3" data-service-id="{{ $service->id }}" style="display:{{ in_array((string) $service->id, array_map('strval', $selectedServices), true) ? 'block' : 'none' }};">
                    <label class="font-weight-bold">Notes — {{ $service->name }}</label>
                    <textarea name="service_notes[{{ $service->id }}]" id="lead_service_note_{{ $service->id }}"
                              class="form-control tinymce-editor" rows="5" data-editor-height="200"
                              placeholder="Optional notes for this requested service...">{{ $existingNote }}</textarea>
                    <div class="invalid-feedback error-service_notes-{{ $service->id }}"></div>
                    <small class="text-muted">Stored as <code>TEXT</code>, so TinyMCE is used.</small>
                </div>
            @endforeach
        </div>

        <div class="col-md-12">
            <hr>
            <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-paperclip mr-1"></i>Private Attachments</h6>
        </div>

        @if($isEdit)
            <div class="col-md-12 mb-3">
                <div class="row">
                    @forelse($lead->getMedia(\App\Models\Lead::ATTACHMENTS_COLLECTION) as $media)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <div class="border rounded p-2 h-100">
                                @if($media->isImage())
                                    @can('lead_attachment_download')
                                        <img src="{{ route('admin.leads.attachments.preview', [$lead->id, $media->id]) }}"
                                             class="img-fluid border rounded mb-2" style="width:100%;height:110px;object-fit:cover;" alt="{{ $media->file_name }}">
                                    @endcan
                                @else
                                    <div class="text-center py-4 bg-light rounded mb-2"><i class="fas fa-file fa-3x text-muted"></i></div>
                                @endif

                                <div class="small text-truncate" title="{{ $media->file_name }}">{{ $media->file_name }}</div>

                                @can('lead_attachment_download')
                                    <a href="{{ route('admin.leads.attachments.download', [$lead->id, $media->id]) }}" class="btn btn-outline-info btn-xs mt-2">
                                        <i class="fas fa-download mr-1"></i>Download
                                    </a>
                                @endcan

                                @can('lead_update')
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input type="checkbox" class="custom-control-input" name="remove_attachment_ids[]" value="{{ $media->id }}" id="remove_attachment_{{ $media->id }}">
                                        <label class="custom-control-label text-danger small" for="remove_attachment_{{ $media->id }}">Remove</label>
                                    </div>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><p class="text-muted small">No existing attachments.</p></div>
                    @endforelse
                </div>
            </div>
        @endif

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Add Private Attachments</label>

            <div class="d-flex align-items-center flex-wrap mb-2" style="gap:8px;">
                <input type="file" name="attachments[]" id="lead_attachments" class="form-control-file"
                       style="max-width:520px;"
                       multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx">

                @can('media_list')
                    <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold" id="btnChooseLeadAttachmentImages">
                        <i class="fas fa-photo-video mr-1"></i>Choose Images from Media
                    </button>
                @endcan
            </div>

            <div id="lead-picker-media-inputs"></div>

            <div class="text-danger small mt-1 error-attachments"></div>
            <div class="text-danger small mt-1 error-attachment_media_ids"></div>

            <small class="text-muted d-block">
                Uploaded files and Media Picker images are copied into this Lead's private attachment collection.
                JPG/PNG/WebP/PDF/Word/Excel uploads: max 10 MB each; maximum 10 new attachments per request.
            </small>

            <div class="row mt-3" id="picker-attachment-previews"></div>
            <div class="row mt-1" id="new-attachment-previews"></div>
        </div>

        <div class="col-md-12">
            <hr>
            <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-code mr-1"></i>Attribution / Snapshot JSON</h6>
        </div>

        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Source Page URL</label>
            <input type="text" name="source_page_url" class="form-control" maxlength="255"
                   value="{{ old('source_page_url', $isEdit ? $lead->source_page_url : '') }}"
                   placeholder="/quote or https://example.com/page">
            <div class="invalid-feedback error-source_page_url"></div>
        </div>

        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Metadata JSON</label>
            <textarea name="metadata_json" class="form-control lead-json" rows="5" placeholder='{"calculator":{}}'>{{ old('metadata_json', $isEdit ? $json($lead->metadata) : '') }}</textarea>
            <div class="invalid-feedback error-metadata_json"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">UTM JSON</label>
            <textarea name="utm_json" class="form-control lead-json" rows="5" placeholder='{"utm_source":"google"}'>{{ old('utm_json', $isEdit ? $json($lead->utm) : '') }}</textarea>
            <div class="invalid-feedback error-utm_json"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Consent JSON</label>
            <textarea name="consent_json" class="form-control lead-json" rows="5" placeholder='{"privacy":true}'>{{ old('consent_json', $isEdit ? $json($lead->consent) : '') }}</textarea>
            <div class="invalid-feedback error-consent_json"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Pricing Snapshot JSON</label>
            <textarea name="pricing_snapshot_json" class="form-control lead-json" rows="5" placeholder='{"total":0}'>{{ old('pricing_snapshot_json', $isEdit ? $json($lead->pricing_snapshot) : '') }}</textarea>
            <div class="invalid-feedback error-pricing_snapshot_json"></div>
        </div>
    </div>

    <div class="text-right border-top pt-3 mt-2">
        <button type="button" class="btn btn-light border font-weight-bold px-4 mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-5">
            <i class="fas fa-save mr-2"></i>{{ $isEdit ? 'Update Lead' : 'Save Lead' }}
        </button>
    </div>
</form>

<style>
    #ajaxModal #ajax-form .select2-container {
        width: 100% !important;
    }

    #ajaxModal #ajax-form .select2-container--default .select2-selection--single {
        height: 38px !important;
        min-height: 38px !important;
        display: flex !important;
        align-items: center !important;
        border: 1px solid #ced4da !important;
        border-radius: .25rem !important;
        background-color: #fff !important;
    }

    #ajaxModal #ajax-form .select2-container--default
    .select2-selection--single .select2-selection__rendered {
        width: 100%;
        line-height: 36px !important;
        padding-left: 12px !important;
        padding-right: 30px !important;
        color: #495057 !important;
    }

    #ajaxModal #ajax-form .select2-container--default
    .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        top: 1px !important;
        right: 4px !important;
    }

    #ajaxModal #ajax-form .select2-container--default .select2-selection--multiple {
        min-height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: .25rem !important;
        padding: 3px 6px !important;
        background-color: #fff !important;
        display: flex !important;
        align-items: center !important;
    }

    #ajaxModal #ajax-form .select2-container--default
    .select2-selection--multiple .select2-selection__rendered {
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 4px !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        list-style: none !important;
    }

    #ajaxModal #ajax-form .select2-container--default
    .select2-selection--multiple .select2-selection__choice {
        float: none !important;
        margin: 1px 0 !important;
        padding: 3px 8px 3px 24px !important;
        border: 1px solid #ced4da !important;
        border-radius: .25rem !important;
        background: #f8f9fa !important;
        color: #495057 !important;
        line-height: 22px !important;
        max-width: 100%;
    }

    #ajaxModal #ajax-form .select2-container--default
    .select2-selection--multiple .select2-selection__choice__remove {
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 7px !important;
        border-right: 1px solid #ced4da !important;
    }

    #ajaxModal #ajax-form .select2-container--default
    .select2-selection--multiple .select2-search--inline {
        float: none !important;
        margin: 0 !important;
        flex: 1 1 150px;
    }

    #ajaxModal #ajax-form .select2-container--default
    .select2-selection--multiple .select2-search__field {
        margin: 0 !important;
        height: 28px !important;
        line-height: 28px !important;
        min-width: 120px !important;
    }

    .select2-container--open {
        z-index: 1065 !important;
    }

    .lead-picker-preview-card {
        position: relative;
        border: 1px solid #dee2e6;
        border-radius: .375rem;
        padding: .5rem;
        height: 100%;
        background: #fff;
    }

    .lead-picker-preview-card img {
        width: 100%;
        height: 105px;
        object-fit: cover;
        border-radius: .25rem;
    }

    .lead-picker-preview-card .btn-remove-picker-image {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 26px;
        height: 26px;
        padding: 0;
        border-radius: 50%;
    }
</style>


{{--
    IMPORTANT: This form is loaded through AJAX into #ajaxModal.
    A Blade @section('js') declared inside an AJAX partial is not rendered by the parent
    layout, so Select2 and Media Picker handlers never bind. Include the form JS directly.
--}}
@include('backoffice.admin.leads.partials.form_js')