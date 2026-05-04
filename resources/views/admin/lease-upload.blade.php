@extends('layouts.admin')
@section('title','Lease Agreement Upload')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.rentals') }}">Rental Activity</a></li>
<li class="breadcrumb-item active">Lease Agreement Upload</li>
@endsection

@section('content')
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
    <div class="page-header mb-0">
        <h1><i class="fas fa-file-contract me-2 text-primary"></i>Lease Agreement Upload</h1>
        <p>Upload the lease agreement for the selected tenant.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.rentals', ['lease_queue' => 1]) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Queue
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h6><i class="fas fa-user me-2"></i>Rental Details</h6>
            </div>
            <div class="p-3 small">
                <div class="mb-2"><strong>Tenant:</strong> {{ $rental->tenant->name ?? 'N/A' }}</div>
                <div class="mb-2"><strong>Email:</strong> {{ $rental->tenant->email ?? 'N/A' }}</div>
                <div class="mb-2"><strong>Property:</strong> {{ $rental->house->title ?? 'N/A' }}</div>
                <div class="mb-2"><strong>Location:</strong> {{ $rental->house->location ?? 'N/A' }}</div>
                <div class="mb-2"><strong>Stay Decision:</strong> {{ ucfirst((string) ($rental->stay_decision ?? 'pending')) }}</div>
                <div class="mb-2"><strong>Lease Status:</strong> {{ ucfirst((string) ($rental->lease_status ?? 'requested')) }}</div>
                <div class="mb-2"><strong>Monthly Rent:</strong> Nu. {{ number_format((float) ($rental->monthly_rent ?? $rental->house->price ?? 0), 0) }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="fas fa-upload me-2"></i>Upload Lease PDF</h6>
            </div>
            <div class="p-3">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.rentals.lease.upload', $rental) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lease Agreement PDF</label>
                            <input type="file" name="lease_file" class="form-control" accept="application/pdf,.pdf" required>
                            <div class="form-text">PDF only.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Rent Amount</label>
                            <input type="number" step="0.01" min="1" name="monthly_rent" class="form-control" value="{{ old('monthly_rent', (float) ($rental->monthly_rent ?? $rental->house->price ?? 0)) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Security Deposit</label>
                            <input type="number" step="0.01" min="0" name="security_deposit_amount" class="form-control" value="{{ old('security_deposit_amount', (float) ($rental->house->security_deposit_amount ?? 0)) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lease Duration (months)</label>
                            <input type="number" min="1" max="60" name="duration_months" class="form-control" value="{{ old('duration_months', 12) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lease Start Date</label>
                            <input type="date" name="lease_start_date" class="form-control" value="{{ old('lease_start_date', optional($rental->rental_date)->format('Y-m-d') ?? now()->toDateString()) }}">
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a href="{{ route('admin.rentals', ['lease_queue' => 1]) }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i>Upload Lease
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
