@extends('layouts.app')

@section('title', 'Monthly Settlements')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-calculator text-primary"></i>
                        Monthly Settlements - {{ \Carbon\Carbon::parse($currentMonth . '-01')->format('F Y') }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Owner Name</th>
                                    <th>Email</th>
                                    <th>Total Rent Collected</th>
                                    <th>Commission ({{ number_format($settlements[0]['commission_amount'] ?? 0 / max($settlements[0]['total_rent_collected'] ?? 1, 1) * 100, 1) }}%)</th>
                                    <th>Net Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($settlements as $settlement)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded-circle bg-primary">
                                                    {{ substr($settlement['owner']->name, 0, 1) }}
                                                </span>
                                            </div>
                                            {{ $settlement['owner']->name }}
                                        </div>
                                    </td>
                                    <td>{{ $settlement['owner']->email }}</td>
                                    <td class="text-success fw-bold">
                                        Nu {{ number_format($settlement['total_rent_collected'], 2) }}
                                    </td>
                                    <td class="text-warning">
                                        Nu {{ number_format($settlement['commission_amount'], 2) }}
                                    </td>
                                    <td class="text-primary fw-bold">
                                        Nu {{ number_format($settlement['net_amount'], 2) }}
                                    </td>
                                    <td>
                                        @if($settlement['settlement_status'] === 'transferred')
                                            <span class="badge bg-success">Transferred</span>
                                        @elseif($settlement['settlement_status'] === 'settled')
                                            <span class="badge bg-info">Settled</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.settlements.owner', $settlement['owner']) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Details
                                            </a>
                                            @if($settlement['settlement_status'] === 'pending' && $settlement['total_rent_collected'] > 0)
                                                <form action="{{ route('admin.settlements.process', $settlement['owner']) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="action" value="settle">
                                                    <input type="hidden" name="settlement_month" value="{{ $currentMonth }}">
                                                    <button type="submit" class="btn btn-sm btn-success"
                                                            onclick="return confirm('Are you sure you want to settle this payment?')">
                                                        <i class="fas fa-check"></i> Settle
                                                    </button>
                                                </form>

                                                <!-- Transfer button triggers modal -->
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#transferModal-{{ $settlement['owner']->id }}">
                                                    <i class="fas fa-paper-plane"></i> Transfer
                                                </button>

                                                <!-- Transfer Modal -->
                                                <div class="modal fade" id="transferModal-{{ $settlement['owner']->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Transfer to {{ $settlement['owner']->name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="{{ route('admin.settlements.process', $settlement['owner']) }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                <input type="hidden" name="action" value="transfer">
                                                                <input type="hidden" name="settlement_month" value="{{ $currentMonth }}">
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Owner Bank Name</label>
                                                                        <select name="owner_bank_code" class="form-control" style="width: 100%;">
                                                                            <option value="">-- Select bank --</option>
                                                                            @foreach(\App\Enums\Bank::getList() as $code => $label)
                                                                                <option value="{{ $code }}" @selected(old('owner_bank_code', $settlement['owner']->bank_name) === $code)>
                                                                                    {{ strtoupper($code) }} - {{ $label }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        <small class="text-muted">Bank on file for owner</small>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Account Holder</label>
                                                                        <input type="text" class="form-control" readonly value="{{ $settlement['owner']->account_holder_name }}">
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Account Number</label>
                                                                        <input type="text" name="owner_account_number" class="form-control" value="{{ $settlement['owner']->account_number }}" required>
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
                                            @elseif($settlement['settlement_id'])
                                                <a href="{{ route('admin.settlements.receipt', $settlement['settlement_id']) }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-receipt"></i> Receipt
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="fas fa-info-circle"></i> No settlements available for this month.
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
@endsection