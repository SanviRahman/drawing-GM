@php($isEdit=isset($section)) @php($action=$isEdit?route('admin.campaign_sections.update',$section->id):route('admin.campaign_sections.store'))
<form id="ajax-form" action="{{ $action }}" method="POST">@csrf @if($isEdit)@method('PUT')@endif
<div class="row"><div class="col-md-6 mb-3"><label class="font-weight-bold">Campaign <span class="text-danger">*</span></label><select name="campaign_id" class="form-control" required>@foreach($campaigns as $c)<option value="{{ $c->id }}" {{ (int)old('campaign_id',$selectedCampaignId)===(int)$c->id?'selected':'' }}>{{ $c->title }}</option>@endforeach</select><div class="invalid-feedback error-campaign_id"></div></div><div class="col-md-6 mb-3"><label class="font-weight-bold">Section Type <span class="text-danger">*</span></label><select name="section_key" class="form-control" required>@foreach($types as $k=>$l)<option value="{{ $k }}" {{ old('section_key',$isEdit?$section->section_key:'hero')===$k?'selected':'' }}>{{ $l }}</option>@endforeach</select><div class="invalid-feedback error-section_key"></div></div><div class="col-md-6 mb-3"><label class="font-weight-bold">Heading</label><input type="text" name="heading" class="form-control" value="{{ old('heading',$isEdit?$section->heading:'') }}"></div><div class="col-md-6 mb-3"><label class="font-weight-bold">Subheading</label><input type="text" name="subheading" class="form-control" value="{{ old('subheading',$isEdit?$section->subheading:'') }}"></div><div class="col-md-3 mb-3"><label class="font-weight-bold">Enabled</label><select name="is_enabled" class="form-control"><option value="1" {{ (string)old('is_enabled',$isEdit?(int)$section->is_enabled:1)==='1'?'selected':'' }}>Yes</option><option value="0" {{ (string)old('is_enabled',$isEdit?(int)$section->is_enabled:1)==='0'?'selected':'' }}>No</option></select></div><div class="col-md-3 mb-3"><label class="font-weight-bold">Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order',$isEdit?$section->sort_order:0) }}"></div><div class="col-md-12 mb-3"><label class="font-weight-bold">Payload JSON</label><textarea name="payload_json" class="form-control text-monospace" rows="12" spellcheck="false">{{ old('payload_json',$isEdit?json_encode($section->payload,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES):"{}") }}</textarea><div class="invalid-feedback error-payload_json d-block"></div><small class="text-muted">JSON field: TinyMCE is intentionally not used. Payload is validated against the selected allowlisted section type.</small></div></div>
<div class="text-right border-top pt-3"><button type="button" class="btn btn-light border font-weight-bold px-4 mr-2" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary font-weight-bold px-5"><i class="fas fa-save mr-1"></i>{{ $isEdit?'Update Section':'Save Section' }}</button></div></form>
<style>
    #ajaxModal .select2-container {
        width: 100% !important;
    }

    #ajaxModal .select2-container--default .select2-selection--single {
        height: 38px !important;
        min-height: 38px !important;
        display: flex !important;
        align-items: center !important;
        border: 1px solid #ced4da !important;
        border-radius: .25rem !important;
    }

    #ajaxModal .select2-container--default
    .select2-selection--single
    .select2-selection__rendered {
        width: 100% !important;
        line-height: normal !important;
        padding-left: 12px !important;
        padding-right: 30px !important;
        color: #495057 !important;
    }

    #ajaxModal .select2-container--default
    .select2-selection--single
    .select2-selection__arrow {
        height: 36px !important;
        top: 1px !important;
        right: 4px !important;
    }

    #ajaxModal .select2-dropdown {
        z-index: 1060 !important;
    }

    #ajaxModal .select2-search--dropdown {
        padding: 6px !important;
    }

    #ajaxModal .select2-search--dropdown .select2-search__field {
        height: 36px !important;
        padding: 6px 10px !important;
        border: 1px solid #ced4da !important;
        border-radius: .25rem !important;
    }
</style>