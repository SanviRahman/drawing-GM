@php
    $isEdit = isset($faq) && $faq instanceof \App\Models\Faq;
    $actionUrl = $isEdit ? route('admin.faqs.update', $faq->id) : route('admin.faqs.store');
    $selectedPages = collect(old('page_ids', $isEdit ? $faq->pages->pluck('id')->all() : array_filter([(int) ($preselectedPageId ?? 0)])))->map(fn($id) => (string) $id)->all();
    $selectedServices = collect(old('service_ids', $isEdit ? $faq->services->pluck('id')->all() : []))->map(fn($id) => (string) $id)->all();
    $selectedLocations = collect(old('location_ids', $isEdit ? $faq->locations->pluck('id')->all() : []))->map(fn($id) => (string) $id)->all();
    $selectedNavItems = collect(old('menu_item_ids', $isEdit ? $faq->menuItems->pluck('id')->all() : array_filter([(int) ($preselectedMenuItemId ?? 0)])))->map(fn($id) => (string) $id)->all();
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Question <span class="text-danger">*</span></label>
            <textarea name="question" id="faq_question" class="form-control tinymce-editor" rows="5" data-editor-height="220" placeholder="Enter FAQ question...">{{ old('question', $isEdit ? $faq->question : '') }}</textarea>
            <div class="invalid-feedback error-question"></div>
            <small class="text-muted">Database type is <code>TEXT</code>, so the project TinyMCE rule is applied.</small>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Answer <span class="text-danger">*</span></label>
            <textarea name="answer" id="faq_answer" class="form-control tinymce-editor" rows="10" data-editor-height="380" placeholder="Enter FAQ answer...">{{ old('answer', $isEdit ? $faq->answer : '') }}</textarea>
            <div class="invalid-feedback error-answer"></div>
            <small class="text-muted">Database type is <code>LONGTEXT</code>, so TinyMCE is used.</small>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
            <select name="is_active" class="form-control" required>
                <option value="1" {{ old('is_active', $isEdit ? (int) $faq->is_active : 1) == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('is_active', $isEdit ? (int) $faq->is_active : 1) == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
            <div class="invalid-feedback error-is_active"></div>
        </div>

        <div class="col-md-12 mb-3">
            <div class="faq-mapping-card">
                <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                    <div>
                        <h6 class="font-weight-bold text-primary mb-1">
                            <i class="fas fa-link mr-1"></i>Reusable FAQ Mapping
                        </h6>
                        <small class="text-muted">Assign to navbar sections and/or existing pages, services and locations.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="font-weight-bold mb-1" for="faq_menu_item_ids">
                        <i class="fas fa-bars text-info mr-1"></i>Primary Navbar Sections
                    </label>
                    <select id="faq_menu_item_ids" name="menu_item_ids[]" class="form-control faq-select2" multiple data-placeholder="Choose Home, Plastering, Hacking, etc.">
                        @foreach($navOptions as $id => $label)
                            <option value="{{ $id }}" {{ in_array((string) $id, $selectedNavItems, true) ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback error-menu_item_ids"></div>
                    <small class="text-muted">Selecting a navbar item assigns this FAQ to that specific destination. You can share a FAQ across several menu items.</small>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                        <label class="font-weight-bold mb-1">Pages</label>
                        <select name="page_ids[]" class="form-control faq-select2" multiple data-placeholder="Select pages">
                            @foreach($pageOptions as $id => $label)
                                <option value="{{ $id }}" {{ in_array((string) $id, $selectedPages, true) ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback error-page_ids"></div>
                    </div>

                    <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                        <label class="font-weight-bold mb-1">Services</label>
                        <select name="service_ids[]" class="form-control faq-select2" multiple data-placeholder="Select services">
                            @foreach($serviceOptions as $id => $label)
                                <option value="{{ $id }}" {{ in_array((string) $id, $selectedServices, true) ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback error-service_ids"></div>
                    </div>

                    <div class="col-lg-4 col-md-6 mb-0">
                        <label class="font-weight-bold mb-1">Locations</label>
                        <select name="location_ids[]" class="form-control faq-select2" multiple data-placeholder="Select locations">
                            @foreach($locationOptions as $id => $label)
                                <option value="{{ $id }}" {{ in_array((string) $id, $selectedLocations, true) ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback error-location_ids"></div>
                    </div>
                </div>

                <div class="alert alert-light border small mb-0 mt-3 py-2">
                    <i class="fas fa-info-circle text-info mr-1"></i>
                    Mapping order is target-specific and stored in <code>faqables.sort_order</code>. Existing order is preserved; newly attached FAQs are appended.
                </div>
            </div>
        </div>
    </div>

    <div class="text-right border-top pt-3 mt-2">
        <button type="button" class="btn btn-light border font-weight-bold px-4 mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-5">
            <i class="fas fa-save mr-2"></i>{{ $isEdit ? 'Update FAQ' : 'Save FAQ' }}
        </button>
    </div>
</form>
