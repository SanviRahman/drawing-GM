@php($isEdit = isset($media))
@if($isEdit)
<form id="ajax-form" action="{{ route('admin.media.update', $media->id) }}" method="POST">
    @csrf @method('PUT')
    <div class="form-group">
        <label class="font-weight-bold">Display Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ $media->name }}" maxlength="190" required>
        <div class="invalid-feedback error-name"></div>
    </div>
    <div class="form-group">
        <label class="font-weight-bold">Alt Text</label>
        <input type="text" name="alt_text" class="form-control" value="{{ $data['alt_text'] ?? '' }}" maxlength="255">
        <div class="invalid-feedback error-alt_text"></div>
        <small class="text-muted">Used by frontend components when this media is rendered as meaningful content.</small>
    </div>
    <div class="form-group">
        <label class="font-weight-bold">Caption</label>
        <textarea name="caption" class="form-control" rows="3" maxlength="1000">{{ $data['caption'] ?? '' }}</textarea>
        <div class="invalid-feedback error-caption"></div>
    </div>
    <div class="alert alert-light border small">
        <strong>File:</strong> {{ $media->file_name }}<br>
        <strong>Collection:</strong> {{ $media->collection_name }}<br>
        <strong>MIME:</strong> {{ $media->mime_type ?: 'unknown' }}
    </div>
    <div class="text-right"><button type="button" class="btn btn-light border mr-2" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Update Metadata</button></div>
</form>
@else
<form id="ajax-form" action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="form-group">
        <label class="font-weight-bold">Media Files <span class="text-danger">*</span></label>
        <input type="file" name="files[]" class="form-control-file" multiple required accept="image/jpeg,image/png,image/webp,image/gif,image/x-icon,video/mp4,video/webm,application/pdf">
        <div class="text-danger small mt-1 error-files"></div>
        <small class="text-muted">Up to 10 files, maximum 10MB each. JPG, PNG, WEBP, GIF, ICO, MP4, WEBM, PDF.</small>
    </div>
    <div class="alert alert-info small"><i class="fas fa-info-circle mr-1"></i>Files uploaded here become reusable global media and are owned by the uploading admin's <code>media_library</code> collection.</div>
    <div class="text-right"><button type="button" class="btn btn-light border mr-2" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-cloud-upload-alt mr-1"></i>Upload</button></div>
</form>
@endif
