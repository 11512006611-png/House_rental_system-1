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
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" name="password_confirmation"
                                   class="form-control"
                                   placeholder="Repeat password" required>
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

@endsection
