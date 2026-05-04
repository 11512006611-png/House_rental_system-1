@extends('layouts.admin')

@section('title', 'Reports')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div class="page-header mb-0">
        <h1><i class="fas fa-chart-bar me-2 text-primary"></i>Reports</h1>
        <p class="mb-0">Performance summary from {{ $startDate->format('d M Y') }} to {{ now()->format('d M Y') }}</p>
    </div>

    <form method="GET" action="{{ route('admin.reports') }}" class="d-flex align-items-center gap-2">
        <label for="months" class="form-label mb-0 small text-muted">Range</label>
        <select id="months" name="months" class="form-select form-select-sm" style="min-width: 130px;">
            @foreach([3, 6, 12, 24] as $m)
                <option value="{{ $m }}" {{ (int) $monthsBack === $m ? 'selected' : '' }}>Last {{ $m }} months</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sm btn-primary">Apply</button>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body p-3">
                <div class="small text-muted">Revenue</div>
                <div class="h5 mb-0">Nu. {{ number_format($summary['total_revenue'], 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body p-3">
                <div class="small text-muted">Commission</div>
                <div class="h5 mb-0">Nu. {{ number_format($summary['total_commission'], 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body p-3">
                <div class="small text-muted">Paid Transactions</div>
                <div class="h5 mb-0">{{ number_format($summary['paid_transactions']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body p-3">
                <div class="small text-muted">Verified Payments</div>
                <div class="h5 mb-0 text-success">{{ number_format($summary['verified_payments']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body p-3">
                <div class="small text-muted">Pending Verification</div>
                <div class="h5 mb-0 text-warning">{{ number_format($summary['pending_verifications']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body p-3">
                <div class="small text-muted">Pending Refunds</div>
                <div class="h5 mb-0 text-danger">{{ number_format($summary['pending_refunds']) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="mb-0"><i class="fas fa-calendar-days me-2 text-primary"></i>Monthly Report Table</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Month</th>
                    <th>Revenue</th>
                    <th>Commission</th>
                    <th>Verified</th>
                    <th>Pending</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monthlyRows as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $row->ym)->format('M Y') }}</td>
                        <td>Nu. {{ number_format((float) $row->revenue, 0) }}</td>
                        <td>Nu. {{ number_format((float) $row->commission, 0) }}</td>
                        <td><span class="badge bg-success">{{ (int) $row->verified_count }}</span></td>
                        <td><span class="badge bg-warning text-dark">{{ (int) $row->pending_count }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No report data found for selected range.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="mb-0"><i class="fas fa-receipt me-2 text-primary"></i>Recent Payment Records</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Month</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentPayments as $payment)
                    <tr>
                        <td>{{ optional($payment->payment_date)->format('d M Y') }}</td>
                        <td>{{ $payment->tenant->name ?? 'Tenant' }}</td>
                        <td>{{ $payment->rental->house->title ?? 'Property' }}</td>
                        <td>{{ $payment->billingMonthLabel() }}</td>
                        <td>Nu. {{ number_format((float) $payment->amount, 0) }}</td>
                        <td>
                            @if($payment->verification_status === 'verified')
                                <span class="badge bg-success">Verified</span>
                            @elseif($payment->verification_status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($payment->verification_status === 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst((string) $payment->verification_status) }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No recent payments found for selected range.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
