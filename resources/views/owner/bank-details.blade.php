@extends('layouts.app')

@section('title', 'Bank Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pb-0">
                    <h4 class="mb-1">Bank Details</h4>
                    <p class="text-muted mb-0">Update the account information the admin will use for monthly transfers.</p>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-4 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <div class="text-muted small">Current bank</div>
                                <div class="fw-semibold">
                                    @php
                                        $bankList = \App\Enums\Bank::getList();
                                        $currentBankLabel = $owner->bank_name ? ($bankList[$owner->bank_name] ?? $owner->bank_name) : 'Not provided';
                                    @endphp
                                    {{ $owner->bank_name ? strtoupper($owner->bank_name) . ' - ' . $currentBankLabel : 'Not provided' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-muted small">Current account number</div>
                                <div class="fw-semibold">{{ $masked ?? 'Not provided' }}</div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('owner.bank-details.update') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Bank Name</label>
                            <select name="bank_name" class="form-select">
                                <option value="">-- Select bank --</option>
                                @foreach(\App\Enums\Bank::getList() as $code => $label)
                                    <option value="{{ $code }}" @selected(old('bank_name', $owner->bank_name) === $code)>
                                        {{ strtoupper($code) }} - {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Account Holder Name</label>
                            <input type="text" name="account_holder_name" class="form-control" value="{{ old('account_holder_name', $owner->account_holder_name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="account_number" class="form-control" value="" placeholder="Enter new account number only if you want to change it">
                            <div class="form-text">Leave blank to keep the current account number.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $owner->phone) }}" placeholder="Used for mobile banking">
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Bank Details</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection