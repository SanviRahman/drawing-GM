@php
    $isEdit = isset($admin);
    $actionUrl = $isEdit ? route('admin.admins.update', $admin->id) : route('admin.admins.store');
    $currentPhotoUrl = $isEdit ? $admin->image_url : asset('images/no-image.png');
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ $isEdit ? $admin->name : old('name') }}" required placeholder="Enter full name">
            <div class="invalid-feedback error-name"></div>
        </div>

        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Username <span class="text-danger">*</span></label>
            <input type="text" name="username" class="form-control" value="{{ $isEdit ? $admin->username : old('username') }}" required placeholder="Enter username">
            <div class="invalid-feedback error-username"></div>
        </div>

        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" value="{{ $isEdit ? $admin->email : old('email') }}" required placeholder="email@example.com">
            <div class="invalid-feedback error-email"></div>
        </div>

        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Phone</label>
            <input type="text" name="phone" class="form-control" value="{{ $isEdit ? $admin->phone : old('phone') }}" maxlength="20" placeholder="Enter phone number">
            <div class="invalid-feedback error-phone"></div>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Profile Photo</label>
            <div class="d-flex align-items-center" style="gap: 10px;">
                <img id="admin-photo-preview" src="{{ $currentPhotoUrl }}" class="rounded-circle border" width="48" height="48" style="object-fit: cover;" alt="Profile Photo">
                <div class="flex-grow-1">
                    <input type="file" name="photo" id="admin_photo" class="form-control-file" accept="image/jpeg,image/png,image/jpg,image/webp">
                    <button type="button" class="btn btn-outline-primary btn-sm mt-1 btn-choose-media" data-target="admin_photo">
                        <i class="fas fa-photo-video mr-1"></i> Choose from Media
                    </button>
                </div>
            </div>
            <input type="hidden" name="photo_media_id" id="photo_media_id" value="">
            <div class="invalid-feedback error-photo"></div>
            <div class="text-danger small mt-1 error-photo_media_id"></div>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Assign Roles</label>
            <select name="roles[]" class="form-control select2" multiple data-placeholder="Select roles for this admin">
                @if(isset($roles))
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ ($isEdit && $admin->hasRole($role)) ? 'selected' : '' }}>
                            {{ ucfirst($role) }}
                        </option>
                    @endforeach
                @endif
            </select>
            <div class="invalid-feedback error-roles"></div>
            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Leave empty if you don't want to assign any specific roles right now.</small>
        </div>

        <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-control" required>
                <option value="1" {{ (!$isEdit || $admin->status == 1) ? 'selected' : '' }}>Active</option>
                <option value="0" {{ ($isEdit && $admin->status == 0) ? 'selected' : '' }}>Inactive</option>
            </select>
            <div class="invalid-feedback error-status"></div>
        </div>

        <div class="col-md-12">
            <hr>
            <h6 class="font-weight-bold text-primary mb-3">Security</h6>
        </div>

        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Password @if(!$isEdit) <span class="text-danger">*</span> @endif</label>
            <input type="password" name="password" class="form-control" placeholder="{{ $isEdit ? 'Leave blank to keep current password' : 'Enter strong password' }}" {{ !$isEdit ? 'required' : '' }}>
            <div class="invalid-feedback error-password"></div>
        </div>

        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Confirm Password @if(!$isEdit) <span class="text-danger">*</span> @endif</label>
            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm password" {{ !$isEdit ? 'required' : '' }}>
        </div>
    </div>

    <div class="text-right border-top pt-3 mt-2 bg-white rounded-bottom">
        <button type="button" class="btn btn-light border font-weight-bold px-4 mr-2 shadow-sm" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-5 shadow-sm">
            <i class="fas fa-save mr-2"></i> {{ $isEdit ? 'Update Admin' : 'Save Admin' }}
        </button>
    </div>
</form>

<script>
    if ($('.select2').length) {
        $('.select2').select2({
            width: '100%',
            dropdownParent: $('#ajaxModal')
        });
    }

    $('#admin_photo').on('change', function (e) {
        $('#photo_media_id').val('');

        if (e.target.files && e.target.files[0]) {
            let reader = new FileReader();
            reader.onload = function (ev) { $('#admin-photo-preview').attr('src', ev.target.result); };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    $('.btn-choose-media[data-target="admin_photo"]').on('click', function () {
        if (typeof MediaPicker === 'undefined') {
            Swal.fire('Error', 'Media Picker is not available on this page.', 'error');
            return;
        }

        MediaPicker.open(function (media) {
            $('#admin_photo').val('');
            $('#photo_media_id').val(media.id);
            $('#admin-photo-preview').attr('src', media.url);
        }, { type: 'image' });
    });
</script>
