@php
    $isEdit = isset($post);
    $actionUrl = $isEdit ? route('admin.posts.update', $post->id) : route('admin.posts.store');
    $featuredUrl = $isEdit ? $post->featured_image_url : null;
    $existingContentMedia = $isEdit ? $post->getMedia(\App\Models\Post::CONTENT_IMAGES_COLLECTION) : collect();
    $selectedAuthor = old('author_id', $isEdit ? $post->author_id : auth('admin')->id());
    $selectedCategory = old('category_id', $isEdit ? $post->category_id : null);
    $selectedStatus = old('status', $isEdit ? $post->status : 'draft');
    $publishedAt = old(
        'published_at',
        $isEdit && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : ''
    );
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <div class="col-md-8 mb-3">
            <label class="font-weight-bold">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" id="post_title" class="form-control" maxlength="190" required value="{{ old('title', $isEdit ? $post->title : '') }}" placeholder="Enter blog post title">
            <div class="invalid-feedback error-title"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Slug</label>
            <input type="text" name="slug" id="post_slug" class="form-control" maxlength="190" value="{{ old('slug', $isEdit ? $post->slug : '') }}" placeholder="Auto-generated from title">
            <div class="invalid-feedback error-slug"></div>
            <small class="text-muted">Leave blank on create to auto-generate.</small>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Author <span class="text-danger">*</span></label>
            <select name="author_id" class="form-control select2" required data-placeholder="Select author">
                @foreach($authors as $author)
                    <option value="{{ $author->id }}" {{ (string) $selectedAuthor === (string) $author->id ? 'selected' : '' }}>
                        {{ $author->name }}{{ $author->trashed() ? ' [Trashed]' : (!$author->status ? ' [Inactive]' : '') }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback error-author_id"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Category</label>
            <select name="category_id" class="form-control select2" data-placeholder="Uncategorized">
                <option value="">Uncategorized</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ (string) $selectedCategory === (string) $category->id ? 'selected' : '' }}>
                        {{ $category->name }}{{ $category->trashed() ? ' [Trashed]' : (!$category->is_active ? ' [Inactive]' : '') }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback error-category_id"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
            <select name="status" id="post_status" class="form-control" required>
                @foreach(\App\Models\Post::STATUSES as $value => $label)
                    <option value="{{ $value }}" {{ $selectedStatus === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback error-status"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Publish Date / Schedule</label>
            <input type="datetime-local" name="published_at" class="form-control" value="{{ $publishedAt }}">
            <div class="invalid-feedback error-published_at"></div>
            <small class="text-muted">Published + future time = scheduled post. Blank Published posts use current time.</small>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Reading Minutes</label>
            <input type="number" name="reading_minutes" min="1" step="1" class="form-control" value="{{ old('reading_minutes', $isEdit ? $post->reading_minutes : '') }}" placeholder="e.g. 5">
            <div class="invalid-feedback error-reading_minutes"></div>
        </div>

        <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Allow Comments <span class="text-danger">*</span></label>
            <select name="allow_comments" class="form-control" required>
                <option value="0" {{ !old('allow_comments', $isEdit ? $post->allow_comments : false) ? 'selected' : '' }}>No</option>
                <option value="1" {{ old('allow_comments', $isEdit ? $post->allow_comments : false) ? 'selected' : '' }}>Yes</option>
            </select>
            <div class="invalid-feedback error-allow_comments"></div>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Excerpt</label>
            <textarea name="excerpt" id="post_excerpt" class="form-control tinymce-editor" rows="6" data-editor-height="240" placeholder="Short article excerpt...">{{ old('excerpt', $isEdit ? $post->excerpt : '') }}</textarea>
            <div class="invalid-feedback error-excerpt d-block"></div>
            <small class="text-muted">Stored as <code>TEXT</code>, therefore TinyMCE is used.</small>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Body <span class="text-danger">*</span></label>
            <textarea name="body" id="post_body" class="form-control tinymce-editor" rows="14" data-editor-height="520" required placeholder="Write the full blog article...">{{ old('body', $isEdit ? $post->body : '') }}</textarea>
            <div class="invalid-feedback error-body d-block"></div>
            <small class="text-muted">Stored as <code>LONGTEXT</code>, therefore TinyMCE is used.</small>
        </div>
    </div>

    <hr>
    <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-image mr-1"></i>Featured Image</h6>

    <div class="card border shadow-none mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center mb-3" style="gap:8px;">
                <input type="file" name="featured" id="post_featured" class="form-control-file" accept="image/jpeg,image/png,image/webp,image/gif">
                @can('media_list')
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnChooseFeatured"><i class="fas fa-photo-video mr-1"></i>Media Picker</button>
                @endcan
                <button type="button" class="btn btn-outline-danger btn-sm" id="btnRemoveFeatured"><i class="fas fa-times mr-1"></i>Remove</button>
            </div>

            <input type="hidden" name="featured_media_id" id="featured_media_id" value="">
            <input type="hidden" name="featured_remove" id="featured_remove" value="0">
            <div class="text-danger small error-featured"></div>
            <div class="text-danger small error-featured_media_id"></div>

            <div id="featured_preview_wrap" class="{{ $featuredUrl ? '' : 'd-none' }}">
                <img id="featured_preview" src="{{ $featuredUrl ?: asset('images/no-image.png') }}" alt="Featured image preview" class="rounded border shadow-sm" style="width:220px;height:140px;object-fit:cover;">
            </div>
            <div id="featured_empty" class="text-muted small {{ $featuredUrl ? 'd-none' : '' }}"><i class="far fa-image mr-1"></i>No featured image selected.</div>
        </div>
    </div>

    <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-images mr-1"></i>Content Images</h6>
    <div class="card border shadow-none mb-3">
        <div class="card-body">
            <div class="alert alert-info py-2">
                <i class="fas fa-info-circle mr-1"></i>These are Post-owned Spatie <code>content_images</code>. You can upload/select new images, preview them, remove individual images, or remove all.
            </div>

            <div class="d-flex flex-wrap align-items-center mb-3" style="gap:8px;">
                <input type="file" name="content_images[]" id="content_images" class="form-control-file" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                @can('media_list')
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnChooseContentImages"><i class="fas fa-photo-video mr-1"></i>Media Picker</button>
                @endcan
                <button type="button" class="btn btn-outline-danger btn-sm" id="btnClearContentImages"><i class="fas fa-trash-alt mr-1"></i>Remove All</button>
            </div>

            <input type="hidden" name="content_images_media_ids" id="content_images_media_ids" value="[]">
            <input type="hidden" name="content_images_remove_ids" id="content_images_remove_ids" value="[]">
            <input type="hidden" name="content_images_clear" id="content_images_clear" value="0">

            <div class="text-danger small error-content_images"></div>
            <div class="text-danger small error-content_images_media_ids"></div>
            <div class="text-danger small error-content_images_remove_ids"></div>

            <div id="content_images_preview" class="d-flex flex-wrap" style="gap:10px;">
                @foreach($existingContentMedia as $media)
                    @php
                        try { $mediaUrl = $media->getUrl(); } catch (\Throwable) { $mediaUrl = null; }
                    @endphp
                    @if($mediaUrl)
                        <div class="border rounded bg-light p-1 text-center position-relative post-content-preview-item" data-source="existing" data-media-id="{{ $media->id }}" style="width:122px;">
                            <button type="button" class="btn btn-danger btn-sm btn-remove-content-image position-absolute" data-source="existing" data-media-id="{{ $media->id }}" title="Remove image" style="right:3px;top:3px;padding:1px 5px;z-index:2;"><i class="fas fa-times"></i></button>
                            <img src="{{ $mediaUrl }}" alt="{{ $media->name }}" class="rounded" style="width:110px;height:78px;object-fit:cover;">
                            <small class="text-muted d-block text-truncate mt-1" title="{{ $media->file_name }}">{{ $media->file_name }}</small>
                        </div>
                    @endif
                @endforeach

                <div id="content_images_empty" class="text-muted small {{ $existingContentMedia->count() ? 'd-none' : '' }}"><i class="far fa-images mr-1"></i>No content images selected.</div>
            </div>
        </div>
    </div>

    <div class="text-right border-top pt-3 mt-3 bg-white rounded-bottom">
        <button type="button" class="btn btn-light border font-weight-bold px-4 mr-2 shadow-sm" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-5 shadow-sm"><i class="fas fa-save mr-2"></i>{{ $isEdit ? 'Update Post' : 'Save Post' }}</button>
    </div>
</form>

<style>
/* Post modal Select2: match Bootstrap form-control height without affecting other UI. */
#ajaxModal #ajax-form .select2-container {
    width: 100% !important;
}

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

@section('js')
@include('backoffice.admin.posts.partials.form_js', ['isEdit' => $isEdit, 'featuredUrl' => $featuredUrl, 'existingContentMedia' => $existingContentMedia])
@endsection

