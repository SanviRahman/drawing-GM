@php
    $isEdit = isset($service) && $service instanceof \App\Models\Service;
    $actionUrl = $isEdit ? route('admin.services.update', $service->id) : route('admin.services.store');
    $heroConfig = $isEdit ? ($service->hero_config ?? []) : [];
    $heroHeading = data_get($heroConfig, 'heading');
    $heroOverlay = data_get($heroConfig, 'overlay');
    $heroFocalPosition = data_get($heroConfig, 'focal_position', 'center center');
    $heroCtaLabel = data_get($heroConfig, 'cta.label');
    $heroCtaUrl = data_get($heroConfig, 'cta.url');
    $publishedAtValue = $isEdit && $service->published_at ? $service->published_at->format('Y-m-d\TH:i') : '';
    $mediaCollections = [
        'hero_desktop' => ['label' => 'Desktop Hero', 'multiple' => false, 'max' => 1],
        'hero_mobile' => ['label' => 'Mobile Hero', 'multiple' => false, 'max' => 1],
        'gallery' => ['label' => 'Gallery', 'multiple' => true, 'max' => 20],
    ];
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data">
    @csrf

    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-8 mb-3">
            <label class="font-weight-bold">Service Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="service_name" class="form-control" value="{{ old('name', $isEdit ? $service->name : '') }}" required maxlength="190" placeholder="e.g. HDB Painting">
            <div class="invalid-feedback error-name"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Icon</label>
            <input type="text" name="icon" class="form-control" value="{{ old('icon', $isEdit ? $service->icon : '') }}" maxlength="100" placeholder="paint-roller">
            <div class="invalid-feedback error-icon"></div>
            <small class="text-muted">Controlled icon key only; no raw HTML.</small>
        </div>

        <div class="col-md-8 mb-3">
            <label class="font-weight-bold">Slug <span class="text-danger">*</span></label>
            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text">/services/</span></div>
                <input type="text" name="slug" id="service_slug" class="form-control" value="{{ old('slug', $isEdit ? $service->slug : '') }}" required maxlength="190" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" placeholder="hdb-painting">
            </div>
            <div class="invalid-feedback d-block error-slug"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
            <select name="status" id="service_status" class="form-control" required>
                @foreach(\App\Models\Service::STATUSES as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $isEdit ? $service->status : 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback error-status"></div>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Summary</label>
            <textarea name="summary" id="service_summary" class="form-control tinymce-editor" rows="7" data-editor-height="280" placeholder="Short service summary...">{{ old('summary', $isEdit ? $service->summary : '') }}</textarea>
            <div class="invalid-feedback error-summary"></div>
            <small class="text-muted">This field is stored as <code>TEXT</code>, therefore it uses TinyMCE.</small>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Content</label>
            <textarea name="content" id="service_content" class="form-control tinymce-editor" rows="12" data-editor-height="420" placeholder="Full service content...">{{ old('content', $isEdit ? $service->content : '') }}</textarea>
            <div class="invalid-feedback error-content"></div>
            <small class="text-muted">This field is stored as <code>LONGTEXT</code>, therefore it uses TinyMCE.</small>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Featured <span class="text-danger">*</span></label>
            <select name="is_featured" class="form-control" required>
                <option value="0" {{ old('is_featured', $isEdit ? (int) $service->is_featured : 0) == 0 ? 'selected' : '' }}>No</option>
                <option value="1" {{ old('is_featured', $isEdit ? (int) $service->is_featured : 0) == 1 ? 'selected' : '' }}>Yes</option>
            </select>
            <div class="invalid-feedback error-is_featured"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $isEdit ? $service->sort_order : '') }}" min="0" step="1" placeholder="Auto">
            <div class="invalid-feedback error-sort_order"></div>
            <small class="text-muted">Leave blank on create to append automatically.</small>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Published At</label>
            <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', $publishedAtValue) }}">
            <div class="invalid-feedback error-published_at"></div>
        </div>
    </div>

    <hr>

    <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-image mr-1"></i>Hero Configuration</h6>

    <div class="row">
        <div class="col-md-8 mb-3">
            <label class="font-weight-bold">Hero Heading</label>
            <input type="text" name="hero_heading" class="form-control" value="{{ old('hero_heading', $heroHeading) }}" maxlength="190" placeholder="Hero headline">
            <div class="invalid-feedback error-hero_heading"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Overlay Opacity</label>
            <input type="number" name="hero_overlay" class="form-control" value="{{ old('hero_overlay', $heroOverlay) }}" min="0" max="1" step="0.05" placeholder="0.45">
            <div class="invalid-feedback error-hero_overlay"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Focal Position</label>
            <input type="text" name="hero_focal_position" class="form-control" value="{{ old('hero_focal_position', $heroFocalPosition) }}" maxlength="50" placeholder="center center">
            <div class="invalid-feedback error-hero_focal_position"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">CTA Label</label>
            <input type="text" name="hero_cta_label" class="form-control" value="{{ old('hero_cta_label', $heroCtaLabel) }}" maxlength="100" placeholder="Get Free Quote">
            <div class="invalid-feedback error-hero_cta_label"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">CTA URL</label>
            <input type="text" name="hero_cta_url" class="form-control" value="{{ old('hero_cta_url', $heroCtaUrl) }}" maxlength="500" placeholder="/quote">
            <div class="invalid-feedback error-hero_cta_url"></div>
        </div>
    </div>

    <hr>

    <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-photo-video mr-1"></i>Service Media</h6>
    <div class="alert alert-info py-2"><i class="fas fa-info-circle mr-1"></i>Desktop and mobile hero collections are single-image collections. Gallery supports up to 20 images. Uploading or selecting a new set replaces the current collection.</div>

    @foreach($mediaCollections as $collection => $config)

        @php
            $existingMedia = $isEdit ? $service->getMedia($collection) : collect();
        @endphp

        <div class="card border shadow-none mb-4 media-block" data-collection="{{ $collection }}">
            <div class="card-header bg-light py-2"><strong>{{ $config['label'] }}</strong></div>

            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center mb-2" style="gap:8px;">
                    @if($config['multiple'])
                        <input type="file" name="{{ $collection }}[]" id="{{ $collection }}" class="form-control-file service-media-file" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                    @else
                        <input type="file" name="{{ $collection }}" id="{{ $collection }}" class="form-control-file service-media-file" accept="image/jpeg,image/png,image/webp,image/gif">
                    @endif

                    @if(auth('admin')->user()?->can('media_list'))
                        <button type="button" class="btn btn-outline-primary btn-sm btn-choose-service-media" data-collection="{{ $collection }}" data-label="{{ $config['label'] }}" data-multiple="{{ $config['multiple'] ? 1 : 0 }}" data-max="{{ $config['max'] }}"><i class="fas fa-images mr-1"></i>Media Picker</button>
                    @endif

                    <button type="button" class="btn btn-outline-danger btn-sm btn-clear-service-media" data-collection="{{ $collection }}"><i class="fas fa-times mr-1"></i>Clear</button>
                </div>

                <input type="hidden" name="{{ $collection }}_media_ids" id="{{ $collection }}_media_ids" value="">
                <input type="hidden" name="{{ $collection }}_clear" id="{{ $collection }}_clear" value="0">

                <div class="text-danger small mb-2 error-{{ $collection }}"></div>
                <div class="text-danger small mb-2 error-{{ $collection }}_media_ids"></div>

                <div class="d-flex flex-wrap media-preview-grid" id="{{ $collection }}_preview" style="gap:10px;">
                    @if($existingMedia->count() > 0)

                        @foreach($existingMedia as $media)

                            @php
                                try {
                                    $mediaUrl = $media->getUrl();
                                } catch (\Throwable) {
                                    $mediaUrl = null;
                                }
                            @endphp

                            @if($mediaUrl)
                                <div class="border rounded bg-light p-1 text-center" style="width:110px;"><img src="{{ $mediaUrl }}" alt="{{ $media->name }}" class="rounded" style="width:100px;height:72px;object-fit:cover;"><small class="text-muted d-block text-truncate mt-1" title="{{ $media->file_name }}">{{ $media->file_name }}</small></div>
                            @endif

                        @endforeach

                    @else

                        <div class="text-muted small empty-media-message"><i class="far fa-images mr-1"></i>No images selected.</div>

                    @endif
                </div>
            </div>
        </div>

    @endforeach

    <div class="d-flex justify-content-end border-top pt-3 mt-4">
        <button type="button" class="btn btn-light mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>{{ $isEdit ? 'Update Service' : 'Save Service' }}</button>
    </div>
</form>

@section('js')
@include('backoffice.admin.services.partials.formjs')
@endsection