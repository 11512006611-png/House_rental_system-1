@extends('layouts.admin')
@section('title','Transaction History')
@section('breadcrumb')
<li class="breadcrumb-item active">Transactions</li>
@endsection

@section('content')

<div class="page-header">
    <h1><i class="fas fa-money-bill-transfer me-2 text-success"></i>Transaction History</h1>
    <p>Track all rental payments, revenue, and admin commissions.</p>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card h-100"><div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <div class="stat-icon" style="background:#eff6ff;"><i class="fas fa-sack-dollar" style="color:#3b82f6;"></i></div>
                <span class="chip chip-blue">Revenue</span>
            </div>
            <div class="stat-value mt-2">Nu. {{ number_format($totalRevenue, 0) }}</div>
            <div class="stat-label">Total Rental Revenue</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card h-100"><div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <div class="stat-icon" style="background:#faf5ff;"><i class="fas fa-percent" style="color:#9333ea;"></i></div>
                <span class="chip chip-purple">Commission</span>
            </div>
            <div class="stat-value mt-2" style="color:#9333ea;">Nu. {{ number_format($totalCommission, 0) }}</div>
            <div class="stat-label">Platform Commission Earned</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card h-100"><div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <div class="stat-icon" style="background:#f0fdf4;"><i class="fas fa-receipt" style="color:#16a34a;"></i></div>
                <span class="chip chip-green">Count</span>
            </div>
            <div class="stat-value mt-2">{{ $totalPayments }}</div>
            <div class="stat-label">Total Transactions</div>
        </div></div>
    </div>
</div>

{{-- Filters --}}
<div class="admin-card mb-4">
    <div class="p-3">
        <form method="GET" action="{{ route('admin.transactions') }}" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Search tenant, property..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="paid"    @selected(request('status')==='paid')>Paid</option>
                    <option value="pending" @selected(request('status')==='pending')>Pending</option>
                    <option value="overdue" @selected(request('status')==='overdue')>Overdue</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="month" name="month" class="form-control form-control-sm"
                    value="{{ request('month') }}" title="Filter by month">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
            @if(request()->hasAny(['search','status','month']))
            <div class="col-md-2">
                <a href="{{ route('admin.transactions') }}" class="btn btn-sm btn-outline-secondary w-100">
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
        <h6><i class="fas fa-list me-2"></i>Payments ({{ $payments->total() }})</h6>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Owner</th>
                    <th class="text-end">Amount</th>
                    <th class="text-center">Rate</th>
                    <th class="text-end">Commission</th>
                    <th class="text-end">Owner Share</th>
                    <th class="text-center">Proof</th>
                    <th class="text-center">Date</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Verification</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                @php
                    $house = $p->rental->house ?? null;
                    $owner = $house->owner ?? null;
                @endphp
                <tr>
                    <td class="text-muted small">{{ ($payments->currentPage() - 1) * $payments->perPage() + $loop->iteration }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="u-avatar" style="background:#fff7ed;color:#ea580c;">{{ strtoupper(substr($p->tenant->name ?? '?', 0, 1)) }}</div>
                            <span style="font-size:.84rem;">{{ $p->tenant->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="text-muted small">{{ Str::limit($house->title ?? '—', 28) }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="u-avatar" style="background:#dbeafe;color:#2563eb;">{{ strtoupper(substr($owner->name ?? '?', 0, 1)) }}</div>
                            <span style="font-size:.84rem;">{{ $owner->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="text-end fw-600" style="font-size:.85rem;">Nu. {{ number_format($p->amount, 0) }}</td>
                    <td class="text-center">
                        <span class="chip chip-purple">{{ $p->commission_rate ?? 0 }}%</span>
                    </td>
                    <td class="text-end fw-600" style="color:#9333ea;font-size:.85rem;">
                        Nu. {{ number_format($p->commission_amount ?? 0, 0) }}
                    </td>
                    <td class="text-end fw-600" style="color:#2563eb;font-size:.85rem;">
                        Nu. {{ number_format($p->owner_share_amount ?? 0, 0) }}
                    </td>
                    <td class="text-center small">
                        @if($p->payment_proof_path)
                            <a href="{{ asset('storage/' . $p->payment_proof_path) }}" target="_blank" class="btn btn-sm btn-light">File</a>
                        @elseif($p->transaction_id)
                            {{ $p->transaction_id }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-center text-muted small">
                        {{ $p->payment_date ? $p->payment_date->format('d M Y') : '—' }}
                    </td>
                    <td class="text-center">
                        @if($p->status === 'paid')    <span class="chip chip-green">Paid</span>
                        @elseif($p->status === 'pending') <span class="chip chip-yellow">Pending</span>
                        @else                          <span class="chip chip-red">Overdue</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($p->verification_status === 'verified')
                            <span class="chip chip-green">Verified</span>
                        @elseif($p->verification_status === 'rejected')
                            <span class="chip chip-red">Rejected</span>
                        @else
                            <div class="d-flex gap-1 justify-content-center flex-wrap">
                                <form method="POST" action="{{ route('admin.transactions.verify', $p) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Verify</button>
                                </form>
                                <form method="POST" action="{{ route('admin.transactions.reject', $p) }}">
                                    @csrf
                                    <input type="hidden" name="rejection_note" value="Payment proof could not be verified.">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center text-muted py-5">
                        <i class="fas fa-receipt fa-2x mb-2 d-block opacity-25"></i>
                        No transactions found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
    <div class="px-3 py-2 border-top">
        {{ $payments->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection
