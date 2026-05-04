@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-home"></i>
            </div>
            <h4 class="auth-title">Reset Password</h4>
            <p class="auth-subtitle">Enter your new password</p>
        </div>

        <div class="auth-body">
            <form action="{{ route('password.update') }}" method="POST">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" class="form-control @error('email') is-invalid @enderror" placeholder="your@email.com" required autofocus>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Min. 8 characters" required>
                            <button type="button" class="input-group-text password-toggle" data-target="password" aria-label="Show password" aria-pressed="false" style="cursor: pointer;">
                                <i class="fas fa-eye text-muted"></i>
                            </button>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" placeholder="Repeat password" required>
                            <button type="button" class="input-group-text password-toggle" data-target="password_confirmation" aria-label="Show confirm password" aria-pressed="false" style="cursor: pointer;">
                                <i class="fas fa-eye text-muted"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-hrs-primary w-100 btn-lg">
                    <i class="fas fa-key me-2"></i> Reset Password
                </button>
            </form>

            <div class="auth-divider"><span>or</span></div>

            <p class="text-center mb-0 small">
                Remember your password?
                <a href="{{ route('login') }}" class="text-hrs-primary fw-semibold">Back to Login</a>
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleButtons = document.querySelectorAll('.password-toggle');

    toggleButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const targetName = button.getAttribute('data-target');
            const input = document.getElementById(targetName);
            if (!input) return;

            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';

            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', !show);
                icon.classList.toggle('fa-eye-slash', show);
            }

            button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            button.setAttribute('aria-pressed', show ? 'true' : 'false');
        });
    });
});
</script>

@endsection