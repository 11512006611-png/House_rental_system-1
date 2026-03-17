@extends('layouts.owner')

@section('title', 'Payments')

@section('breadcrumb')
    <li class="breadcrumb-item active">Payments</li>
@endsection

@section('content')

{{-- ── Header ────────────────────────────────────────────────────────────── --}}
<div class="mb-3">
    <h1 class="mb-0" style="font-size:1.35rem;font-weight:800;color:#0f172a;">Payments</h1>
    <p class="text-muted mb-0" style="font-size:.82rem;">All payment records for your rental properties</p>
</div>

{{-- ── Summary Cards ─────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-12 col-sm-4">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#dcfce7;flex-shrink:0;">
                <i class="fas fa-circle-check" style="color:#15803d;"></i>
            </div>
            <div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value" style="color:#15803d;font-size:1.35rem;">Nu {{ number_format($totalRevenue) }}</div>
                <div class="stat-footer">{{ $totalCount }} payments total</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#fef9c3;flex-shrink:0;">
                <i class="fas fa-clock" style="color:#a16207;"></i>
            </div>
            <div>
                <div class="stat-label">Pending</div>
                <div class="stat-value" style="color:#a16207;font-size:1.35rem;">Nu {{ number_format($totalPending) }}</div>
                <div class="stat-footer">Awaiting payment</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#fee2e2;flex-shrink:0;">
                <i class="fas fa-triangle-exclamation" style="color:#b91c1c;"></i>
            </div>
            <div>
                <div class="stat-label">Overdue</div>
                <div class="stat-value" style="color:#b91c1c;font-size:1.35rem;">Nu {{ number_format($totalOverdue) }}</div>
                <div class="stat-footer">Past due date</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Filter Bar ────────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('owner.payments') }}" class="ob-card p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-sm">
            <label class="form-label mb-1" style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Tenant</label>
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Search by tenant name…" value="{{ request('search') }}"
                   style="border-radius:8px;font-size:.83rem;">
        </div>
        <div class="col-auto">
            <label class="form-label mb-1" style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Status</label>
            <select name="status" class="form-select form-select-sm" style="border-radius:8px;font-size:.83rem;">
                <option value="">All</option>
                <option value="paid"    {{ request('status') === 'paid'    ? 'selected' : '' }}>Paid</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label mb-1" style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Month</label>
            <input type="month" name="month" class="form-control form-control-sm"
                   value="{{ request('month') }}" style="border-radius:8px;font-size:.83rem;">
        </div>
        <div class="col-auto d-flex gap-2 align-self-end">
            <button type="submit" class="btn btn-sm"
                    style="background:var(--ob-accent);color:#fff;border-radius:8px;font-size:.82rem;">
                <i class="fas fa-magnifying-glass me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','status','month']))
            <a href="{{ route('owner.payments') }}" class="btn btn-sm btn-light" style="border-radius:8px;font-size:.82rem;">
                <i class="fas fa-xmark"></i>
            </a>
            @endif
        </div>
    </div>
</form>

{{-- ── Table ─────────────────────────────────────────────────────────────── --}}
<div class="ob-card">
    @if($payments->isNotEmpty())
    <div class="table-responsive">
        <table class="table ob-table mb-0">
            <thead>
                <tr>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Amount</th>
                    <th>Owner Share</th>
                    <th>Payment Date</th>
                    <th>Proof</th>
                    <th>Status</th>
                    <th>Verification</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $pay)
                @php
                    $statusColors = [
                        'paid'    => 'chip-green',
                        'pending' => 'chip-yellow',
                        'overdue' => 'chip-red',
                    ];
                @endphp
                <tr>
                    {{-- Tenant --}}
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="ob-avatar" style="width:28px;height:28px;font-size:.68rem;flex-shrink:0;">
                                {{ strtoupper(substr($pay->tenant?->name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-600" style="font-size:.83rem;">{{ $pay->tenant?->name ?? '—' }}</div>
                                <div style="font-size:.69rem;color:#94a3b8;">{{ $pay->tenant?->email ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    {{-- Property --}}
                    <td style="font-size:.82rem;">
                        <div class="fw-500">{{ $pay->rental?->house?->title ?? '—' }}</div>
                    </td>
                    {{-- Amount --}}
                    <td class="fw-700" style="font-size:.88rem;color:#0f172a;">
                        Nu {{ number_format($pay->amount) }}
                    </td>
                    <td class="fw-600" style="font-size:.82rem;color:#2563eb;">
                        Nu {{ number_format((float)($pay->owner_share_amount ?? 0), 0) }}
                    </td>
                    {{-- Payment Date --}}
                    <td style="font-size:.82rem;color:#475569;">
                        {{ optional($pay->payment_date)->format('d M Y') ?? '—' }}
                    </td>
                    {{-- Proof --}}
                    <td style="font-size:.8rem;color:#64748b;">
                        @if($pay->payment_proof_path)
                            <a href="{{ asset('storage/' . $pay->payment_proof_path) }}" target="_blank" class="btn btn-sm btn-light">View</a>
                        @elseif($pay->transaction_id)
                            <span class="small">{{ $pay->transaction_id }}</span>
                        @else
                            —
                        @endif
                    </td>
                    {{-- Status --}}
                    <td>
                        <span class="chip {{ $statusColors[$pay->status] ?? 'chip-gray' }}">
                            {{ ucfirst($pay->status) }}
                        </span>
                    </td>
                    <td>
                        @if($pay->verification_status === 'verified')
                            <span class="chip chip-green">Verified</span>
                        @elseif($pay->verification_status === 'rejected')
                            <span class="chip chip-red">Rejected</span>
                        @else
                            <span class="chip chip-yellow">Pending</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($payments->hasPages())
    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top"
         style="font-size:.78rem;color:#64748b;background:#f8fafc;border-radius:0 0 14px 14px;">
        <span>Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }}</span>
        {{ $payments->links('pagination::bootstrap-5') }}
    </div>
    @endif

    @else
    <div class="text-center py-5" style="color:#94a3b8;">
        <i class="fas fa-money-bill-wave d-block mb-3" style="font-size:3rem;opacity:.2;"></i>
        <p class="mb-0" style="font-size:.9rem;font-weight:600;">No payments found</p>
        <p style="font-size:.8rem;">
            @if(request()->hasAny(['search','status','month']))
                Try adjusting your filters.
            @else
                Payment records will appear here once tenants make payments.
            @endif
        </p>
    </div>
    @endif
</div>

@endsection
