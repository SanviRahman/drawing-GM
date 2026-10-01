@php
$isEdit=isset($testimonial);
$actionUrl=$isEdit?route('admin.testimonials.update',$testimonial->id):route('admin.testimonials.store');
$photoUrl=$isEdit&&$testimonial->hasMedia('photo')?$testimonial->getFirstMediaUrl('photo'):null;
$screenshotUrl=$isEdit&&$testimonial->hasMedia('testimonial_screenshot')?$testimonial->getFirstMediaUrl('testimonial_screenshot'):null;
$selectedService=old('service_ids.0',$isEdit?optional($testimonial->services->first())->id:'');
$selectedLocation=old('location_ids.0',$isEdit?optional($testimonial->locations->first())->id:'');
@endphp
<form id="ajax-form" action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit)@method('PUT')@endif
    <div class="row">
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Type <span
                    class="text-danger">*</span></label><select name="type" id="testimonial_type" class="form-control"
                required>
                <option value="text" {{ old('type',$isEdit?$testimonial->type:'text')==='text'?'selected':'' }}>Text
                </option>
                <option value="whatsapp_screenshot"
                    {{ old('type',$isEdit?$testimonial->type:'')==='whatsapp_screenshot'?'selected':'' }}>WhatsApp
                    Screenshot</option>
                <option value="video" {{ old('type',$isEdit?$testimonial->type:'')==='video'?'selected':'' }}>Video
                </option>
            </select>
            <div class="invalid-feedback error-type"></div>
        </div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Customer Name <span
                    class="text-danger">*</span></label><input type="text" name="customer_name" class="form-control"
                value="{{ old('customer_name',$isEdit?$testimonial->customer_name:'') }}" required>
            <div class="invalid-feedback error-customer_name"></div>
        </div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Customer Title</label><input type="text"
                name="customer_title" class="form-control"
                value="{{ old('customer_title',$isEdit?$testimonial->customer_title:'') }}">
            <div class="invalid-feedback error-customer_title"></div>
        </div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Rating</label><select name="rating"
                class="form-control">
                <option value="">No Rating</option>@foreach([1,2,3,4,5] as $ratingValue)<option
                    value="{{ $ratingValue }}"
                    {{ (string)old('rating',$isEdit?$testimonial->rating:'')===(string)$ratingValue?'selected':'' }}>
                    {{ $ratingValue }} Star</option>@endforeach
            </select>
            <div class="invalid-feedback error-rating"></div>
        </div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Source <span
                    class="text-danger">*</span></label><select name="source" class="form-control"
                required>@foreach(['google'=>'Google','whatsapp'=>'WhatsApp','facebook'=>'Facebook','direct'=>'Direct']
                as $key=>$label)<option value="{{ $key }}"
                    {{ old('source',$isEdit?$testimonial->source:'direct')===$key?'selected':'' }}>{{ $label }}</option>
                @endforeach</select>
            <div class="invalid-feedback error-source"></div>
        </div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Reviewed At</label><input type="datetime-local"
                name="reviewed_at" class="form-control"
                value="{{ old('reviewed_at',$isEdit&&$testimonial->reviewed_at?$testimonial->reviewed_at->format('Y-m-d\TH:i'):'') }}">
            <div class="invalid-feedback error-reviewed_at"></div>
        </div>
        <div class="col-md-12 mb-3"><label class="font-weight-bold">Source URL</label><input type="url"
                name="source_url" class="form-control"
                value="{{ old('source_url',$isEdit?$testimonial->source_url:'') }}" placeholder="https://...">
            <div class="invalid-feedback error-source_url"></div>
        </div>
        <div class="col-md-12 mb-3"><label class="font-weight-bold">Review</label><textarea id="testimonial_review"
                name="review" class="form-control tinymce-editor" rows="7"
                data-editor-height="300">{{ old('review',$isEdit?$testimonial->review:'') }}</textarea>
            <div class="invalid-feedback error-review"></div>
        </div>
        <div class="col-md-6 mb-3"><label class="font-weight-bold">Customer Photo</label>
            <div class="d-flex align-items-center flex-wrap" style="gap:10px;"><img id="photo_preview" @if($photoUrl)
                    src="{{ $photoUrl }}" @endif width="90" height="90"
                    class="rounded border bg-light {{ $photoUrl?'':'d-none' }}" style="object-fit:cover;"
                    alt="Customer photo preview">
                <div class="flex-grow-1"><input type="file" name="photo" id="photo_file" class="form-control-file"
                        accept="image/jpeg,image/png,image/webp">@can('media_list')<button type="button"
                        class="btn btn-outline-primary btn-sm mt-2 choose-media" data-target="photo"><i
                            class="fas fa-images mr-1"></i>Media Picker</button>@endcan<button type="button"
                        class="btn btn-outline-danger btn-sm mt-2 remove-media" data-target="photo"><i
                            class="fas fa-times mr-1"></i>Remove</button></div>
            </div><input type="hidden" name="photo_media_id" id="photo_media_id" value=""><input type="hidden"
                name="photo_remove" id="photo_remove" value="0">
            <div class="invalid-feedback error-photo"></div>
            <div class="text-danger small mt-1 error-photo_media_id"></div>
        </div>
        <div class="col-md-6 mb-3"><label class="font-weight-bold">Testimonial Screenshot</label>
            <div class="d-flex align-items-center flex-wrap" style="gap:10px;"><img id="testimonial_screenshot_preview"
                    @if($screenshotUrl) src="{{ $screenshotUrl }}" @endif width="90" height="90"
                    class="rounded border bg-light {{ $screenshotUrl?'':'d-none' }}" style="object-fit:cover;"
                    alt="Testimonial screenshot preview">
                <div class="flex-grow-1"><input type="file" name="testimonial_screenshot"
                        id="testimonial_screenshot_file" class="form-control-file"
                        accept="image/jpeg,image/png,image/webp">@can('media_list')<button type="button"
                        class="btn btn-outline-primary btn-sm mt-2 choose-media" data-target="testimonial_screenshot"><i
                            class="fas fa-images mr-1"></i>Media Picker</button>@endcan<button type="button"
                        class="btn btn-outline-danger btn-sm mt-2 remove-media" data-target="testimonial_screenshot"><i
                            class="fas fa-times mr-1"></i>Remove</button></div>
            </div><input type="hidden" name="testimonial_screenshot_media_id" id="testimonial_screenshot_media_id"
                value=""><input type="hidden" name="testimonial_screenshot_remove" id="testimonial_screenshot_remove"
                value="0">
            <div class="invalid-feedback error-testimonial_screenshot"></div>
            <div class="text-danger small mt-1 error-testimonial_screenshot_media_id"></div>
        </div>
        <div class="col-md-6 mb-3"><label class="font-weight-bold">Service</label><select name="service_ids[]"
                id="service_ids" class="form-control">
                <option value="">Select Service</option>@foreach($serviceOptions as $id=>$name)<option value="{{ $id }}"
                    {{ (string)$selectedService===(string)$id?'selected':'' }}>{{ $name }}</option>@endforeach
            </select>
            <div class="invalid-feedback error-service_ids"></div>
        </div>
        <div class="col-md-6 mb-3"><label class="font-weight-bold">Location</label><select name="location_ids[]"
                id="location_ids" class="form-control">
                <option value="">Select Location</option>@foreach($locationOptions as $id=>$name)<option
                    value="{{ $id }}" {{ (string)$selectedLocation===(string)$id?'selected':'' }}>{{ $name }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback error-location_ids"></div>
        </div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Sort Order</label><input type="number"
                name="sort_order" class="form-control"
                value="{{ old('sort_order',$isEdit?$testimonial->sort_order:0) }}" min="0">
            <div class="invalid-feedback error-sort_order"></div>
        </div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Featured</label><select name="is_featured"
                class="form-control">
                <option value="0" {{ !$isEdit||!$testimonial->is_featured?'selected':'' }}>No</option>
                <option value="1" {{ $isEdit&&$testimonial->is_featured?'selected':'' }}>Yes</option>
            </select>
            <div class="invalid-feedback error-is_featured"></div>
        </div>
        <div class="col-md-4 mb-3"><label class="font-weight-bold">Status</label><select name="is_active"
                class="form-control">
                <option value="1" {{ !$isEdit||$testimonial->is_active?'selected':'' }}>Active</option>
                <option value="0" {{ $isEdit&&!$testimonial->is_active?'selected':'' }}>Inactive</option>
            </select>
            <div class="invalid-feedback error-is_active"></div>
        </div>
    </div>
    <div class="text-right border-top pt-3"><button type="button" class="btn btn-light border mr-2"
            data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i
                class="fas fa-save mr-1"></i>{{ $isEdit?'Update Testimonial':'Save Testimonial' }}</button></div>
</form>
<style>
#globalMediaPickerModal {
    z-index: 1080 !important;
}
</style>