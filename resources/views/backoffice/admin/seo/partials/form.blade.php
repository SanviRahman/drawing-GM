@php
$isEdit = isset($seoMeta);
$actionUrl = $isEdit ? route('admin.seo.update', $seoMeta->id) : route('admin.seo.store');
$typeKey = $isEdit ? $seoMeta->typeKey() : old('seoable_type_key', 'page');
$ownerId = $isEdit ? $seoMeta->seoable_id : old('seoable_id');
$ownerLabel = $isEdit ? $seoMeta->ownerLabel() : '';
$socialUrl = $isEdit ? $seoMeta->social_image_url : null;
@endphp
<form id="ajax-form" action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data">
@csrf @if($isEdit) @method('PUT') @endif
<div class="row">
<div class="col-md-4 mb-3"><label class="font-weight-bold">Content Type <span class="text-danger">*</span></label><select name="seoable_type_key" id="seoable_type_key" class="form-control" required>@foreach($typeLabels as $key=>$label)<option value="{{ $key }}" {{ $typeKey===$key?'selected':'' }}>{{ $label }}</option>@endforeach</select><div class="invalid-feedback error-seoable_type_key"></div></div>
<div class="col-md-8 mb-3"><label class="font-weight-bold">Content Item <span class="text-danger">*</span></label><select name="seoable_id" id="seoable_id" class="form-control select2" data-selected="{{ $ownerId }}" data-selected-text="{{ $ownerLabel }}" required>@if($ownerId)<option value="{{ $ownerId }}" selected>{{ $ownerLabel }}</option>@endif</select><div class="invalid-feedback error-seoable_id"></div></div>

<div class="col-md-6 mb-3"><label class="font-weight-bold">Meta Title</label><input type="text" name="meta_title" class="form-control" maxlength="190" value="{{ old('meta_title',$isEdit?$seoMeta->meta_title:'') }}"><div class="invalid-feedback error-meta_title"></div></div>
<div class="col-md-6 mb-3"><label class="font-weight-bold">Canonical URL</label><input type="url" name="canonical_url" class="form-control" maxlength="2048" placeholder="https://example.com/page" value="{{ old('canonical_url',$isEdit?$seoMeta->canonical_url:'') }}"><div class="invalid-feedback error-canonical_url"></div></div>

<div class="col-md-12 mb-3"><label class="font-weight-bold">Meta Description</label><textarea name="meta_description" id="seo_meta_description" class="form-control tinymce-editor" rows="5" data-editor-height="240">{{ old('meta_description',$isEdit?$seoMeta->meta_description:'') }}</textarea><div class="invalid-feedback error-meta_description d-block"></div><small class="text-muted">Stored as TEXT; frontend metadata builder strips HTML before rendering the meta tag.</small></div>

<div class="col-md-6 mb-3"><label class="font-weight-bold">Robots <span class="text-danger">*</span></label><select name="robots" class="form-control">@foreach($robotsOptions as $key=>$label)<option value="{{ $key }}" {{ old('robots',$isEdit?$seoMeta->robots:'index,follow')===$key?'selected':'' }}>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-3 mb-3"><label class="font-weight-bold">Include in Sitemap</label><select name="include_in_sitemap" class="form-control"><option value="1" {{ (string)old('include_in_sitemap',$isEdit?(int)$seoMeta->include_in_sitemap:1)==='1'?'selected':'' }}>Yes</option><option value="0" {{ (string)old('include_in_sitemap',$isEdit?(int)$seoMeta->include_in_sitemap:1)==='0'?'selected':'' }}>No</option></select></div>
<div class="col-md-3 mb-3"><label class="font-weight-bold">Sitemap Priority</label><input type="number" name="sitemap_priority" class="form-control" step="0.1" min="0" max="1" value="{{ old('sitemap_priority',$isEdit?$seoMeta->sitemap_priority:'') }}"></div>
<div class="col-md-4 mb-3"><label class="font-weight-bold">Change Frequency</label><select name="sitemap_changefreq" class="form-control"><option value="">-- None --</option>@foreach($changefreqOptions as $key=>$label)<option value="{{ $key }}" {{ old('sitemap_changefreq',$isEdit?$seoMeta->sitemap_changefreq:'')===$key?'selected':'' }}>{{ $label }}</option>@endforeach</select></div>

<div class="col-md-8 mb-3"><label class="font-weight-bold">OG Title</label><input type="text" name="og_title" class="form-control" maxlength="190" value="{{ old('og_title',$isEdit?$seoMeta->og_title:'') }}"></div>
<div class="col-md-12 mb-3"><label class="font-weight-bold">OG Description</label><textarea name="og_description" id="seo_og_description" class="form-control tinymce-editor" rows="5" data-editor-height="240">{{ old('og_description',$isEdit?$seoMeta->og_description:'') }}</textarea><div class="invalid-feedback error-og_description d-block"></div></div>

<div class="col-md-12 mb-3"><label class="font-weight-bold">Social Image</label>
<div class="d-flex align-items-start flex-wrap" style="gap:12px">
<div id="social_image_preview_wrap" class="{{ $socialUrl ? '' : 'd-none' }}"><img id="social_image_preview" src="{{ $socialUrl ?: '' }}" style="width:160px;height:100px;object-fit:cover" class="rounded border" alt="Social image"></div>
<div class="flex-grow-1">
<input type="file" name="social_image" id="social_image" class="form-control-file" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif">
<input type="hidden" name="social_image_media_id" id="social_image_media_id" value="">
<input type="hidden" name="social_image_remove" id="social_image_remove" value="0">
<div class="mt-2">
@can('media_list')<button type="button" class="btn btn-outline-primary btn-sm" id="btnChooseSocialImage"><i class="fas fa-photo-video mr-1"></i>Choose from Media</button>@endcan
<button type="button" class="btn btn-outline-danger btn-sm" id="btnRemoveSocialImage"><i class="fas fa-times mr-1"></i>Remove</button>
</div>
<div class="text-danger small error-social_image"></div><div class="text-danger small error-social_image_media_id"></div>
</div></div></div>

<div class="col-md-12 mb-3"><label class="font-weight-bold">Schema Overrides JSON</label><textarea name="schema_overrides" class="form-control text-monospace" rows="8" placeholder='{"@type":"Service"}'>{{ old('schema_overrides',$isEdit&&$seoMeta->schema_overrides?json_encode($seoMeta->schema_overrides,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES):'') }}</textarea><div class="invalid-feedback error-schema_overrides"></div><small class="text-warning">Only validated structured overrides should be consumed publicly. Never render this as arbitrary script.</small></div>
</div>
<div class="text-right border-top pt-3"><button type="button" class="btn btn-light border mr-2" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary font-weight-bold px-4"><i class="fas fa-save mr-1"></i>{{ $isEdit?'Update SEO':'Save SEO' }}</button></div>
</form>
