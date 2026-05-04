@extends('layouts.admin')

@section('title', 'User Profile')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users') }}">Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">Profile</li>
@endsection

@section('content')

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1><i class="fas fa-id-card me-2 text-primary"></i>User Profile</h1>
        <p>Latest profile data submitted by this {{ $user->role }}.</p>
    </div>
    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="admin-card p-4 text-center h-100">
            @if($user->profile_image_url)
                <img src="{{ $user->profile_image_url }}" alt="{{ $user->name }}"
                     style="width:110px;height:110px;object-fit:cover;border-radius:50%;border:3px solid #e2e8f0;">
            @else
                <div class="u-avatar mx-auto" style="width:110px;height:110px;font-size:2rem;background:#eff6ff;color:#1d4ed8;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif

            <h5 class="mt-3 mb-1">{{ $user->name }}</h5>
            @if($user->username)
                <div class="text-muted mb-2">@{{ $user->username }}</div>
            @endif

            <div class="d-flex justify-content-center gap-2 flex-wrap mt-2">
                @if($user->role === 'tenant')
                    <span class="chip chip-orange">Tenant</span>
                @else
                    <span class="chip chip-green">Owner</span>
                @endif

                @if($user->status === 'approved')
                    <span class="chip chip-green">Active</span>
                @elseif($user->status === 'pending')
                    <span class="chip chip-yellow">Pending</span>
                @elseif($user->status === 'suspended')
                    <span class="chip chip-red">Suspended</span>
                @else
                    <span class="chip chip-gray">Rejected</span>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="admin-card p-0 overflow-hidden">
            <div class="admin-card-header">
                <h6><i class="fas fa-user-pen me-2"></i>Profile Details</h6>
            </div>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <tbody>
                        <tr>
                            <th style="width:34%;">Full Name</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>
                                <a href="mailto:{{ $user->email }}" class="text-decoration-none">{{ $user->email }}</a>
                            </td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>{{ $user->phone ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Date of Birth</th>
                            <td>{{ $user->date_of_birth ? $user->date_of_birth->format('d M Y') : '—' }}</td>
                        </tr>
                        <tr>
                            <th>Current Address</th>
                            <td>{{ $user->current_address ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Joined</th>
                            <td>{{ $user->created_at?->format('d M Y, h:i A') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td>{{ $user->updated_at?->format('d M Y, h:i A') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Total Properties / Rentals</th>
                            <td>
                                @if($user->role === 'owner')
                                    {{ $user->properties_count ?? 0 }} properties
                                @else
                                    {{ $user->rentals_count ?? 0 }} rentals
                                @endif
                            </td>
                        </tr>
                        @if($user->role === 'tenant')
                        <tr>
                            <th>Latest Rental</th>
                            <td>
                                @if($latestRental && $latestRental->house)
                                    {{ $latestRental->house->title }}
                                    <span class="text-muted">({{ ucfirst($latestRental->status) }})</span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
