@extends('layouts.owner')

@section('title', 'My Tenants')

@section('breadcrumb')
    <li class="breadcrumb-item active">My Tenants</li>
@endsection

@section('content')

{{-- ── Header ────────────────────────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h1 class="mb-0" style="font-size:1.35rem;font-weight:800;color:#0f172a;">My Tenants</h1>
        <p class="text-muted mb-0" style="font-size:.82rem;">All rental agreements for your properties</p>
    </div>
</div>

{{-- ── Stat Chips ────────────────────────────────────────────────────────── --}}
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('owner.tenants') }}"
       class="chip {{ !request('status') ? 'chip-teal' : 'chip-gray' }} text-decoration-none" style="padding:.32rem .9rem;font-size:.75rem;">
        All
    </a>
    <a href="{{ route('owner.tenants') }}?status=active"
       class="chip {{ request('status') === 'active' ? 'chip-green' : 'chip-gray' }} text-decoration-none" style="padding:.32rem .9rem;font-size:.75rem;">
        Active <strong class="ms-1">{{ $totalActive }}</strong>
    </a>
    <a href="{{ route('owner.tenants') }}?status=pending"
       class="chip {{ request('status') === 'pending' ? 'chip-yellow' : 'chip-gray' }} text-decoration-none" style="padding:.32rem .9rem;font-size:.75rem;">
        Pending <strong class="ms-1">{{ $totalPending }}</strong>
    </a>
    <a href="{{ route('owner.tenants') }}?status=expired"
       class="chip {{ request('status') === 'expired' ? 'chip-red' : 'chip-gray' }} text-decoration-none" style="padding:.32rem .9rem;font-size:.75rem;">
        Expired <strong class="ms-1">{{ $totalExpired }}</strong>
    </a>
</div>

{{-- ── Search Bar ────────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('owner.tenants') }}" class="ob-card p-3 mb-3">
    @if(request('status'))
    <input type="hidden" name="status" value="{{ request('status') }}">
    @endif
    <div class="row g-2 align-items-end">
        <div class="col-12 col-sm">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Search by tenant name or email…" value="{{ request('search') }}"
                   style="border-radius:8px;font-size:.83rem;">
        </div>
        <div class="col-auto d-flex gap-2">
            <button type="submit" class="btn btn-sm"
                    style="background:var(--ob-accent);color:#fff;border-radius:8px;font-size:.82rem;">
                <i class="fas fa-magnifying-glass me-1"></i>Search
            </button>
            @if(request()->hasAny(['search','status']))
            <a href="{{ route('owner.tenants') }}" class="btn btn-sm btn-light" style="border-radius:8px;font-size:.82rem;">
                <i class="fas fa-xmark"></i>
            </a>
            @endif
        </div>
    </div>
</form>

