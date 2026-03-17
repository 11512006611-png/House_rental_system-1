@extends('layouts.app')

@section('title', 'Login')

@section('content')

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-home"></i>
            </div>
            <h4 class="auth-title">Welcome Back</h4>
            <p class="auth-subtitle">Sign in to your HRS Bhutan account</p>
        </div>

        <div class="auth-body">
            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="role" class="form-label fw-semibold">Role</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user-tag text-muted"></i></span>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">Select your role</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="owner" {{ old('role') === 'owner' ? 'selected' : '' }}>Owner</option>
                            <option value="tenant" {{ old('role') === 'tenant' ? 'selected' : '' }}>Tenant</option>
                        </select>
                        @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="your@email.com"
                               value="{{ old('email') }}" required autofocus>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label class="form-label fw-semibold">Password</label>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Enter your password" required>
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small" for="remember">Remember me</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-hrs-primary w-100 btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i> Sign In
                </button>

                @if($errors->has('account_not_found'))
                <div class="alert alert-warning mt-3 mb-0" role="alert">
                    <div class="fw-semibold mb-2">{{ $errors->first('account_not_found') }}</div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('register') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user-plus me-1"></i> Register Now
                        </a>
                        <button type="submit"
                                formmethod="GET"
                                formaction="{{ route('auth.google.redirect') }}"
                                class="btn btn-outline-dark btn-sm">
                            <i class="fab fa-google me-1"></i> Continue with Google
                        </button>
                    </div>
                </div>
                @endif
            </form>

            <div class="auth-divider"><span>or</span></div>

            <p class="text-center mb-0 small">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-hrs-primary fw-semibold">Create one free</a>
            </p>
        </div>
    </div>
</div>

@endsection
