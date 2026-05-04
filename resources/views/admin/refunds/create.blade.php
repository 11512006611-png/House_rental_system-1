@extends('layouts.admin')

@section('title', 'Process Refund')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.refunds.index') }}">Refunds</a></li>
<li class="breadcrumb-item active">Process Refund</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 fw-bold mb-1">Process Refund for {{ $booking->house?->title }}</h1>
                <p class="text-muted">Use the formula: refund_amount = security_deposit - damage_cost - pending_dues</p>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small text-uppercase">Security Deposit</div>
                            <div class="fw-bold">Nu. {{ number_format((float) $booking->security_deposit_amount, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small text-uppercase">Tenant</div>
                            <div class="fw-bold">{{ $moveOutRequest->tenant?->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small text-uppercase">Move-Out Date</div>
                            <div class="fw-bold">{{ optional($moveOutRequest->move_out_date)->format('d M Y') }}</div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.refunds.store', $moveOutRequest) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Damage Cost</label>
                            <input type="number" min="0" step="0.01" name="damage_cost" value="{{ old('damage_cost', 0) }}" class="form-control @error('damage_cost') is-invalid @enderror">
                            @error('damage_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pending Dues</label>
                            <input type="number" min="0" step="0.01" name="pending_dues" value="{{ old('pending_dues', 0) }}" class="form-control @error('pending_dues') is-invalid @enderror">
                            @error('pending_dues')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Inspection Notes</label>
                            <textarea name="inspection_notes" rows="4" class="form-control @error('inspection_notes') is-invalid @enderror">{{ old('inspection_notes') }}</textarea>
                            @error('inspection_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Admin Notes</label>
                            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary px-4">Process Refund</button>
                        <a href="{{ route('admin.refunds.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
