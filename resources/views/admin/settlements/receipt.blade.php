@extends('layouts.app')

@section('title', 'Settlement Receipt')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-receipt"></i>
                        Monthly Settlement Receipt
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Receipt Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>House Rental System</h5>
                            <p class="mb-1">Settlement ID: #{{ $settlement->id }}</p>
                            <p class="mb-1">Settlement Month: {{ \Carbon\Carbon::parse($settlement->settlement_month . '-01')->format('F Y') }}</p>
                            <p class="mb-0">Processed Date: {{ $settlement->processed_at ? \Carbon\Carbon::parse($settlement->processed_at)->format('M d, Y H:i') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h5>Owner Details</h5>
                            <p class="mb-1">{{ $settlement->owner->name }}</p>
                            <p class="mb-1">{{ $settlement->owner->email }}</p>
                            <p class="mb-0">{{ $settlement->owner->phone ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Settlement Summary -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Total Rent Collected</td>
                                    <td class="text-end text-success fw-bold">Nu. {{ number_format($settlement->total_rent_collected, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Commission ({{ number_format($settlement->commission_rate, 1) }}%)</td>
                                    <td class="text-end text-warning">-Nu. {{ number_format($settlement->commission_amount, 2) }}</td>
                                </tr>
                                <tr class="table-active">
                                    <td><strong>Net Amount Sent to Owner</strong></td>
                                    <td class="text-end text-primary fw-bold">Nu. {{ number_format($settlement->net_amount, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Status Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6>Settlement Status</h6>
                                    @if($settlement->status === 'transferred')
                                        <span class="badge bg-success fs-6">Transferred</span>
                                        <p class="mt-2 mb-0">
                                            <small>Transferred on: {{ \Carbon\Carbon::parse($settlement->transferred_at)->format('M d, Y H:i') }}</small>
                                        </p>
                                    @elseif($settlement->status === 'settled')
                                        <span class="badge bg-info fs-6">Settled</span>
                                        <p class="mt-2 mb-0">
                                            <small>Settled on: {{ \Carbon\Carbon::parse($settlement->processed_at)->format('M d, Y H:i') }}</small>
                                        </p>
                                    @else
                                        <span class="badge bg-warning fs-6">Pending</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6>Transfer Method</h6>
                                    <p class="mb-0">Bank Transfer</p>
                                    <small class="text-muted">Admin sends the net amount to the owner account</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($settlement->transfer_notes)
                        <div class="alert alert-light border mt-4">
                            <strong>Transfer Notes:</strong> {{ $settlement->transfer_notes }}
                        </div>
                    @endif

                    @if($settlement->owner_account_number)
                        <div class="mt-3">
                            <strong>Account Number Used:</strong> {{ $settlement->owner_account_number }}
                        </div>
                    @endif

                    @if($settlement->transfer_proof_path)
                        <div class="mt-3">
                            <strong>Transfer Proof:</strong>
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $settlement->transfer_proof_path) }}" alt="Transfer Proof" class="img-fluid" style="max-width:400px;">
                            </div>
                        </div>
                    @endif

                    <!-- Footer -->
                    <div class="text-center mt-4">
                        <p class="text-muted">
                            <small>This is an official settlement receipt recorded by House Rental System for monthly rent distribution.</small>
                        </p>
                        <div class="btn-group">
                            <button onclick="window.print()" class="btn btn-outline-primary">
                                <i class="fas fa-print"></i> Print Receipt
                            </button>
                            <a href="{{ route('admin.settlements.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Settlements
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-header {
        display: none !important;
    }
    .container-fluid {
        margin: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endsection