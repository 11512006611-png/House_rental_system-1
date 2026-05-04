@extends('layouts.app')

@section('title', 'Register')

@section('content')

<div class="auth-wrapper">
    <div class="auth-card auth-card-wide">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-user-plus"></i>
            </div>
            <h4 class="auth-title">Create an Account</h4>
            <p class="auth-subtitle">Join HRS Bhutan — find or list houses across the Kingdom</p>
        </div>

        <div class="auth-body">
            <form action="{{ route('register') }}" method="POST">
                @csrf

                @if($errors->any())
                <div class="alert alert-danger small" role="alert">
                    <strong>Registration could not be completed.</strong>
                    Please correct the highlighted fields and try again.
                </div>
                @endif

                @if($errors->has('email') || $errors->has('phone') || $errors->has('username'))
                <div class="alert alert-warning small" role="alert">
                    <strong>Account already exists.</strong>
                    It looks like these details are already registered. Please <a href="{{ route('login') }}" class="alert-link">log in here</a>.
                </div>
                @endif

                <div class="alert alert-info small" role="alert">
                    Admin accounts are created manually by the system administrator for security.
                </div>

                <!-- Role Selection -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">I am a... <span class="text-danger">*</span></label>
                    <div class="role-selector">
                        <input type="radio" name="role" id="role_tenant" value="tenant"
                               class="role-radio" {{ old('role', 'tenant') == 'tenant' ? 'checked' : '' }} required>
                        <label for="role_tenant" class="role-option">
                            <i class="fas fa-search-location"></i>
                            <span class="role-name">Tenant</span>
                            <span class="role-desc">Looking for a house</span>
                        </label>

                        <input type="radio" name="role" id="role_owner" value="owner"
                               class="role-radio" {{ old('role') == 'owner' ? 'checked' : '' }}>
                        <label for="role_owner" class="role-option">
                            <i class="fas fa-home"></i>
                            <span class="role-name">Owner</span>
                            <span class="role-desc">Listing houses for rent</span>
                        </label>
                    </div>
                    @error('role')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Your full name"
                                   value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <small class="d-block text-muted mb-1" style="font-size:.8rem;">Enter 8 digits (example: 17123456).</small>
                        <div class="input-group">
                            <span class="input-group-text">+975</span>
                            <input type="text" name="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   placeholder="17xxxxxx"
                                   value="{{ old('phone') }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                        <small class="d-block text-muted mb-1" style="font-size:.8rem;">Select your date of birth (must be 18+).</small>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-day text-muted"></i></span>
                            <input type="date" name="date_of_birth" id="date_of_birth"
                                   class="form-control @error('date_of_birth') is-invalid @enderror"
                                   value="{{ old('date_of_birth') }}" required>
                            @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="mt-3" id="currentAddressGroup">
                    <label class="form-label fw-semibold">Current Address <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-map-marker-alt text-muted"></i></span>
                        <textarea name="current_address" id="currentAddressInput"
                                  class="form-control @error('current_address') is-invalid @enderror"
                                  rows="2" maxlength="500"
                                  placeholder="Enter your current full address">{{ old('current_address') }}</textarea>
                        @error('current_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <small class="text-muted">Required for owners, hidden for tenants.</small>
                </div>

                <div class="mt-3 mb-3">
                    <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="your@email.com"
                               value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min. 8 characters" required>
                            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="password" aria-label="Show or hide password">
                                <i class="fas fa-eye"></i>
                            </button>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="small text-danger mt-1 d-none" id="passwordMismatchMessage">
                            Password confirmation does not match. Please enter the same password.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" name="password_confirmation"
                                   class="form-control @if($errors->has('password')) is-invalid @endif"
                                   placeholder="Repeat password" required>
                            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="password_confirmation" aria-label="Show or hide confirm password">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if($errors->has('password'))<div class="invalid-feedback">{{ $errors->first('password') }}</div>@endif
                        </div>
                    </div>
                </div>

                <div class="mt-4 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="agree" required>
                        <label class="form-check-label small" for="agree">
                            I agree to the <a href="#" class="text-hrs-primary">Terms of Service</a>
                            and <a href="#" class="text-hrs-primary">Privacy Policy</a>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-hrs-primary w-100 btn-lg">
                    <i class="fas fa-user-plus me-2"></i> Create Account
                </button>
            </form>

            <div class="auth-divider"><span>or</span></div>

            <p class="text-center mb-0 small">
                Already have an account?
                <a href="{{ route('login') }}" class="text-hrs-primary fw-semibold">Sign in here</a>
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleInputs = document.querySelectorAll('input[name="role"]');
    const addressGroup = document.getElementById('currentAddressGroup');
    const addressInput = document.getElementById('currentAddressInput');

    function toggleAddressField() {
        const selectedRole = document.querySelector('input[name="role"]:checked')?.value || 'tenant';
        const shouldShowAddress = selectedRole !== 'tenant';

        if (shouldShowAddress) {
            addressGroup.classList.remove('d-none');
            addressInput.setAttribute('required', 'required');
        } else {
            addressGroup.classList.add('d-none');
            addressInput.removeAttribute('required');
            addressInput.value = '';
        }
    }

    roleInputs.forEach(function (input) {
        input.addEventListener('change', toggleAddressField);
    });

    toggleAddressField();

    const registerForm = document.querySelector('form[action="{{ route("register") }}"]');
    const dobInput = document.getElementById('date_of_birth');
    const passwordInput = document.querySelector('input[name="password"]');
    const confirmPasswordInput = document.querySelector('input[name="password_confirmation"]');
    const passwordMismatchMessage = document.getElementById('passwordMismatchMessage');

    function validatePasswordMatch(showMessage) {
        if (!passwordInput || !confirmPasswordInput) return true;

        const hasValue = passwordInput.value.length > 0 || confirmPasswordInput.value.length > 0;
        const isMatch = passwordInput.value === confirmPasswordInput.value;
        const shouldShow = showMessage && hasValue && !isMatch;

        confirmPasswordInput.classList.toggle('is-invalid', shouldShow);
        if (passwordMismatchMessage) {
            passwordMismatchMessage.classList.toggle('d-none', !shouldShow);
        }

        return isMatch;
    }

    if (registerForm && dobInput) {
        registerForm.addEventListener('submit', function (event) {
            if (!validatePasswordMatch(true)) {
                event.preventDefault();
                confirmPasswordInput.focus();
                return false;
            }

            const dobValue = dobInput.value;
            if (!dobValue) return;

            const dob = new Date(dobValue);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            const m = today.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                age -= 1;
            }

            if (age < 18) {
                event.preventDefault();
                alert('You must be 18 years or older to register. Please select your correct date of birth.');
                dobInput.focus();
                return false;
            }
        });
    }

    if (passwordInput && confirmPasswordInput) {
        passwordInput.addEventListener('input', function () {
            validatePasswordMatch(false);
        });

        confirmPasswordInput.addEventListener('input', function () {
            validatePasswordMatch(true);
        });
    }

    const toggleButtons = document.querySelectorAll('.password-toggle');
    toggleButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const targetName = button.getAttribute('data-target');
            const input = document.querySelector('input[name="' + targetName + '"]');
            if (!input) return;

            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';

            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', !show);
                icon.classList.toggle('fa-eye-slash', show);
            }
        });
    });
});
</script>

@endsection
