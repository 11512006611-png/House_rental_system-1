@extends('layouts.app')

@section('title', 'Booking Details')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <h1 class="h3 fw-bold mb-1">Booking #{{ $booking->id }}</h1>
                            <p class="text-muted mb-0">{{ $booking->house?->title }}</p>
                        </div>
                        <span class="badge bg-info text-dark text-uppercase">{{ str_replace('_', ' ', $booking->status) }}</span>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase">First Month Rent</div>
                            <div class="fw-bold fs-4">Nu. {{ number_format((float) $booking->first_month_rent_amount, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase">Security Deposit</div>
                            <div class="fw-bold fs-4">Nu. {{ number_format((float) $booking->security_deposit_amount, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase">Total Reserved Amount</div>
                            <div class="fw-bold fs-4">Nu. {{ number_format((float) ($booking->first_month_rent_amount + $booking->security_deposit_amount), 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h5 fw-bold">Payment Records</h2>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Held By Admin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($booking->payments as $payment)
                                <tr>
                                    <td>{{ $payment->paymentTypeLabel() }}</td>
                                    <td>Nu. {{ number_format((float) $payment->amount, 2) }}</td>
                                    <td>{{ ucfirst($payment->verification_status) }}</td>
                                    <td>{{ $payment->held_by_admin ? 'Yes' : 'No' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-muted">No payment records yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($booking->refund)
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 fw-bold">Refund Summary</h2>
                    <p class="mb-1">Security Deposit: Nu. {{ number_format((float) $booking->refund->security_deposit_amount, 2) }}</p>
                    <p class="mb-1">Damage Cost: Nu. {{ number_format((float) $booking->refund->damage_cost, 2) }}</p>
                    <p class="mb-1">Pending Dues: Nu. {{ number_format((float) $booking->refund->pending_dues, 2) }}</p>
                    <p class="fw-bold mb-0">Refund Amount: Nu. {{ number_format((float) $booking->refund->refund_amount, 2) }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
