@extends('layouts.app')

@section('title', 'Monthly Earnings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-chart-line text-success"></i>
                        Monthly Earnings Overview
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Current Month Summary -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Total Rent Collected</h5>
                                    <h3>${{ number_format($currentMonthData['total_rent_collected'], 2) }}</h3>
                                    <small>This Month</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Commission Deducted</h5>
                                    <h3>${{ number_format($currentMonthData['commission_amount'], 2) }}</h3>
                                    <small>({{ number_format($currentMonthData['commission_rate'], 1) }}%)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Net Earnings</h5>
                                    <h3>${{ number_format($currentMonthData['net_amount'], 2) }}</h3>
                                    <small>This Month</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Settlement Status</h5>
                                    <h4>
                                        @if($currentMonthData['settlement_status'] === 'transferred')
                                            <span class="badge bg-success">Transferred</span>
                                        @elseif($currentMonthData['settlement_status'] === 'settled')
                                            <span class="badge bg-info">Settled</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </h4>
                                    <small>{{ \Carbon\Carbon::parse($currentMonth . '-01')->format('F Y') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Payments -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-clock text-primary"></i>
                                Recent Payments ({{ \Carbon\Carbon::parse($currentMonth . '-01')->format('F Y') }})
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Property</th>
                                            <th>Tenant</th>
                                            <th>Payment Date</th>
                                            <th>Amount</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentPayments as $payment)
                                        <tr>
                                            <td>
                                                <strong>{{ $payment->rental->house->title }}</strong><br>
                                                <small class="text-muted">{{ $payment->rental->house->locationModel->name ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        <span class="avatar-initial rounded-circle bg-secondary">
                                                            {{ substr($payment->rental->tenant->name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                    {{ $payment->rental->tenant->name }}
                                                </div>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y H:i') }}</td>
                                            <td class="text-success fw-bold">${{ number_format($payment->amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ ucwords(str_replace('_', ' ', $payment->payment_type)) }}</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                <i class="fas fa-info-circle"></i> No payments received this month yet.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Settlement History -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-history text-primary"></i>
                                Settlement History
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Month</th>
                                            <th>Total Collected</th>
                                            <th>Commission</th>
                                            <th>Net Amount</th>
                                            <th>Status</th>
                                            <th>Processed Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($settlements as $settlement)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($settlement->settlement_month . '-01')->format('F Y') }}</td>
                                            <td class="text-success fw-bold">${{ number_format($settlement->total_rent_collected, 2) }}</td>
                                            <td class="text-warning">${{ number_format($settlement->commission_amount, 2) }}</td>
                                            <td class="text-primary fw-bold">${{ number_format($settlement->net_amount, 2) }}</td>
                                            <td>
                                                @if($settlement->status === 'transferred')
                                                    <span class="badge bg-success">Transferred</span>
                                                @elseif($settlement->status === 'settled')
                                                    <span class="badge bg-info">Settled</span>
                                                @else
                                                    <span class="badge bg-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($settlement->processed_at)
                                                    {{ \Carbon\Carbon::parse($settlement->processed_at)->format('M d, Y') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                <i class="fas fa-info-circle"></i> No settlement history available.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection