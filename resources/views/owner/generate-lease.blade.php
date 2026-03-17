@extends('layouts.app')

@section('title', 'Generate Digital Lease')

@push('styles')
<style>
.lease-gen-container {
    max-width: 700px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.lease-gen-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 2rem;
    color: white;
    margin-bottom: 2rem;
}

.lease-gen-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 1.8rem;
}

.lease-gen-header p {
    margin: 0;
    opacity: 0.9;
}

.tenant-info-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.tenant-info-card h3 {
    margin-top: 0;
    color: #1e293b;
    font-size: 1rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e2e8f0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    color: #64748b;
    font-weight: 500;
}

.info-value {
    color: #1e293b;
    font-weight: 600;
}

.form-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.form-section h3 {
    margin: 0 0 1.5rem 0;
    color: #1e293b;
    font-size: 1.1rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #334155;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 1rem;
    font-family: inherit;
    transition: border-color 0.2s;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.help-text {
    font-size: 0.85rem;
    color: #64748b;
    margin-top: 0.4rem;
}

.summary-box {
    background: #f0f9ff;
    border-left: 4px solid #667eea;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    font-size: 0.95rem;
}

.summary-row strong {
    color: #1e293b;
}

.summary-total {
    padding-top: 0.75rem;
    border-top: 2px solid rgba(102, 126, 234, 0.2);
    font-weight: 700;
    font-size: 1.05rem;
}

.btn-group {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.btn {
    flex: 1;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn-submit {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #e2e8f0;
    color: #334155;
}

.btn-secondary:hover {
    background: #cbd5e1;
}

.alert {
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-info {
    background-color: #dbeafe;
    border-left: 4px solid #0284c7;
    color: #0c4a6e;
}

.alert-error {
    background-color: #fee2e2;
    border-left: 4px solid #dc2626;
    color: #7f1d1d;
}
</style>
@endpush

@section('content')
<div class="lease-gen-container">
    <div class="lease-gen-header">
        <h1>📄 Generate Digital Lease</h1>
        <p>Create a professional lease agreement for your tenant</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Please fix the following errors:</strong>
            <ul style="margin-bottom: 0; padding-left: 1.5rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tenant Information -->
    <div class="tenant-info-card">
        <h3>👤 Tenant Information</h3>
        <div class="info-row">
            <span class="info-label">Name:</span>
            <span class="info-value">{{ $rental->tenant->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $rental->tenant->email }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value">{{ $rental->tenant->phone }}</span>
        </div>
    </div>

    <!-- Property Information -->
    <div class="tenant-info-card">
        <h3>🏠 Property Information</h3>
        <div class="info-row">
            <span class="info-label">Property:</span>
            <span class="info-value">{{ $rental->house->title }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Location:</span>
            <span class="info-value">{{ $rental->house->locationModel?->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Monthly Rent:</span>
            <span class="info-value">Nu. {{ number_format($rental->monthly_rent, 2) }}</span>
        </div>
    </div>

    <!-- Lease Generation Form -->
    <form method="POST" action="{{ route('owner.rentals.generate-lease.store', $rental) }}" class="form-section">
        @csrf
        
        <h3>📋 Lease Details</h3>

        <div class="form-group">
            <label for="lease_end_date">Lease End Date *</label>
            <input 
                type="date" 
                id="lease_end_date" 
                name="lease_end_date"
                value="{{ old('lease_end_date', $rental->end_date?->format('Y-m-d') ?? now()->addMonths(2)->format('Y-m-d')) }}"
                min="{{ now()->tomorrow()->format('Y-m-d') }}"
                required
            >
            <div class="help-text">When will the lease expire? This will be shown in the agreement.</div>
        </div>

        <div class="form-group">
            <label for="advance_amount">Advance Payment Amount (Nu.) *</label>
            <input 
                type="number" 
                id="advance_amount" 
                name="advance_amount"
                step="0.01"
                min="0"
                value="{{ old('advance_amount', $rental->monthly_rent) }}"
                required
            >
            <div class="help-text">The amount tenant must pay as advance before moving in. Usually equals 1-3 months of rent.</div>
        </div>

        <!-- Summary -->
        <div class="summary-box">
            <div class="summary-row">
                <span>Monthly Rent:</span>
                <strong>Nu. {{ number_format($rental->monthly_rent, 2) }}</strong>
            </div>
            <div class="summary-row">
                <span>Advance Amount:</span>
                <strong id="adv-display">Nu. {{ number_format($rental->monthly_rent, 2) }}</strong>
            </div>
            <div class="summary-row summary-total">
                <span>Total Due Before Move-In:</span>
                <strong id="total-display">Nu. {{ number_format($rental->monthly_rent, 2) }}</strong>
            </div>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-submit">
                ✓ Generate & Send Lease
            </button>
            <a href="{{ route('owner.tenants') }}" class="btn btn-secondary" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">
                ✕ Cancel
            </a>
        </div>
    </form>

    <div class="alert alert-info">
        <strong>ℹ️ What happens next:</strong>
        <ul style="margin-bottom: 0; padding-left: 1.5rem;">
            <li>A professional lease agreement will be generated</li>
            <li>Your bank details will be included in the agreement</li>
            <li>The tenant will receive a notification with the lease</li>
            <li>Tenant must review and pay the advance amount</li>
            <li>After payment verification, the lease becomes active</li>
        </ul>
    </div>
</div>

<script>
document.getElementById('advance_amount').addEventListener('input', function() {
    const advanceAmount = parseFloat(this.value) || 0;
    const monthlyRent = {{ $rental->monthly_rent }};
    
    document.getElementById('adv-display').textContent = 'Nu. ' + advanceAmount.toFixed(2);
    document.getElementById('total-display').textContent = 'Nu. ' + advanceAmount.toFixed(2);
});
</script>
@endsection
