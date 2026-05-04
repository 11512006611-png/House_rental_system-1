@extends('layouts.admin')

@section('title', 'Refunds')

@section('breadcrumb')
<li class="breadcrumb-item active">Refunds</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Refund Management</h1>
        <p class="text-muted mb-0">Review move-out inspections and process security deposit refunds.</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h5 fw-bold mb-3">Open Move-Out Requests</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Property</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingMoveOutRequests as $request)
                    <tr>
                        <td>{{ $request->tenant?->name }}</td>
                        <td>{{ $request->house?->title }}</td>
                        <td>{{ ucfirst($request->status) }}</td>
                        <td>
                            <a href="{{ route('admin.refunds.create', $request) }}" class="btn btn-primary btn-sm">Calculate Refund</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-muted">No open move-out requests.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h5 fw-bold mb-3">Processed Refunds</h2>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Property</th>
                        <th>Deposit</th>
                        <th>Damage</th>
                        <th>Dues</th>
                        <th>Refund</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($refunds as $refund)
                    <tr>
                        <td>{{ $refund->tenant?->name }}</td>
                        <td>{{ $refund->house?->title }}</td>
                        <td>Nu. {{ number_format((float) $refund->security_deposit_amount, 2) }}</td>
                        <td>Nu. {{ number_format((float) $refund->damage_cost, 2) }}</td>
                        <td>Nu. {{ number_format((float) $refund->pending_dues, 2) }}</td>
                        <td class="fw-bold">Nu. {{ number_format((float) $refund->refund_amount, 2) }}</td>
                        <td>{{ ucfirst($refund->status) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-muted">No refunds processed yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">{{ $refunds->links('pagination::bootstrap-5') }}</div>
@endsection
