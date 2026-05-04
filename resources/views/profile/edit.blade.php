@extends('layouts.app')

@section('title', 'Edit Profile')

@push('styles')
<style>
.profile-edit-shell {
    background: linear-gradient(140deg, #f8fafc 0%, #ecfeff 100%);
    min-height: calc(100vh - 80px);
    padding: 2rem 0 3rem;
}
.profile-edit-card {
    border: 0;
    border-radius: 16px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
}
.avatar-preview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e2e8f0;
}
.avatar-fallback-preview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1e3a5f, #0ea5e9);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.7rem;
    font-weight: 800;
    border: 3px solid #e2e8f0;
}
.section-title {
    font-size: 0.9rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #475569;
}
</style>
@endpush

@section('content')
<div class="profile-edit-shell">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card profile-edit-card">
                    <div class="card-header bg-white border-0 pt-4 px-4 px-md-5">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div>
                                <h2 class="h4 mb-1">Edit Profile</h2>
                                <p class="text-muted mb-0">Update your profile details, photo, and password.</p>
                            </div>
                            <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Profile
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="card-body px-4 px-md-5 pb-5">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <p class="section-title mb-3">Profile Photo</p>
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                @php
                                    $avatarInitial = strtoupper(substr($user->username ?: $user->name, 0, 1));
                                @endphp
                                <img
                                    id="profileImagePreview"
                                    src="{{ $user->profile_image_url ?: '' }}"
                                    alt="Profile image"
                                    class="avatar-preview {{ $user->profile_image_url ? '' : 'd-none' }}"
                                >
                                <div
                                    id="profileImageFallback"
                                    class="avatar-fallback-preview {{ $user->profile_image_url ? 'd-none' : '' }}"
                                >{{ $avatarInitial }}</div>
                                <div class="flex-grow-1">
                                    <input id="profileImageInput" type="file" name="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/png,image/jpeg,image/webp">
                                    <div class="form-text">Optional. JPG, PNG, or WEBP up to 2MB.</div>
                                    @error('profile_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Username</label>
                                <input type="text" name="username" value="{{ old('username', $user->username) }}" class="form-control @error('username') is-invalid @enderror" required>
                                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control @error('phone') is-invalid @enderror" placeholder="+975 17xxxxxx">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date of Birth</label>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}" class="form-control @error('date_of_birth') is-invalid @enderror">
                                @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role</label>
                                <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" disabled>
                            </div>
                        </div>

                        <hr class="my-4">

                        <p class="section-title mb-3">Change Password (Optional)</p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Current Password</label>
                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror">
                                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">New Password</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                            <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-hrs-primary px-4">
                                <i class="fas fa-floppy-disk me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('profileImageInput');
    const preview = document.getElementById('profileImagePreview');
    const fallback = document.getElementById('profileImageFallback');

    if (!input || !preview || !fallback) {
        return;
    }

    input.addEventListener('change', function (event) {
        const file = event.target.files && event.target.files[0];

        if (!file) {
            return;
        }

        const reader = new FileReader();

        reader.onload = function (loadEvent) {
            preview.src = loadEvent.target.result;
            preview.style.display = 'block';
            fallback.style.display = 'none';
        };

        reader.readAsDataURL(file);
    });
});
</script>
@endpush
