@extends('layouts.admin')

@section('title', 'Pending Approvals')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Pending Approvals</li>
@endsection

@section('content')

    <div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
        <div>
            <h1><i class="fas fa-user-clock me-2 text-warning"></i>Pending Approvals</h1>
            <p>Review and approve or reject new user registrations.</p>
        </div>
        @if($pendingUsers->count() > 0)
            <span class="badge bg-warning text-dark fs-6 align-self-center">{{ $pendingUsers->count() }} Pending</span>
        @endif
    </div>

    <div class="admin-card">
        @if($pendingUsers->count() > 0)
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Registered</th>
                        <th style="width:160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingUsers as $i => $u)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="u-avatar" style="background:#fef9c3;color:#a16207;font-size:.8rem;">
                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                </div>
                                <span class="fw-semibold">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td>
                            <a href="mailto:{{ $u->email }}" class="text-muted text-decoration-none small">
                                {{ $u->email }}
                            </a>
                        </td>
                        <td>
                            @if($u->role === 'owner')
                                <span class="chip chip-green">
                                    <i class="fas fa-user-tie me-1"></i>Owner
                                </span>
                            @else
                                <span class="chip chip-orange">
                                    <i class="fas fa-user me-1"></i>Tenant
                                </span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $u->phone ?? '—' }}</td>
                        <td class="text-muted small">
                            {{ \Carbon\Carbon::parse($u->created_at)->format('d M Y, H:i') }}
                            <div class="text-muted" style="font-size:.7rem;">
                                {{ \Carbon\Carbon::parse($u->created_at)->diffForHumans() }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                <form action="{{ route('admin.users.approve', $u->id) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-check me-1"></i>Approve
                                    </button>
                                </form>
                                <form action="{{ route('admin.users.reject', $u->id) }}" method="POST" class="m-0"
                                      onsubmit="return confirm('Reject the account of {{ addslashes($u->name) }}? They will not be able to log in.')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-xmark me-1"></i>Reject
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="fas fa-circle-check fa-3x mb-3 text-success opacity-50"></i>
            <h5 class="fw-semibold">All caught up!</h5>
            <p class="mb-0 small">There are no users waiting for approval right now.</p>
        </div>
        @endif
    </div>

@endsection
