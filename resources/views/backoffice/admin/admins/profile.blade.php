@extends('backoffice.admin.layouts.app')

@section('meta_title', 'Admin Profile')
@section('plugins.Sweetalert2', true)

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 profile-alert" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <strong>Success!</strong> {{ session('success') }}

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 profile-alert" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Please check the form carefully.</strong>

            <ul class="mb-0 mt-2 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form action="{{ route('admin.update_profile') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <div class="col-lg-4 col-md-5 mb-4">
                <div class="card profile-card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="profile-photo-wrapper mx-auto mb-3">
                            <img src="{{ $admin->image_url }}"
                                 id="profile-photo-preview"
                                 class="profile-photo"
                                 alt="{{ $admin->name }}">
                        </div>

                        <h4 class="font-weight-bold text-dark mb-1">
                            {{ $admin->name ?? 'Admin User' }}
                        </h4>

                        <p class="text-muted mb-2">
                            {{ $admin->email ?? 'No email found' }}
                        </p>

                        <span class="badge badge-primary px-3 py-2 rounded-pill">
                            <i class="fas fa-user-shield mr-1"></i>
                            Admin Account
                        </span>

                        <hr class="my-4">

                        <div class="text-left profile-info-box">
                            <div class="d-flex align-items-center mb-3">
                                <div class="profile-info-icon bg-primary-soft text-primary">
                                    <i class="fas fa-user"></i>
                                </div>

                                <div class="ml-3">
                                    <small class="text-muted d-block">Username</small>
                                    <strong>{{ $admin->username ?? 'N/A' }}</strong>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-3">
                                <div class="profile-info-icon bg-success-soft text-success">
                                    <i class="fas fa-envelope"></i>
                                </div>

                                <div class="ml-3">
                                    <small class="text-muted d-block">Email</small>
                                    <strong class="text-break">{{ $admin->email ?? 'N/A' }}</strong>
                                </div>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="profile-info-icon bg-warning-soft text-warning">
                                    <i class="fas fa-camera"></i>
                                </div>

                                <div class="ml-3">
                                    <small class="text-muted d-block">Photo</small>
                                    <strong>{{ $admin->hasProfilePhoto() ? 'Uploaded' : 'Not uploaded' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-md-7 mb-4">
                <div class="card border-0 shadow-sm profile-form-card">
                    <div class="card-header bg-white border-bottom px-4 py-3">
                        <h5 class="card-title mb-0 font-weight-bold">
                            <i class="fas fa-edit text-primary mr-2"></i>
                            Profile Information
                        </h5>

                        <small class="text-muted">
                            Update your personal information and profile photo.
                        </small>
                    </div>

                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="profile-label">
                                    Full Name <span class="text-danger">*</span>
                                </label>

                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-user text-muted"></i>
                                        </span>
                                    </div>

                                    <input type="text"
                                           name="name"
                                           value="{{ old('name', $admin->name) }}"
                                           class="form-control shadow-none @error('name') is-invalid @enderror"
                                           placeholder="Enter your full name"
                                           required>
                                </div>

                                @error('name')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="profile-label">
                                    Username <span class="text-danger">*</span>
                                </label>

                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-at text-muted"></i>
                                        </span>
                                    </div>

                                    <input type="text"
                                           name="username"
                                           value="{{ old('username', $admin->username) }}"
                                           class="form-control shadow-none @error('username') is-invalid @enderror"
                                           placeholder="Enter your username"
                                           required>
                                </div>

                                @error('username')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="profile-label">
                                    Email Address <span class="text-danger">*</span>
                                </label>

                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-envelope text-muted"></i>
                                        </span>
                                    </div>

                                    <input type="email"
                                           name="email"
                                           value="{{ old('email', $admin->email) }}"
                                           class="form-control shadow-none @error('email') is-invalid @enderror"
                                           placeholder="Enter your email address"
                                           required>
                                </div>

                                @error('email')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="profile-label">Profile Photo</label>

                                <div class="custom-file">
                                    <input type="file"
                                           name="photo"
                                           id="photo"
                                           class="custom-file-input @error('photo') is-invalid @enderror"
                                           accept="image/jpeg,image/png,image/jpg,image/webp">

                                    <label class="custom-file-label" for="photo">Select profile photo</label>
                                </div>

                                <input type="hidden" name="photo_media_id" id="photo_media_id" value="">
                                @can('media_list')
                                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btnChoosePhotoMedia">
                                        <i class="fas fa-photo-video mr-1"></i> Choose from Media
                                    </button>
                                @endcan

                                <small class="text-muted d-block mt-1">
                                    Recommended: JPG, PNG, WEBP. Use square image for best preview.
                                </small>

                                @error('photo')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror

                                @error('photo_media_id')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-light border mt-3 mb-0">
                            <div class="d-flex">
                                <div class="mr-3 text-primary">
                                    <i class="fas fa-info-circle fa-lg"></i>
                                </div>

                                <div>
                                    <strong>Profile Tip</strong>
                                    <p class="mb-0 text-muted small">
                                        Keep your name, username and email updated. Profile photo will appear in the admin panel header/sidebar where applicable.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top px-4 py-3 text-right">
                        <button type="submit" class="btn btn-primary px-5 shadow-sm">
                            <i class="fas fa-save mr-1"></i>
                            Update Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('css')
    <style>
        .profile-alert { border-radius: 12px; }
        .profile-card, .profile-form-card { border-radius: 14px; overflow: hidden; }
        .profile-card { background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); }
        .profile-photo-wrapper { width: 155px; height: 155px; border-radius: 50%; padding: 5px; background: linear-gradient(135deg, #007bff, #20c997); box-shadow: 0 12px 30px rgba(0, 123, 255, .18); }
        .profile-photo { width: 145px; height: 145px; border-radius: 50%; object-fit: cover; border: 4px solid #ffffff; background: #f8f9fa; }
        .profile-label { color: #6c757d; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: .35px; margin-bottom: 6px; }
        .profile-info-box { border: 1px solid #edf0f5; border-radius: 12px; padding: 16px; background: #ffffff; }
        .profile-info-icon { width: 38px; height: 38px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 38px; }
        .bg-primary-soft { background: rgba(0, 123, 255, .12); }
        .bg-success-soft { background: rgba(40, 167, 69, .12); }
        .bg-warning-soft { background: rgba(255, 193, 7, .18); }
        .input-group-text { border-color: #e9ecef; }
        .form-control, .custom-file-label { border-color: #e9ecef; border-radius: 8px; }
        .input-group .form-control { border-top-left-radius: 0; border-bottom-left-radius: 0; }
        .input-group .input-group-text { border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
        .btn { border-radius: 8px; font-weight: 600; }

        @media (max-width: 767px) {
            .profile-photo-wrapper { width: 135px; height: 135px; }
            .profile-photo { width: 125px; height: 125px; }
        }
    </style>
@endpush

@section('js')
    <script>
        $(document).ready(function () {
            $('#photo').on('change', function () {
                let input = this;
                let fileName = input.files && input.files[0] ? input.files[0].name : 'Select profile photo';

                $(this).next('.custom-file-label').html(fileName);
                $('#photo_media_id').val('');

                if (input.files && input.files[0]) {
                    let reader = new FileReader();

                    reader.onload = function (e) {
                        $('#profile-photo-preview').attr('src', e.target.result);
                    };

                    reader.readAsDataURL(input.files[0]);
                }
            });

            $('#btnChoosePhotoMedia').on('click', function () {
                if (typeof MediaPicker === 'undefined') {
                    Swal.fire('Error', 'Media Picker is not available on this page.', 'error');
                    return;
                }

                MediaPicker.open(function (media) {
                    $('#photo').val('');
                    $('.custom-file-label').html('Select profile photo');
                    $('#photo_media_id').val(media.id);
                    $('#profile-photo-preview').attr('src', media.url);
                }, { type: 'image', multiple: false, title: 'Choose Profile Photo' });
            });
        });
    </script>
@endsection
