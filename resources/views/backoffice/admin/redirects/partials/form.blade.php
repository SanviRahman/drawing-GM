@php($isEdit=isset($redirect))
<form id="ajax-form" action="{{ $isEdit?route('admin.redirects.update',$redirect->id):route('admin.redirects.store') }}" method="POST">
@csrf @if($isEdit) @method('PUT') @endif
<div class="row">
<div class="col-md-12 mb-3"><label class="font-weight-bold">From Path <span class="text-danger">*</span></label><input type="text" name="from_path" class="form-control" maxlength="500" placeholder="/old-page" value="{{ old('from_path',$isEdit?$redirect->from_path:'') }}" required><div class="invalid-feedback error-from_path"></div><small class="text-muted">Use a public path. Admin/storage/vendor paths are blocked.</small></div>
<div class="col-md-12 mb-3"><label class="font-weight-bold">Destination <span class="text-danger">*</span></label><input type="text" name="to_url" class="form-control" maxlength="2048" placeholder="/new-page or https://example.com/new-page" value="{{ old('to_url',$isEdit?$redirect->to_url:'') }}" required><div class="invalid-feedback error-to_url"></div></div>
<div class="col-md-6 mb-3"><label class="font-weight-bold">HTTP Status</label><select name="status_code" class="form-control">@foreach($statusCodes as $code=>$label)<option value="{{ $code }}" {{ (string)old('status_code',$isEdit?$redirect->status_code:301)===(string)$code?'selected':'' }}>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-6 mb-3"><label class="font-weight-bold">Status</label><select name="is_active" class="form-control"><option value="1" {{ (string)old('is_active',$isEdit?(int)$redirect->is_active:1)==='1'?'selected':'' }}>Active</option><option value="0" {{ (string)old('is_active',$isEdit?(int)$redirect->is_active:1)==='0'?'selected':'' }}>Inactive</option></select></div>
</div>
<div class="text-right border-top pt-3"><button type="button" class="btn btn-light border mr-2" data-dismiss="modal">Cancel</button><button class="btn btn-primary font-weight-bold px-4" type="submit"><i class="fas fa-save mr-1"></i>{{ $isEdit?'Update Redirect':'Save Redirect' }}</button></div>
</form>
