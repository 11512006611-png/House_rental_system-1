@extends('layouts.admin')
@section('title','Manage Users')
@section('breadcrumb')
<li class="breadcrumb-item active">All Users</li>
@endsection

@section('content')

<div class="page-header">
    <h1><i class="fas fa-users me-2 text-primary"></i>User Management</h1>
    <p>View, activate, or deactivate user accounts.</p>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value">{{ $totalAll }}</div>
            <div class="stat-label">Total Users</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value text-success">{{ $totalActive }}</div>
            <div class="stat-label"><span class="chip chip-green">Active</span></div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value" style="color:#f59e0b">{{ $totalPendingCount }}</div>
            <div class="stat-label"><span class="chip chip-yellow">Pending</span></div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value text-danger">{{ $totalSusp }}</div>
            <div class="stat-label"><span class="chip chip-red">Suspended</span></div>
        </div></div>
    </div>
</div>

{{-- Filters --}}
<div class="admin-card mb-4">
    <div class="p-3">
        <form method="GET" action="{{ route('admin.users') }}" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Search name, email, phone..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="role" class="form-select form-select-sm">
                    <option value="">All Roles</option>
                    <option value="owner"  @selected(request('role')==='owner')>Owners</option>
                    <option value="tenant" @selected(request('role')==='tenant')>Tenants</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="approved"  @selected(request('status')==='approved')>Active</option>
                    <option value="pending"   @selected(request('status')==='pending')>Pending</option>
                    <option value="suspended" @selected(request('status')==='suspended')>Suspended</option>
                    <option value="rejected"  @selected(request('status')==='rejected')>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
            @if(request()->hasAny(['search','role','status']))
            <div class="col-md-2">
                <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="fas fa-times me-1"></i>Clear
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Table --}}
<div class="admin-card">
    <div class="admin-card-header">
        <h6><i class="fas fa-list me-2"></i>Users ({{ $users->total() }})</h6>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th class="text-center">Role</th>
                    <th class="text-center">Properties/Rentals</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Joined</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td class="text-muted small">{{ $loop->iteration }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="u-avatar" style="background:#fef9c3;color:#a16207;">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                            <span class="fw-600" style="font-size:.85rem;">{{ $u->name }}</span>
                        </div>
                    </td>
                    <td class="text-muted small">{{ $u->email }}</td>
                    <td class="text-muted small">{{ $u->phone ?? '—' }}</td>
                    <td class="text-center">
                        @if($u->role === 'owner')  <span class="chip chip-green">Owner</span>
                        @elseif($u->role === 'tenant') <span class="chip chip-orange">Tenant</span>
                        @else <span class="chip chip-purple">Admin</span>
                        @endif
                    </td>
                    <td class="text-center text-muted small">
                        @if($u->role === 'owner')
                            <i class="fas fa-building me-1"></i>{{ $u->houses_count ?? $u->houses()->count() }}
                        @elseif($u->role === 'tenant')
                            <i class="fas fa-file-contract me-1"></i>{{ $u->rentals_count ?? $u->rentals()->count() }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-center">
                        @php $s = $u->status; @endphp
                        @if($s === 'approved')   <span class="chip chip-green">Active</span>
                        @elseif($s === 'pending')<span class="chip chip-yellow">Pending</span>
                        @elseif($s === 'suspended')<span class="chip chip-red">Suspended</span>
                        @elseif($s === 'rejected')<span class="chip chip-gray">Rejected</span>
                        @else <span class="chip chip-gray">{{ $s }}</span>
                        @endif
                    </td>
                    <td class="text-center text-muted small">{{ $u->created_at->format('d M Y') }}</td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center flex-wrap">
                            @if($s === 'suspended')
                            <form action="{{ route('admin.users.activate', $u->id) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-outline-success"
                                    style="font-size:.72rem;padding:.2rem .5rem;"
                                    onclick="return confirm('Activate {{ addslashes($u->name) }}?')"
                                    title="Activate">
                                    <i class="fas fa-user-check me-1"></i>Activate
                                </button>
                            </form>
                            @elseif($s === 'approved')
                            <form action="{{ route('admin.users.deactivate', $u->id) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-outline-warning"
                                    style="font-size:.72rem;padding:.2rem .5rem;"
                                    onclick="return confirm('Suspend {{ addslashes($u->name) }}?')"
                                    title="Suspend">
                                    <i class="fas fa-user-slash me-1"></i>Suspend
                                </button>
                            </form>
                            @elseif($s === 'pending')
                            <form action="{{ route('admin.users.approve', $u->id) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-success"
                                    style="font-size:.72rem;padding:.2rem .5rem;"
                                    onclick="return confirm('Approve {{ addslashes($u->name) }}?')">
                                    <i class="fas fa-check me-1"></i>Approve
                                </button>
                            </form>
                            <form action="{{ route('admin.users.reject', $u->id) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-outline-danger"
                                    style="font-size:.72rem;padding:.2rem .5rem;"
                                    onclick="return confirm('Reject {{ addslashes($u->name) }}?')">
                                    <i class="fas fa-times me-1"></i>Reject
                                </button>
                            </form>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="fas fa-users fa-2x mb-2 d-block opacity-25"></i>
                        No users found matching filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-3 py-2 border-top">
        {{ $users->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection
