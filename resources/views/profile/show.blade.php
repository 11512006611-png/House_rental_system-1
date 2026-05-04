@extends('layouts.app')

@section('title', 'My Profile')

@push('styles')
<style>
.profile-shell {
    background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
    min-height: calc(100vh - 80px);
    padding: 2rem 0 3rem;
}
.profile-card {
    border: 0;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    overflow: hidden;
}
.profile-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #0ea5e9 100%);
    padding: 1.5rem;
    color: #fff;
}
.profile-avatar-lg {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid rgba(255, 255, 255, 0.45);
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.35);
}
.profile-avatar-fallback {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    border: 4px solid rgba(255, 255, 255, 0.45);
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
    font-size: 2rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.35);
}
.profile-item {
    border-bottom: 1px solid #eef2f7;
    padding: 0.95rem 0;
}
.profile-item:last-child {
    border-bottom: 0;
}
.profile-label {
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.profile-value {
    color: #0f172a;
    font-size: 1rem;
    font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="profile-shell">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="profile-card card">
                    <div class="profile-banner d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            @if($user->profile_image_url)
                                <img src="{{ $user->profile_image_url }}" alt="Profile image" class="profile-avatar-lg">
                            @else
                                <div class="profile-avatar-fallback">{{ strtoupper(substr($user->username ?: $user->name, 0, 1)) }}</div>
                            @endif
                            <div>
                                <p class="mb-1 opacity-75 small">My Account</p>
                                <h2 class="h4 mb-1">{{ $user->username ?: $user->name }}</h2>
                                <p class="mb-0 opacity-75">{{ ucfirst($user->role) }}</p>
                            </div>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="btn btn-hrs-primary px-4">
                            <i class="fas fa-pen-to-square me-2"></i>Edit Profile
                        </a>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <div class="profile-item row">
                            <div class="col-md-4 profile-label">Username</div>
                            <div class="col-md-8 profile-value">{{ $user->username ?: '-' }}</div>
                        </div>
                        <div class="profile-item row">
                            <div class="col-md-4 profile-label">Email</div>
                            <div class="col-md-8 profile-value">{{ $user->email }}</div>
                        </div>
                        <div class="profile-item row">
                            <div class="col-md-4 profile-label">Phone</div>
                            <div class="col-md-8 profile-value">{{ $user->phone ?: '-' }}</div>
                        </div>
                        <div class="profile-item row">
                            <div class="col-md-4 profile-label">Date of Birth</div>
                            <div class="col-md-8 profile-value">{{ $user->date_of_birth ? $user->date_of_birth->format('d M Y') : '-' }}</div>
                        </div>
                        <div class="profile-item row">
                            <div class="col-md-4 profile-label">Role</div>
                            <div class="col-md-8 profile-value">{{ ucfirst($user->role) }}</div>
                        </div>
                        <div class="profile-item row">
                            <div class="col-md-4 profile-label">Joined</div>
                            <div class="col-md-8 profile-value">{{ $user->created_at?->format('d M Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
