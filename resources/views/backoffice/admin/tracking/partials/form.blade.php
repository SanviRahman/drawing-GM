@php
    $isEdit = isset($trackingProvider);
    $currentProvider = old('provider', $isEdit ? $trackingProvider->provider : 'meta_pixel');
    $config = $isEdit ? ($trackingProvider->config ?? []) : [];
    $metaPixels = old('meta_pixels', $isEdit ? $trackingProvider->metaPixels() : []);
    $genericConfig = $config;
    unset($genericConfig['meta_pixels'], $genericConfig['raw_script_execution']);
@endphp

<form id="ajax-form" action="{{ $isEdit ? route('admin.tracking.update', $trackingProvider->id) : route('admin.tracking.store') }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="alert alert-info border-0">
        <i class="fas fa-shield-alt mr-1"></i>
        Raw JavaScript injection is disabled. Meta Pixel snippets are stored encrypted for admin reference,
        while public runtime uses validated Pixel IDs and generated provider code.
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Provider <span class="text-danger">*</span></label>
            <select name="provider" id="tracking_provider_key" class="form-control">
                @foreach($providers as $key => $label)
                    <option value="{{ $key }}" {{ $currentProvider === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback error-provider"></div>
        </div>

        <div class="col-md-8 mb-3">
            <label class="font-weight-bold">Public Identifier</label>
            <input type="text" name="public_identifier" class="form-control" maxlength="255"
                   value="{{ old('public_identifier', $isEdit ? $trackingProvider->public_identifier : '') }}"
                   placeholder="Meta pixel (Primary) ID">
            <div class="invalid-feedback error-public_identifier"></div>
        </div>

        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Secret / Access Token</label>
            <input type="password" name="secret" class="form-control" autocomplete="new-password"
                   placeholder="{{ $isEdit ? 'Leave blank to keep existing secret' : 'Optional encrypted secret' }}">
            <div class="invalid-feedback error-secret"></div>
            <small class="text-muted">Encrypted at rest and never displayed back.</small>
        </div>

        <div class="col-md-2 mb-3">
            <label class="font-weight-bold">Clear Secret</label>
            <select name="clear_secret" class="form-control">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>

        <div class="col-md-2 mb-3">
            <label class="font-weight-bold">Enabled</label>
            <select name="is_enabled" class="form-control">
                <option value="1" {{ (string) old('is_enabled', $isEdit ? (int) $trackingProvider->is_enabled : 0) === '1' ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ (string) old('is_enabled', $isEdit ? (int) $trackingProvider->is_enabled : 0) === '0' ? 'selected' : '' }}>No</option>
            </select>
        </div>

        <div class="col-md-2 mb-3">
            <label class="font-weight-bold">Mode</label>
            <select name="test_mode" class="form-control">
                <option value="1" {{ (string) old('test_mode', $isEdit ? (int) $trackingProvider->test_mode : 1) === '1' ? 'selected' : '' }}>Test</option>
                <option value="0" {{ (string) old('test_mode', $isEdit ? (int) $trackingProvider->test_mode : 1) === '0' ? 'selected' : '' }}>Live</option>
            </select>
        </div>
    </div>

    <div id="metaPixelConfig" class="{{ $currentProvider === 'meta_pixel' ? '' : 'd-none' }}">
        <hr>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="font-weight-bold text-primary mb-0">
                <i class="fab fa-facebook mr-1"></i>Multiple Meta Pixels / Scripts
            </h6>
            <button type="button" id="btnAddMetaPixel" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-plus mr-1"></i>Add Pixel
            </button>
        </div>

        <div id="metaPixelGeneralError" class="alert alert-danger py-2 px-3 d-none"></div>

        <div id="metaPixelRows">
            @foreach($metaPixels as $i => $pixel)
                <div class="card border mb-2 meta-pixel-row" data-row-index="{{ $i }}">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label class="small font-weight-bold">Label</label>
                                <input class="form-control"
                                       name="meta_pixels[{{ $i }}][label]"
                                       value="{{ $pixel['label'] ?? '' }}"
                                       placeholder="Secondary Pixel ID">
                                <div class="invalid-feedback field-error"></div>
                            </div>

                            <div class="col-md-3 mb-2">
                                <label class="small font-weight-bold">Pixel ID</label>
                                <input class="form-control"
                                       name="meta_pixels[{{ $i }}][pixel_id]"
                                       value="{{ $pixel['pixel_id'] ?? '' }}"
                                       placeholder="123456789012345">
                                <div class="invalid-feedback field-error"></div>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="small font-weight-bold">Active</label>
                                <select class="form-control" name="meta_pixels[{{ $i }}][is_active]">
                                    <option value="1" {{ (bool) ($pixel['is_active'] ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ ! (bool) ($pixel['is_active'] ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                                <div class="invalid-feedback field-error"></div>
                            </div>

                            <div class="col-md-4 mb-2 text-right">
                                <label class="small d-block">&nbsp;</label>
                                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-meta-pixel">
                                    <i class="fas fa-times mr-1"></i>Remove
                                </button>
                            </div>

                            <div class="col-md-12">
                                <label class="small font-weight-bold">Meta Pixel Script Snippet (optional)</label>
                                <textarea class="form-control text-monospace"
                                          rows="4"
                                          name="meta_pixels[{{ $i }}][script_snippet]"
                                          placeholder="Paste standard Meta Pixel base snippet. It is stored encrypted for reference and never executed raw.">{{ $pixel['script_snippet'] ?? '' }}</textarea>
                                <div class="invalid-feedback field-error"></div>
                                <small class="text-muted">
                                    If both Pixel ID and script are provided, the script's fbq('init', '...') ID must match this row's Pixel ID.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <template id="metaPixelRowTemplate">
            <div class="card border mb-2 meta-pixel-row">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Label</label>
                            <input class="form-control" data-field="label" placeholder="Primary Pixel">
                            <div class="invalid-feedback field-error"></div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Pixel ID</label>
                            <input class="form-control" data-field="pixel_id" placeholder="123456789012345">
                            <div class="invalid-feedback field-error"></div>
                        </div>

                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold">Active</label>
                            <select class="form-control" data-field="is_active">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                            <div class="invalid-feedback field-error"></div>
                        </div>

                        <div class="col-md-4 mb-2 text-right">
                            <label class="small d-block">&nbsp;</label>
                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-meta-pixel">
                                <i class="fas fa-times mr-1"></i>Remove
                            </button>
                        </div>

                        <div class="col-md-12">
                            <label class="small font-weight-bold">Meta Pixel Script Snippet (optional)</label>
                            <textarea class="form-control text-monospace" rows="4" data-field="script_snippet"
                                      placeholder="Standard Meta Pixel snippet; never executed raw."></textarea>
                            <div class="invalid-feedback field-error"></div>
                            <small class="text-muted">
                                If both Pixel ID and script are provided, both IDs must match.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <hr>

    <div class="mb-3">
        <label class="font-weight-bold">Provider Config JSON</label>
        <textarea name="config_json" class="form-control text-monospace" rows="8"
                  placeholder='{"consent_category":"marketing"}'>{{ old('config_json', $genericConfig ? json_encode($genericConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
        <div class="invalid-feedback error-config_json"></div>
        <small class="text-muted">Encrypted inside the JSON column. Do not place passwords in client-visible configuration.</small>
    </div>

    <div class="text-right border-top pt-3">
        <button type="button" class="btn btn-light border mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-4">
            <i class="fas fa-save mr-1"></i>{{ $isEdit ? 'Update Provider' : 'Save Provider' }}
        </button>
    </div>
</form>
