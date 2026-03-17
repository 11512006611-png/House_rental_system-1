@extends('layouts.admin')

@section('title', 'Tenants')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Tenants</li>
@endsection

@section('content')

    <div class="page-header">
        <h1><i class="fas fa-users me-2 text-warning"></i>Tenants</h1>
        <p>All registered tenants and their rental activity.</p>
    </div>

    <div class="admin-card">
        @if($tenants->count() > 0)
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Tenant</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th class="text-center">Rentals</th>
                        <th>Current Rental</th>
                        <th>Registered</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tenants as $i => $t)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="u-avatar" style="background:#fff7ed;color:#ea580c;">
                                    {{ strtoupper(substr($t->name, 0, 1)) }}
                                </div>
                                <span class="fw-semibold">{{ $t->name }}</span>
                            </div>
                        </td>
                        <td>
                            <a href="mailto:{{ $t->email }}" class="text-muted text-decoration-none small">
                                {{ $t->email }}
                            </a>
                        </td>
                        <td class="text-muted small">{{ $t->phone ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $t->rentals_count > 0 ? 'bg-primary' : 'bg-light text-dark border' }}">
                                {{ $t->rentals_count }}
                            </span>
                        </td>
                        <td class="small text-muted">
                            @if($t->rentals->first() && $t->rentals->first()->house)
                                <i class="fas fa-house me-1 text-primary"></i>
                                {{ Str::limit($t->rentals->first()->house->title, 30) }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ \Carbon\Carbon::parse($t->created_at)->format('d M Y') }}
                        </td>
                        <td class="text-center">
                            @if($t->status === 'approved')
                                <span class="chip chip-green">Active</span>
                            @elseif($t->status === 'pending')
                                <span class="chip chip-yellow">Pending</span>
                            @elseif($t->status === 'suspended')
                                <span class="chip chip-red">Suspended</span>
                            @else
                                <span class="chip chip-gray">Rejected</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($t->status === 'suspended')
                            <form action="{{ route('admin.users.activate', $t->id) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-outline-success"
                                    style="font-size:.72rem;padding:.2rem .5rem;"
                                    onclick="return confirm('Activate {{ addslashes($t->name) }}?')">
                                    <i class="fas fa-user-check me-1"></i>Activate
                                </button>
                            </form>
                            @elseif($t->status === 'approved')
                            <form action="{{ route('admin.users.deactivate', $t->id) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-outline-warning"
                                    style="font-size:.72rem;padding:.2rem .5rem;"
                                    onclick="return confirm('Suspend {{ addslashes($t->name) }}?')">
                                    <i class="fas fa-user-slash me-1"></i>Suspend
                                </button>
                            </form>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
            <h5 class="fw-semibold">No tenants registered yet.</h5>
        </div>
        @endif
    </div>

@endsection
