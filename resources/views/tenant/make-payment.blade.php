@extends('layouts.app')

@section('title', 'Make Monthly Payment')

@section('content')
<div class="container py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h3 class="mb-1"><i class="fas fa-credit-card text-primary me-2"></i>Make Monthly Payment</h3>
            <p class="text-muted mb-0">Pay your monthly rent for your rental property.</p>
        </div>
        <a href="{{ route('tenant.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>

    <div class="row g-4">
        {{-- Left Column: Payment Form --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0"><i class="fas fa-wallet text-primary me-2"></i>Select Month & Property</h6>
                </div>
                <div class="card-body p-4">
                    @if($activeRentals->isEmpty())
                        <div class="text-center py-5">
                            <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#f8fafc,#e2e8f0);margin:0 auto 1.25rem;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-home text-muted fs-3"></i>
                            </div>
                            <h6 class="fw-semibold">No Active Rentals</h6>
                            <p class="text-muted small mb-0">You don't have any active rentals to make payments for.</p>
                        </div>
                    @else
                        <form action="{{ route('tenant.payment.store') }}" method="POST" enctype="multipart/form-data" id="monthlyPaymentForm">
                            @csrf

                            {{-- Rental Selection --}}
                            <div class="mb-4">
                                <label for="rental_id" class="form-label fw-semibold">
                                    <i class="fas fa-building me-1" style="color:#3b82f6;"></i>Select Property
                                </label>
                                <select class="form-select @error('rental_id') is-invalid @enderror" 
                                    id="rental_id" name="rental_id" required onchange="updateRentalInfo()">
                                    <option value="">-- Choose a property --</option>
                                    @foreach($activeRentals as $rental)
                                    <option value="{{ $rental->id }}" 
                                        {{ (int) old('rental_id', $selectedRentalId ?? 0) === (int) $rental->id ? 'selected' : '' }}
                                                data-monthly-rent="{{ $rental->monthly_rent }}"
                                                data-security-deposit="{{ $rental->house->security_deposit_amount ?? 0 }}"
                                                data-house-title="{{ $rental->house->title }}">
                                            {{ $rental->house->title }} - Nu. {{ number_format((float) $rental->monthly_rent, 0) }}/month
                                        </option>
                                    @endforeach
                                </select>
                                @error('rental_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Property Details --}}
                            <div class="alert alert-info alert-dismissible fade show" id="propertyInfo" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="d-block text-muted">Property</small>
                                        <span class="fw-semibold" id="propertyTitle">—</span>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="d-block text-muted">Monthly Rent</small>
                                        <span class="fw-semibold" id="monthlyRentDisplay">—</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Month Selection --}}
                            <div class="mb-4">
                                <label for="payment_month" class="form-label fw-semibold">
                                    <i class="fas fa-calendar-alt me-1" style="color:#f59e0b;"></i>Select Payment Month
                                </label>
                                <select class="form-select @error('payment_month') is-invalid @enderror" 
                                        id="payment_month" name="payment_month" required>
                                    <option value="">-- Choose a month --</option>
                                    @for($i = 0; $i < 12; $i++)
                                        @php
                                            $date = now()->addMonths($i);
                                            $value = $date->format('Y-m');
                                            $label = $date->format('F Y');
                                            $isCurrent = $i == 0;
                                        @endphp
                                        <option value="{{ $value }}" {{ old('payment_month', $selectedMonth ?? now()->format('Y-m')) === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                            @if($isCurrent)
                                                (Current)
                                            @endif
                                        </option>
                                    @endfor
                                </select>
                                @error('payment_month')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @if($selectedPayment)
                                    <div class="alert alert-success mt-3 mb-0 py-2">
                                        <i class="fas fa-check-circle me-1"></i>
                                        This month is already paid. Your record is kept below for {{ $selectedPayment->billingMonthLabel() }}.
                                    </div>
                                @endif
                            </div>

                            {{-- Payment Method --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-credit-card me-1" style="color:#8b5cf6;"></i>Payment Method
                                </label>
                                <div class="row g-3">
                                    @foreach(['mbob' => 'Mobile Money (BoB)', 'mpay' => 'mPay', 'bdbl' => 'BDBL Transfer', 'cash' => 'Cash Payment'] as $value => $label)
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input @error('payment_method') is-invalid @enderror" 
                                                       type="radio" id="method_{{ $value }}" 
                                                       name="payment_method" value="{{ $value }}" 
                                                       {{ old('payment_method') === $value ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="method_{{ $value }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('payment_method')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Transaction ID --}}
                            <div class="mb-4">
                                <label for="transaction_id" class="form-label fw-semibold">
                                    <i class="fas fa-hashtag me-1" style="color:#06b6d4;"></i>Transaction ID (Optional)
                                </label>
                                <input type="text" class="form-control @error('transaction_id') is-invalid @enderror" 
                                       id="transaction_id" name="transaction_id" maxlength="120"
                                       placeholder="e.g., TXN123456789"
                                       value="{{ old('transaction_id') }}">
                                <small class="text-muted">If you have a transaction ID from your payment provider, enter it here.</small>
                                @error('transaction_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Payment Proof Upload --}}
                            <div class="mb-4">
                                <label for="payment_proof" class="form-label fw-semibold">
                                    <i class="fas fa-image me-1" style="color:#10b981;"></i>Payment Proof (Receipt/Screenshot)
                                </label>
                                <div class="border-2 border-dashed rounded-3 p-4 text-center" id="uploadZone" style="border-color:#cbd5e1;cursor:pointer;transition:.2s;">
                                    <i class="fas fa-cloud-upload-alt fs-3 text-muted mb-2 d-block"></i>
                                    <p class="mb-1"><strong>Click to upload or drag and drop</strong></p>
                                    <p class="text-muted small mb-2">JPG, PNG or PDF (Max 5MB)</p>
                                    <input type="file" id="payment_proof" name="payment_proof" class="d-none @error('payment_proof') is-invalid @enderror"
                                           accept="image/jpeg,image/png,application/pdf" required>
                                    <div id="fileName" class="mt-2 small text-success" style="display:none;"></div>
                                </div>
                                @error('payment_proof')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Notes --}}
                            <div class="mb-4">
                                <label for="notes" class="form-label fw-semibold">
                                    <i class="fas fa-sticky-note me-1" style="color:#ec4899;"></i>Additional Notes (Optional)
                                </label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" maxlength="500"
                                          placeholder="Any additional information about this payment...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Confirmation Checkbox --}}
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input @error('confirm_payment') is-invalid @enderror" 
                                           type="checkbox" id="confirm_payment" name="confirm_payment" required>
                                    <label class="form-check-label" for="confirm_payment">
                                        I confirm that I have made the payment and the proof is valid.
                                    </label>
                                </div>
                                @error('confirm_payment')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Submit Button --}}
                            <button type="submit" class="btn btn-hrs-primary w-100 py-3">
                                <i class="fas fa-check-circle me-2"></i>Submit Payment
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column: Payment History (Last 5 Months) --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0"><i class="fas fa-history text-primary me-2"></i>Last 5 Months</h6>
                </div>
                <div class="card-body p-4">
                    @if($lastPayments->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-inbox text-muted fs-3 mb-2 d-block"></i>
                            <p class="text-muted small mb-0">No payment history yet.</p>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach($lastPayments as $payment)
                                <div class="border rounded-3 p-3" style="border-color:#e2e8f0;">
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <div>
                                            <p class="mb-1 fw-semibold small">{{ $payment->billingMonthLabel() }}</p>
                                            <p class="text-muted small mb-0">{{ $payment->rental->house->title ?? 'Property' }}</p>
                                        </div>
                                        @if($payment->verification_status === 'verified')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>Verified
                                            </span>
                                        @elseif($payment->verification_status === 'pending')
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-hourglass-half me-1"></i>Pending
                                            </span>
                                        @elseif($payment->verification_status === 'rejected')
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle me-1"></i>Rejected
                                            </span>
                                        @endif
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold" style="color:#1e3a5f;">Nu. {{ number_format((float) $payment->amount, 0) }}</span>
                                        <small class="text-muted">{{ optional($payment->payment_date)->format('d M Y') }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <p class="small text-muted mb-1">
                                <strong>Total Paid (Last 5 Months):</strong>
                            </p>
                            <p class="fs-5 fw-semibold" style="color:#10b981;">
                                Nu. {{ number_format($lastPayments->sum('amount'), 0) }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0"><i class="fas fa-receipt text-primary me-2"></i>Payment Records</h6>
                </div>
                <div class="card-body p-4">
                    @if($recentPayments->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-folder-open text-muted fs-3 mb-2 d-block"></i>
                            <p class="text-muted small mb-0">No payment records found yet.</p>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach($recentPayments as $payment)
                                <div class="border rounded-3 p-3 {{ $selectedPayment && $selectedPayment->id === $payment->id ? 'border-success' : '' }}" style="border-color:#e2e8f0;">
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <div>
                                            <p class="mb-1 fw-semibold small">{{ $payment->billingMonthLabel() }}</p>
                                            <p class="text-muted small mb-0">{{ $payment->rental->house->title ?? 'Property' }}</p>
                                        </div>
                                        @if($payment->verification_status === 'verified')
                                            <span class="badge bg-success">Verified</span>
                                        @elseif($payment->verification_status === 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($payment->verification_status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold" style="color:#1e3a5f;">Nu. {{ number_format((float) $payment->amount, 0) }}</span>
                                        <small class="text-muted">{{ optional($payment->payment_date)->format('d M Y') }}</small>
                                    </div>
                                    @if($selectedPayment && $selectedPayment->id === $payment->id)
                                        <div class="mt-2 small text-success">
                                            <i class="fas fa-info-circle me-1"></i>This is the selected month payment record.
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Payment Statistics --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0"><i class="fas fa-chart-pie text-primary me-2"></i>Payment Status</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex gap-2 mb-3">
                        <div style="flex:1;">
                            <small class="text-muted d-block mb-1">Verified</small>
                            <p class="h5 mb-0 text-success fw-bold">{{ $paymentStats['verified'] }}</p>
                        </div>
                        <div style="flex:1;">
                            <small class="text-muted d-block mb-1">Pending</small>
                            <p class="h5 mb-0 text-warning fw-bold">{{ $paymentStats['pending'] }}</p>
                        </div>
                        <div style="flex:1;">
                            <small class="text-muted d-block mb-1">Rejected</small>
                            <p class="h5 mb-0 text-danger fw-bold">{{ $paymentStats['rejected'] }}</p>
                        </div>
                    </div>
                    <div style="height:1px;background:#e2e8f0;margin:1rem 0;"></div>
                    <small class="text-muted d-block mb-1">Total Paid (All Time)</small>
                    <p class="h6 mb-0 fw-bold" style="color:#3b82f6;">Nu. {{ number_format($paymentStats['total_paid'], 0) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // File upload drag-and-drop
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('payment_proof');
    const fileName = document.getElementById('fileName');

    uploadZone.addEventListener('click', () => fileInput.click());

    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.style.borderColor = '#3b82f6';
        uploadZone.style.background = '#eff6ff';
    });

    uploadZone.addEventListener('dragleave', () => {
        uploadZone.style.borderColor = '#cbd5e1';
        uploadZone.style.background = 'transparent';
    });

    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.style.borderColor = '#cbd5e1';
        uploadZone.style.background = 'transparent';
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            updateFileName();
        }
    });

    fileInput.addEventListener('change', updateFileName);

    function updateFileName() {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            fileName.textContent = `✓ ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            fileName.style.display = 'block';
        } else {
            fileName.style.display = 'none';
        }
    }

    // Update property info when rental is selected
    function updateRentalInfo() {
        const select = document.getElementById('rental_id');
        const selected = select.options[select.selectedIndex];
        
        if (selected.value) {
            document.getElementById('propertyInfo').style.display = 'block';
            document.getElementById('propertyTitle').textContent = selected.dataset.houseTitle;
            document.getElementById('monthlyRentDisplay').textContent = 'Nu. ' + (Number(selected.dataset.monthlyRent).toLocaleString('en-IN', {maximumFractionDigits: 0}));
        } else {
            document.getElementById('propertyInfo').style.display = 'none';
        }
    }

    // Form validation
    document.getElementById('monthlyPaymentForm').addEventListener('submit', function(e) {
        if (!this.checkValidity() === false) {
            e.preventDefault();
            e.stopPropagation();
        }
        this.classList.add('was-validated');
    });
</script>

<style>
    .border-2 {
        border-width: 2px !important;
    }
</style>
@endsection
