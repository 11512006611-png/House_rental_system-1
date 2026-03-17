@extends('layouts.admin')
@section('title','Admin Settings')
@section('breadcrumb')
<li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')

<div class="page-header">
    <h1><i class="fas fa-gear me-2 text-primary"></i>Admin Settings</h1>
    <p>Configure platform-wide settings such as commission rates.</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">

        {{-- Commission Rate Card --}}
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h6><i class="fas fa-percent text-purple me-2" style="color:#9333ea;"></i>Commission Settings</h6>
            </div>
            <div class="p-4">
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="commission_rate" class="form-label fw-semibold">
                            Platform Commission Rate (%)
                        </label>
                        <div class="input-group">
                            <input type="number"
                                id="commission_rate"
                                name="commission_rate"
                                class="form-control @error('commission_rate') is-invalid @enderror"
                                value="{{ old('commission_rate', $commissionRate) }}"
                                min="0" max="100" step="0.1"
                                required>
                            <span class="input-group-text bg-white"><i class="fas fa-percent text-muted"></i></span>
                            @error('commission_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text text-muted">
                            This percentage is applied to all new rental payments as platform commission.
                            Current rate: <strong class="text-purple" style="color:#9333ea;">{{ $commissionRate }}%</strong>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-floppy-disk me-2"></i>Save Settings
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Info Card --}}
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="fas fa-circle-info text-primary me-2"></i>How Commission Works</h6>
            </div>
            <div class="p-4">
                <ul class="mb-0 text-muted" style="font-size:.88rem;line-height:1.8;">
                    <li>The commission rate is applied automatically when a payment record is created.</li>
                    <li>Commission amount = Payment Amount × Commission Rate / 100</li>
                    <li>Changing the rate here will only affect <strong>new payments</strong> going forward.</li>
                    <li>Existing payment records retain their original commission amounts.</li>
                    <li>You can view all commission earnings in the <a href="{{ route('admin.transactions') }}">Transactions</a> page.</li>
                </ul>
            </div>
        </div>

    </div>
</div>

@endsection
