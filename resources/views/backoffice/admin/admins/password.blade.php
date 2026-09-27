@extends('backoffice.admin.layouts.app')

@section('meta_title', 'Change Password')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 password-alert" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <strong>Success!</strong> {{ session('success') }}

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 password-alert" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Please fix the following errors:</strong>

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

    <form action="{{ route('admin.update_password') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-lg-4 col-md-5 mb-4">
                <div class="card border-0 shadow-sm password-side-card h-100">
                    <div class="card-body p-4 text-center">
                        <div class="security-icon mx-auto mb-3">
                            <i class="fas fa-shield-alt"></i>
                        </div>

                        <h4 class="font-weight-bold text-dark mb-2">
                            Account Security
                        </h4>

                        <p class="text-muted small mb-4">
                            Use a strong password with uppercase, lowercase, number and special character.
                        </p>

                        <div class="security-tips text-left">
                            <div class="security-tip-item">
                                <i class="fas fa-check-circle text-success mr-2"></i>
                                Minimum 8 characters recommended
                            </div>

                            <div class="security-tip-item">
                                <i class="fas fa-check-circle text-success mr-2"></i>
                                Avoid using old passwords
                            </div>

                            <div class="security-tip-item">
                                <i class="fas fa-check-circle text-success mr-2"></i>
                                Do not share your password
                            </div>

                            <div class="security-tip-item mb-0">
                                <i class="fas fa-check-circle text-success mr-2"></i>
                                Keep your account private
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-md-7 mb-4">
                <div class="card border-0 shadow-sm password-form-card">
                    <div class="card-header bg-white border-bottom px-4 py-3">
                        <h5 class="card-title mb-0 font-weight-bold">
                            <i class="fas fa-key text-primary mr-2"></i>
                            Password Information
                        </h5>

                        <small class="text-muted">
                            Enter your current password and choose a new secure password.
                        </small>
                    </div>

                    <div class="card-body p-4">
                        <div class="form-group">
                            <label class="password-label">
                                Current Password <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-lock text-muted"></i>
                                    </span>
                                </div>

                                <input type="password"
                                       name="current_password"
                                       id="current_password"
                                       value="{{ old('current_password') }}"
                                       class="form-control shadow-none password-field @error('current_password') is-invalid @enderror"
                                       placeholder="Current Password"
                                       required>

                                <div class="input-group-append">
                                    <button type="button"
                                            class="btn btn-outline-secondary toggle-password"
                                            data-target="#current_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            @error('current_password')
                                <span class="text-danger small d-block mt-1">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="password-label">
                                New Password <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-key text-muted"></i>
                                    </span>
                                </div>

                                <input type="password"
                                       name="password"
                                       id="password"
                                       value="{{ old('password') }}"
                                       class="form-control shadow-none password-field @error('password') is-invalid @enderror @error('new_password') is-invalid @enderror"
                                       placeholder="New Password"
                                       required>

                                <div class="input-group-append">
                                    <button type="button"
                                            class="btn btn-outline-secondary toggle-password"
                                            data-target="#password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="password-strength mt-2">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar"
                                         id="password-strength-bar"
                                         role="progressbar"
                                         style="width: 0%;"></div>
                                </div>

                                <small id="password-strength-text" class="text-muted">
                                    Password strength will appear here.
                                </small>
                            </div>

                            @error('password')
                                <span class="text-danger small d-block mt-1">
                                    {{ $message }}
                                </span>
                            @enderror

                            @error('new_password')
                                <span class="text-danger small d-block mt-1">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <label class="password-label">
                                Confirm New Password <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-check-double text-muted"></i>
                                    </span>
                                </div>

                                <input type="password"
                                       name="password_confirmation"
                                       id="password_confirmation"
                                       value="{{ old('password_confirmation') }}"
                                       class="form-control shadow-none password-field @error('password_confirmation') is-invalid @enderror @error('new_password_confirmation') is-invalid @enderror"
                                       placeholder="Confirm New Password"
                                       required>

                                <div class="input-group-append">
                                    <button type="button"
                                            class="btn btn-outline-secondary toggle-password"
                                            data-target="#password_confirmation">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <small id="password-match-text" class="d-block mt-2 text-muted">
                                Confirm password should match new password.
                            </small>

                            @error('password_confirmation')
                                <span class="text-danger small d-block mt-1">
                                    {{ $message }}
                                </span>
                            @enderror

                            @error('new_password_confirmation')
                                <span class="text-danger small d-block mt-1">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="alert alert-light border mt-4 mb-0">
                            <div class="d-flex">
                                <div class="mr-3 text-warning">
                                    <i class="fas fa-lightbulb fa-lg"></i>
                                </div>

                                <div>
                                    <strong>Password Tip</strong>
                                    <p class="mb-0 text-muted small">
                                        A strong password should be unique and not used in other websites or accounts.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top px-4 py-3 text-right">
                        <button type="submit" class="btn btn-primary px-5 shadow-sm">
                            <i class="fas fa-save mr-1"></i>
                            Change Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('css')
    <style>
        .password-alert {
            border-radius: 12px;
        }

        .password-side-card,
        .password-form-card {
            border-radius: 14px;
            overflow: hidden;
        }

        .password-side-card {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .security-icon {
            width: 110px;
            height: 110px;
            border-radius: 26px;
            background: linear-gradient(135deg, #007bff, #20c997);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 44px;
            box-shadow: 0 12px 30px rgba(0, 123, 255, .18);
        }

        .security-tips {
            border: 1px solid #edf0f5;
            border-radius: 12px;
            background: #ffffff;
            padding: 16px;
        }

        .security-tip-item {
            color: #495057;
            font-size: 14px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }

        .password-label {
            color: #6c757d;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .35px;
            margin-bottom: 6px;
        }

        .input-group-text {
            border-color: #e9ecef;
        }

        .form-control {
            border-color: #e9ecef;
        }

        .input-group .input-group-text {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .input-group .form-control {
            border-left: 0;
        }

        .input-group .btn {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .btn {
            border-radius: 8px;
            font-weight: 600;
        }

        .progress {
            border-radius: 20px;
            background-color: #edf0f5;
        }

        .progress-bar {
            transition: width .25s ease;
        }
    </style>
@endpush

@section('js')
    <script>
        $(document).ready(function () {
            $('.toggle-password').on('click', function () {
                let target = $($(this).data('target'));
                let icon = $(this).find('i');

                if (target.attr('type') === 'password') {
                    target.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    target.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            function calculateStrength(password) {
                let score = 0;

                if (password.length >= 8) {
                    score += 25;
                }

                if (/[A-Z]/.test(password)) {
                    score += 25;
                }

                if (/[0-9]/.test(password)) {
                    score += 25;
                }

                if (/[^A-Za-z0-9]/.test(password)) {
                    score += 25;
                }

                return score;
            }

            $('#password').on('keyup change', function () {
                let password = $(this).val();
                let score = calculateStrength(password);
                let bar = $('#password-strength-bar');
                let text = $('#password-strength-text');

                bar.css('width', score + '%')
                    .removeClass('bg-danger bg-warning bg-info bg-success');

                if (!password) {
                    text.removeClass('text-danger text-warning text-info text-success')
                        .addClass('text-muted')
                        .text('Password strength will appear here.');
                    bar.css('width', '0%');
                    return;
                }

                if (score <= 25) {
                    bar.addClass('bg-danger');
                    text.removeClass('text-muted text-warning text-info text-success')
                        .addClass('text-danger')
                        .text('Weak password');
                } else if (score <= 50) {
                    bar.addClass('bg-warning');
                    text.removeClass('text-muted text-danger text-info text-success')
                        .addClass('text-warning')
                        .text('Medium password');
                } else if (score <= 75) {
                    bar.addClass('bg-info');
                    text.removeClass('text-muted text-danger text-warning text-success')
                        .addClass('text-info')
                        .text('Good password');
                } else {
                    bar.addClass('bg-success');
                    text.removeClass('text-muted text-danger text-warning text-info')
                        .addClass('text-success')
                        .text('Strong password');
                }

                $('#password_confirmation').trigger('keyup');
            });

            $('#password_confirmation').on('keyup change', function () {
                let password = $('#password').val();
                let confirmation = $(this).val();
                let text = $('#password-match-text');

                if (!confirmation) {
                    text.removeClass('text-danger text-success')
                        .addClass('text-muted')
                        .text('Confirm password should match new password.');
                    return;
                }

                if (password === confirmation) {
                    text.removeClass('text-muted text-danger')
                        .addClass('text-success')
                        .html('<i class="fas fa-check-circle mr-1"></i> Password matched.');
                } else {
                    text.removeClass('text-muted text-success')
                        .addClass('text-danger')
                        .html('<i class="fas fa-times-circle mr-1"></i> Password does not match.');
                }
            });
        });
    </script>
@endsection
