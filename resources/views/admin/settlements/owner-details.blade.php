@extends('layouts.app')

@section('title', 'Settlement Details - ' . $owner->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-user text-primary"></i>
                            Settlement Details for {{ $owner->name }}
                        </h4>
                        <a href="{{ route('admin.settlements.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Settlements
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Settlement Summary -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Total Rent Collected</h5>
                                    <h3>Nu. {{ number_format($monthlyData['total_rent_collected'], 2) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Commission ({{ number_format($monthlyData['commission_rate'], 1) }}%)</h5>
                                    <h3>Nu. {{ number_format($monthlyData['commission_amount'], 2) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Net Amount to Send to Owner</h5>
                                    <h3>Nu. {{ number_format($monthlyData['net_amount'], 2) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Status</h5>
                                    <h3>
                                        @if($monthlyData['settlement_status'] === 'transferred')
                                            <span class="badge bg-success">Transferred</span>
                                        @elseif($monthlyData['settlement_status'] === 'settled')
                                            <span class="badge bg-info">Settled</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    @if($monthlyData['settlement_status'] === 'pending' && $monthlyData['total_rent_collected'] > 0)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle"></i> Monthly Transfer Workflow</h5>
                                <p class="mb-2">This owner collected Nu. {{ number_format($monthlyData['total_rent_collected'], 2) }} for {{ \Carbon\Carbon::parse($currentMonth . '-01')->format('F Y') }}.</p>
                                <p class="mb-3">After deducting the {{ number_format($monthlyData['commission_rate'], 1) }}% platform commission, the admin will send Nu. {{ number_format($monthlyData['net_amount'], 2) }} to the owner.</p>
                                <div class="btn-group">
                                    <form action="{{ route('admin.settlements.process', $owner) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="action" value="settle">
                                        <input type="hidden" name="settlement_month" value="{{ $currentMonth }}">
                                        <button type="submit" class="btn btn-success"
                                                onclick="return confirm('Mark this monthly settlement as reviewed and ready for transfer?')">
                                            <i class="fas fa-check"></i> Mark as Ready
                                        </button>
                                    </form>
                                    <!-- Open modal to record transfer with proof -->
                                    <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#ownerTransferModal">
                                        <i class="fas fa-paper-plane"></i> Record Transfer
                                    </button>

                                    <!-- Transfer Modal -->
                                    <div class="modal fade" id="ownerTransferModal" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Transfer to {{ $owner->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('admin.settlements.process', $owner) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="hidden" name="action" value="transfer">
                                                    <input type="hidden" name="settlement_month" value="{{ $currentMonth }}">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Owner Bank</label>
                                                            @php
                                                                $bankList = \App\Enums\Bank::getList();
                                                                $bankLabel = $bankList[$owner->bank_name] ?? $owner->bank_name;
                                                            @endphp
                                                            <input type="text" class="form-control" readonly value="{{ strtoupper($owner->bank_name) }} - {{ $bankLabel }}">
                                                            <small class="text-muted">Bank on file for owner (read-only)</small>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Account Holder</label>
                                                            <input type="text" class="form-control" readonly value="{{ $owner->account_holder_name }}">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Account Number (masked)</label>
                                                            @php
                                                                $raw = (string) ($owner->account_number ?? '');
                                                                $digitsOnly = preg_replace('/\D+/', '', $raw);
                                                                $last4 = strlen($digitsOnly) >= 4 ? substr($digitsOnly, -4) : $digitsOnly;
                                                                $masked = $last4 ? '**** **** ' . $last4 : 'Not provided';
                                                            @endphp
                                                            <input type="text" class="form-control" readonly value="{{ $masked }}">
                                                            <small class="text-muted">Admin cannot view full account number; the owner-provided number will be used for transfer records.</small>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Transfer Notes (optional)</label>
                                                            <textarea name="transfer_notes" class="form-control" rows="3">Transfer recorded by admin via bank/direct payout.</textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Upload Transfer Proof (screenshot)</label>
                                                            <input type="file" name="transfer_proof" accept="image/*" class="form-control">
                                                            <small class="text-muted">Accepted: jpeg,png,gif — Max 5MB. This will be attached to the owner's notification and receipt.</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Confirm Transfer</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Payment Details -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list text-primary"></i>
                                Payment Details for {{ \Carbon\Carbon::parse($currentMonth . '-01')->format('F Y') }}
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
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($payments as $payment)
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
                                            <td>{{ $payment->billingMonthLabel() }}</td>
                                            <td class="text-success fw-bold">${{ number_format($payment->amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $payment->paymentTypeLabel() }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">Verified</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                <i class="fas fa-info-circle"></i> No payments found for this month.
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