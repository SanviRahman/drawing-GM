@php
$isEdit=isset($galleryItem);
$actionUrl=$isEdit?route('admin.gallery_items.update',$galleryItem->id):route('admin.gallery_items.store');
$imageUrl=$isEdit&&$galleryItem->hasMedia('image')?$galleryItem->getFirstMediaUrl('image'):asset('images/no-image.png');
$beforeUrl=$isEdit&&$galleryItem->hasMedia('before')?$galleryItem->getFirstMediaUrl('before'):asset('images/no-image.png');
$afterUrl=$isEdit&&$galleryItem->hasMedia('after')?$galleryItem->getFirstMediaUrl('after'):asset('images/no-image.png');
@endphp
<form id="ajax-form" action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit)
    @method('PUT')
    @endif
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Select Gallery <span class="text-danger">*</span></label>
            <select name="gallery_id" class="form-control select2" required>
                <option value="">Select Gallery</option>
                @foreach($galleries as $id=>$name)
                <option value="{{ $id }}" {{($isEdit&&$galleryItem->gallery_id==$id)?'selected':''}}>{{$name}}</option>
                @endforeach
            </select>
            <div class="invalid-feedback error-gallery_id"></div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Item Type <span class="text-danger">*</span></label>
            <select name="item_type" class="form-control" required>
                <option value="image" {{(!$isEdit||$galleryItem->item_type=='image')?'selected':''}}>Image</option>
                <option value="before_after" {{($isEdit&&$galleryItem->item_type=='before_after')?'selected':''}}>Before
                    / After</option>
            </select>
            <div class="invalid-feedback error-item_type"></div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Title</label>
            <input type="text" name="title" class="form-control" value="{{$isEdit?$galleryItem->title:old('title')}}"
                placeholder="Enter title">
            <div class="invalid-feedback error-title"></div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="{{$isEdit?$galleryItem->sort_order:0}}">
            <div class="invalid-feedback error-sort_order"></div>
        </div>
        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Caption</label>
            <textarea id="caption_editor" name="caption" class="form-control tinymce-editor" rows="8"
                data-editor-height="320"
                placeholder="Enter caption">{{$isEdit?$galleryItem->caption:old('caption')}}</textarea>
            <div class="invalid-feedback error-caption"></div>
        </div>
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h6 class="mb-0 font-weight-bold">
                        <i class="fas fa-image text-primary mr-1"></i> Main Image
                    </h6>
                </div>

                <div class="card-body">
                    <div class="media-upload-wrapper">

                        <div class="preview-wrapper mb-3 position-relative">
                            <img id="image_preview"
                                src="{{$imageUrl}}"
                                class="media-preview-image rounded border">

                            <button type="button"
                                class="btn btn-danger btn-sm remove-preview"
                                data-target="image">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <input type="file"
                                name="image"
                                id="image_file"
                                class="form-control-file"
                                accept="image/*">

                            <button type="button"
                                class="btn btn-primary choose-media"
                                data-target="image">
                                <i class="fas fa-images mr-1"></i>
                                Media Picker
                            </button>
                        </div>

                    </div>

                    <input type="hidden" name="image_media_id" id="image_media_id">

                    <div class="invalid-feedback error-image"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Before Image</label>
            <div class="mb-2">
                <img id="before_preview" src="{{$beforeUrl}}" width="90" height="90" class="rounded border"
                    style="object-fit:cover;">
            </div>
            <input type="file" name="before" id="before_file" class="form-control-file" accept="image/*">
            <button type="button" class="btn btn-outline-primary btn-sm mt-2 choose-media" data-target="before"><i
                    class="fas fa-images"></i> Media Picker</button>
            <input type="hidden" name="before_media_id" id="before_media_id">
            <div class="invalid-feedback error-before"></div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">After Image</label>
            <div class="mb-2">
                <img id="after_preview" src="{{$afterUrl}}" width="90" height="90" class="rounded border"
                    style="object-fit:cover;">
            </div>
            <input type="file" name="after" id="after_file" class="form-control-file" accept="image/*">
            <button type="button" class="btn btn-outline-primary btn-sm mt-2 choose-media" data-target="after"><i
                    class="fas fa-images"></i> Media Picker</button>
            <input type="hidden" name="after_media_id" id="after_media_id">
            <div class="invalid-feedback error-after"></div>
        </div>
        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
            <select name="is_active" class="form-control" required>
                <option value="1" {{(!$isEdit||$galleryItem->is_active)?'selected':''}}>Active</option>
                <option value="0" {{($isEdit&&!$galleryItem->is_active)?'selected':''}}>Inactive</option>
            </select>
            <div class="invalid-feedback error-is_active"></div>
        </div>
    </div>

    <div class="text-right border-top pt-3 mt-3">
        <button type="button" class="btn btn-light border mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary"><i
                class="fas fa-save mr-1"></i>{{$isEdit?'Update Gallery Item':'Save Gallery Item'}}</button>
    </div>
</form>
<script>
$(document).ready(function() {
    if ($('.select2').length) {
        $('.select2').select2({
            width: '110%',
            dropdownParent: $('#ajaxModal'),
            placeholder: 'Select Gallery',
            allowClear: true
        });
    }
    if (window.tinymce) {
        setTimeout(function() {
            tinymce.remove('#caption_editor');
            tinymce.init({
                selector: '#caption_editor',
                height: 320,
                menubar: true,
                plugins: 'link image lists table code',
                toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | code',
                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save();
                    });
                }
            });
        }, 500);
    }

    function previewImage(input, target) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $(target).attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    $('#image_file').on('change', function() {
        previewImage(this, '#image_preview');
    });
    $('#before_file').on('change', function() {
        previewImage(this, '#before_preview');
    });
    $('#after_file').on('change', function() {
        previewImage(this, '#after_preview');
    });
    $('.choose-media').on('click', function() {
        let target = $(this).data('target');
        if (typeof MediaPicker === 'undefined') {
            Swal.fire('Error', 'Media Picker not loaded', 'error');
            return;
        }
        MediaPicker.open(function(media) {
            if (!media) return;
            $('#' + target + '_media_id').val(media.id);
            $('#' + target + '_preview').attr('src', media.url);
            $('#' + target + '_file').val('');
        }, {
            type: 'image',
            multiple: false,
            title: 'Select Image'
        });
    });
    $('#ajax-form').on('submit', function() {
        if (window.tinymce) {
            tinymce.triggerSave();
        }
    });
});
</script>
<style>
.select2-container {
    width: 100% !important;
}
.select2-container--default .select2-selection--single {
    height: 38px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 38px !important;
}
</style>
<style>
.select2-container--default .select2-selection--single {
    height: 38px !important;
    display:flex !important;
    align-items:center !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height:normal !important;
    padding-left:12px !important;
}

.media-preview-image {
    width:120px;
    height:120px;
    object-fit:cover;
    display:block;
}

.preview-wrapper {
    width:120px;
}

.remove-preview {
    position:absolute;
    top:-8px;
    right:-8px;
    border-radius:50%;
    width:28px;
    height:28px;
    padding:0;
}
</style>

<script>
$(document).on('click','.remove-preview',function(){

    let target=$(this).data('target');

    $('#'+target+'_preview')
        .attr('src','{{ asset("images/no-image.png") }}');

    $('#'+target+'_media_id').val('');

    $('#'+target+'_file').val('');

});
</script>