{{-- ── Table ─────────────────────────────────────────────────────────────── --}}
<div class="ob-card">
    @if($rentals->isNotEmpty())
    <div class="table-responsive">
        <table class="table ob-table mb-0">
            <thead>
                <tr>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Monthly Rent</th>
                    <th>Lease Period</th>
                    <th>Payments</th>
                    <th>Status</th>
                    <th>Lease</th>
                    <th>Move-Out</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rentals as $rental)
                @php
                    $tenant  = $rental->tenant;
                    $house   = $rental->house;
                    $paidCnt    = $rental->payments->where('status', 'paid')->count();
                    $pendingCnt = $rental->payments->where('status', 'pending')->count();
                    $overdueCnt = $rental->payments->where('status', 'overdue')->count();
                    $paymentVerified = $rental->payments->where('verification_status', 'verified')->isNotEmpty();
                    $leaseAgreement = $rental->leaseAgreement;
                    $tenantSigned = (bool) optional($leaseAgreement)->tenant_signed_at;
                    $ownerSigned = (bool) optional($leaseAgreement)->owner_signed_at;
                    $bothSigned = $tenantSigned && $ownerSigned;
                    $latestMoveOut = $rental->moveOutRequests->sortByDesc('created_at')->first();
                    $statusMap  = [
                        'active'    => 'chip-green',
                        'pending'   => 'chip-yellow',
                        'expired'   => 'chip-gray',
                        'cancelled' => 'chip-red',
                    ];
                @endphp
                <tr>
                    {{-- Tenant --}}
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="ob-avatar" style="flex-shrink:0;">
                                {{ strtoupper(substr($tenant?->name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-600" style="font-size:.84rem;">{{ $tenant?->name ?? '—' }}</div>
                                <div style="font-size:.7rem;color:#94a3b8;">{{ $tenant?->email ?? '' }}</div>
                                @if($tenant?->phone)
                                <div style="font-size:.68rem;color:#94a3b8;"><i class="fas fa-phone me-1"></i>{{ $tenant->phone }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    {{-- Property --}}
                    <td>
                        <div class="fw-500" style="font-size:.83rem;">{{ $house?->title ?? '—' }}</div>
                        <div style="font-size:.7rem;color:#94a3b8;">{{ $house?->locationModel?->name ?? $house?->location ?? '' }}</div>
                    </td>
                    {{-- Monthly Rent --}}
                    <td class="fw-600" style="font-size:.84rem;">Nu {{ number_format($house?->price ?? 0) }}</td>
                    {{-- Lease Period --}}
                    <td style="font-size:.8rem;color:#475569;">
                        @if($rental->start_date)
                            <div>{{ $rental->start_date->format('d M Y') }}</div>
                            <div style="color:#94a3b8;">
                                {{ $rental->end_date ? '→ ' . $rental->end_date->format('d M Y') : 'Ongoing' }}
                            </div>
                        @else
                            —
                        @endif
                    </td>
                    {{-- Payments --}}
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            @if($paidCnt > 0)
                            <span class="chip chip-green">{{ $paidCnt }} paid</span>
                            @endif
                            @if($pendingCnt > 0)
                            <span class="chip chip-yellow">{{ $pendingCnt }} pending</span>
                            @endif
                            @if($overdueCnt > 0)
                            <span class="chip chip-red">{{ $overdueCnt }} overdue</span>
                            @endif
                            @if($paidCnt === 0 && $pendingCnt === 0 && $overdueCnt === 0)
                            <span class="chip chip-gray">No payments</span>
                            @endif
                        </div>
                    </td>
                    {{-- Status --}}
                    <td>
                        <span class="chip {{ $statusMap[$rental->status] ?? 'chip-gray' }}">
                            {{ ucfirst($rental->status) }}
                        </span>
                        {{-- Owner does not perform explicit accept/reject in this mode; only view tenant status --}}
                    </td>
                    <td>
                        @if($rental->lease_status === 'requested')
                            <div class="d-flex flex-column gap-2">
                                @if(!$leaseAgreement)
                                    <div class="small" style="background:#ecfeff;border:1px solid #bae6fd;border-radius:8px;padding:.45rem .6rem;color:#0f172a;">
                                        <strong>Required:</strong> Admin uploads lease agreement after tenant confirms stay.<br>
                                        2-month advance: <strong>Nu {{ number_format((float) $rental->monthly_rent * 2, 0) }}</strong>
                                    </div>
                                    <span class="chip chip-blue">Waiting for admin lease upload</span>
                                @else
                                    <a class="btn btn-sm btn-light w-100"
                                       href="{{ route('rentals.lease.download', $leaseAgreement) }}">View Agreement PDF</a>
                                @endif
                                @if($paymentVerified)
                                    <div class="small text-muted">
                                        Tenant signature: <strong>{{ $tenantSigned ? 'Completed' : 'Pending' }}</strong><br>
                                        Owner signature: <strong>{{ $ownerSigned ? 'Completed' : 'Pending' }}</strong>
                                    </div>
                                    @if(!$ownerSigned)
                                    <form method="POST" action="{{ route('owner.rentals.lease.approve', $rental) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success w-100">Approve Agreement</button>
                                    </form>
                                    @endif
                                @else
                                    <span class="chip chip-yellow">Waiting payment verification</span>
                                @endif
                            </div>
                        @elseif($bothSigned && $rental->lease_status === 'approved')
                            <span class="chip chip-green">Fully Signed</span>
                            @if($leaseAgreement)
                                <div class="mt-1">
                                    <a class="btn btn-sm btn-light" href="{{ route('rentals.lease.download', $leaseAgreement) }}">Agreement PDF</a>
                                </div>
                            @endif
                        @elseif($rental->lease_status === 'approved')
                            <span class="chip chip-blue">Approved (signature sync pending)</span>
                        @elseif($rental->lease_status === 'rejected')
                            <span class="chip chip-red">Rejected</span>
                        @else
                            <span class="chip chip-gray">Not requested</span>
                        @endif
                    </td>
                    <td>
                        @if($latestMoveOut)
                            @php
                                $moveChip = [
                                    'requested' => 'chip-yellow',
                                    'approved' => 'chip-blue',
                                    'completed' => 'chip-green',
                                    'rejected' => 'chip-red',
                                ][$latestMoveOut->status] ?? 'chip-gray';
                            @endphp
                            <span class="chip {{ $moveChip }}">{{ ucfirst($latestMoveOut->status) }}</span>
                            <div class="small text-muted mt-1" style="max-width:220px;">{{ Str::limit($latestMoveOut->reason, 70) }}</div>

                            @if($latestMoveOut->status === 'requested')
                                <div class="d-flex gap-1 flex-wrap mt-2">
                                    <form method="POST" action="{{ route('owner.moveouts.approve', $latestMoveOut) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('owner.moveouts.complete', $latestMoveOut) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Complete</button>
                                    </form>
                                    <form method="POST" action="{{ route('owner.moveouts.reject', $latestMoveOut) }}">
                                        @csrf
                                        <input type="hidden" name="owner_note" value="Move-out request rejected by owner.">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                    </form>
                                </div>
                            @elseif($latestMoveOut->status === 'approved')
                                <div class="d-flex gap-1 flex-wrap mt-2">
                                    <form method="POST" action="{{ route('owner.moveouts.complete', $latestMoveOut) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Mark Completed</button>
                                    </form>
                                </div>
                            @endif
                        @else
                            <span class="chip chip-gray">No request</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($rentals->hasPages())
    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top"
         style="font-size:.78rem;color:#64748b;background:#f8fafc;border-radius:0 0 14px 14px;">
        <span>Showing {{ $rentals->firstItem() }}–{{ $rentals->lastItem() }} of {{ $rentals->total() }}</span>
        {{ $rentals->links('pagination::bootstrap-5') }}
    </div>
    @endif

    @else
    <div class="text-center py-5" style="color:#94a3b8;">
        <i class="fas fa-users d-block mb-3" style="font-size:3rem;opacity:.2;"></i>
        <p class="mb-0" style="font-size:.9rem;font-weight:600;">No tenants found</p>
        <p style="font-size:.8rem;">
            @if(request()->hasAny(['search','status']))
                Try adjusting your search or filter.
            @else
                When tenants book your properties, they will appear here.
            @endif
        </p>
    </div>
    @endif
</div>

@endsection
