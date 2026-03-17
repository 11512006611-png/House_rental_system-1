@extends('layouts.app')

@section('title', 'Bank Details')

@push('styles')
<style>
.bank-details-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.bank-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 1.5rem;
    color: white;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.bank-card h3 {
    margin-top: 0;
    font-size: 0.9rem;
    opacity: 0.9;
}

.bank-card .info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.bank-card .info-item {
    flex: 1;
}

.bank-card .info-label {
    font-size: 0.85rem;
    opacity: 0.8;
    display: block;
    margin-bottom: 0.25rem;
}

.bank-card .info-value {
    font-size: 1.1rem;
    font-weight: 600;
}

.form-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.form-section h4 {
    margin-top: 0;
    color: #1e293b;
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #334155;
    font-size: 0.95rem;
}

.form-group input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.help-text {
    font-size: 0.85rem;
    color: #64748b;
    margin-top: 0.5rem;
}

.btn-submit {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.75rem 2rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    width: 100%;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.alert {
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background-color: #dcfce7;
    border-left: 4px solid #16a34a;
    color: #166534;
}

.alert-info {
    background-color: #dbeafe;
    border-left: 4px solid #0284c7;
    color: #0c4a6e;
}

.info-box {
    background: #f0f9ff;
    border-left: 4px solid #0284c7;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.info-box h5 {
    margin: 0 0 0.5rem 0;
    color: #0c4a6e;
    font-weight: 600;
}

.info-box p {
    margin: 0;
    color: #0c4a6e;
    font-size: 0.95rem;
    line-height: 1.5;
}
</style>
@endpush

@section('content')
<div class="bank-details-container">
    <div style="margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0.5rem; color: #1e293b;">💳 Bank Account Details</h1>
        <p style="color: #64748b; margin: 0;">Provide your bank information so tenants can make payments</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div class="info-box">
        <h5>ℹ️ Why we need this information</h5>
        <p>Your bank details will be displayed in the digital lease agreements. Tenants will transfer their advance payment to this account. Make sure all information is accurate.</p>
    </div>

    @if ($owner->bank_name && $owner->account_number)
        <div class="bank-card">
            <h3>Current Bank Details</h3>
            <div class="info">
                <div class="info-item">
                    <span class="bank-card-label">Account Holder</span>
                    <div class="bank-card-value">{{ $owner->account_holder_name ?? $owner->name }}</div>
                </div>
            </div>
            <div class="info">
                <div class="info-item">
                    <span class="bank-card-label">Bank Name</span>
                    <div class="bank-card-value">{{ $owner->bank_name }}</div>
                </div>
            </div>
            <div class="info">
                <div class="info-item">
                    <span class="bank-card-label">Account Number</span>
                    <div class="bank-card-value">{{ str_repeat('*', strlen($owner->account_number) - 4) }}{{ substr($owner->account_number, -4) }}</div>
                </div>
            </div>
            <div class="info">
                <div class="info-item">
                    <span class="bank-card-label">Advance Amount</span>
                    <div class="bank-card-value">Nu. {{ number_format($owner->advance_payment_amount ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('owner.bank-details.update') }}" class="form-section">
        @csrf
        
        <h4>{{ $owner->bank_name ? 'Update Bank Details' : 'Add Bank Details' }}</h4>

        <div class="form-group">
            <label for="bank_name">Bank Name *</label>
            <input 
                type="text" 
                id="bank_name" 
                name="bank_name" 
                value="{{ old('bank_name', $owner->bank_name) }}"
                placeholder="e.g., Bhutan Bank Limited"
                required
            >
            <div class="help-text">The name of your bank</div>
            @error('bank_name')
                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="account_holder_name">Account Holder Name *</label>
            <input 
                type="text" 
                id="account_holder_name" 
                name="account_holder_name" 
                value="{{ old('account_holder_name', $owner->account_holder_name ?? $owner->name) }}"
                placeholder="Full name as it appears on your bank account"
                required
            >
            <div class="help-text">Must match your bank account name exactly</div>
            @error('account_holder_name')
                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="account_number">Account Number *</label>
            <input 
                type="text" 
                id="account_number" 
                name="account_number" 
                value="{{ old('account_number', $owner->account_number) }}"
                placeholder="Your bank account number"
                required
            >
            <div class="help-text">Your complete bank account number</div>
            @error('account_number')
                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="advance_payment_amount">Default Advance Payment Amount (Nu.) *</label>
            <input 
                type="number" 
                id="advance_payment_amount" 
                name="advance_payment_amount" 
                value="{{ old('advance_payment_amount', $owner->advance_payment_amount ?? 0) }}"
                step="0.01"
                min="0"
                placeholder="0.00"
                required
            >
            <div class="help-text">This amount will be shown in lease agreements. Tenants must pay this before lease activation.</div>
            @error('advance_payment_amount')
                <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn-submit">
            {{ $owner->bank_name ? '✓ Update Details' : '+ Add Bank Details' }}
        </button>
    </form>

    <div style="text-align: center; padding: 1rem; color: #64748b; font-size: 0.9rem;">
        <a href="{{ route('owner.dashboard') }}" style="color: #667eea; text-decoration: none;">← Back to Dashboard</a>
    </div>
</div>
@endsection
