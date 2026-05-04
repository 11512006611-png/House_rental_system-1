@extends('layouts.app')

@section('title', 'Monthly Payment History')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h3 class="mb-1"><i class="fas fa-credit-card text-primary me-2"></i>Monthly Payment History</h3>
            <p class="text-muted mb-0">View all your monthly rent payments and their status.</p>
        </div>
        <a href="{{ route('tenant.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>

    {{-- Payment Statistics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="h4 mb-0 text-success">{{ $verifiedPayments }}</div>
                    <small class="text-muted">Verified Payments</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="h4 mb-0 text-warning">{{ $pendingPayments }}</div>
                    <small class="text-muted">Pending Verification</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="h4 mb-0 text-primary">Nu. {{ number_format($totalPaid, 0) }}</div>
                    <small class="text-muted">Total Paid</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="h4 mb-0 text-info">{{ $payments->total() }}</div>
                    <small class="text-muted">Total Records</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment History Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0"><i class="fas fa-history text-primary me-2"></i>Payment Records</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Month</th>
                        <th>Property</th>
                        <th>Payment Date & Time</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment Method</th>
                        <th>Transaction ID</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    {{ $payment->billingMonthLabel() }}
                                </div>
                                <span class="badge bg-light text-dark mt-1">{{ $payment->paymentTypeLabel() }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $payment->rental->house->title ?? 'Property' }}</div>
                                <small class="text-muted">{{ $payment->rental->house->owner->name ?? '' }}</small>
                            </td>
                            <td>
                                @if($payment->verification_status === 'verified')
                                    <div class="text-success fw-semibold">
                                        <i class="fas fa-check-circle me-1"></i>Verified on {{ optional($payment->verified_at ?? $payment->created_at)->format('d M Y') }}
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>{{ optional($payment->verified_at ?? $payment->created_at)->format('h:i A') }}
                                    </small>
                                @elseif($payment->verification_status === 'pending')
                                    <div class="text-warning fw-semibold">
                                        <i class="fas fa-hourglass-half me-1"></i>Submitted on {{ $payment->payment_date->format('d M Y') }}
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>{{ $payment->created_at->format('h:i A') }}
                                    </small>
                                @else
                                    <div class="text-muted">
                                        <i class="fas fa-times-circle me-1"></i>Not Paid
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-primary">Nu. {{ number_format($payment->amount, 0) }}</div>
                            </td>
                            <td>
                                @if($payment->verification_status === 'verified')
                                    <span class="badge bg-success">Verified</span>
                                @elseif($payment->verification_status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($payment->verification_status === 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($payment->verification_status ?? 'unknown') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($payment->payment_method)
                                    <span class="badge bg-light text-dark">{{ strtoupper($payment->payment_method) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($payment->transaction_id)
                                    <code class="small">{{ $payment->transaction_id }}</code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                                <div class="h5 mb-2">No Payment Records</div>
                                <p class="mb-0">You haven't made any monthly payments yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($payments->hasPages())
            <div class="card-footer bg-white">
                {{ $payments->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- Payment Status Legend --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Payment Status Legend</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2">Verified</span>
                                <small>Payment confirmed by admin</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning text-dark me-2">Pending</span>
                                <small>Awaiting admin verification</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-danger me-2">Failed</span>
                                <small>Payment was rejected</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-secondary me-2">Unknown</span>
                                <small>Status not available</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection