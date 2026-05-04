@extends('layouts.app')

@section('title', 'My Rentals')

@section('content')

<div class="page-header">
    <div class="container">
        <h1 class="page-title">My Rental Requests</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">My Rentals</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    @if($rentals->count() > 0)
    <div class="row g-4">
        @foreach($rentals as $rental)
        <div class="col-md-6 col-lg-4">
            <div class="rental-card">
                <div class="rental-card-top">
                    <div class="rental-status
                        {{ $rental->status === 'active' ? 'status-active' : '' }}
                        {{ $rental->status === 'pending' ? 'status-pending' : '' }}
                        {{ $rental->status === 'expired' ? 'status-expired' : '' }}
                        {{ $rental->status === 'cancelled' ? 'status-cancelled' : '' }}
                    ">
                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                        {{ ucfirst($rental->status) }}
                    </div>
                    <div class="rental-price">Nu. {{ number_format($rental->monthly_rent, 0) }}/mo</div>
                </div>
                @if($rental->house)
                <div class="rental-card-body">
                    <h6 class="fw-semibold mb-1">{{ $rental->house->title }}</h6>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-map-marker-alt me-1 text-hrs-primary"></i>
                        {{ $rental->house->location }}
                        @if($rental->house->locationModel)
                            — {{ $rental->house->locationModel->dzongkhag_name }}
                        @endif
                    </p>
                    <div class="rental-info-row">
                        <span><i class="fas fa-calendar-check text-hrs-primary me-1"></i> From: {{ $rental->rental_date->format('d M Y') }}</span>
                    </div>
                    @if($rental->notes)
                    <p class="small text-muted mt-2 mb-0 fst-italic">"{{ $rental->notes }}"</p>
                    @endif
                    
                    <!-- Action Buttons -->
                    <div class="mt-3 d-flex flex-column gap-2">
                        @if($rental->status === 'active' && $rental->stay_decision === 'yes' && !$rental->leaseAgreement)
                            <a href="{{ route('tenant.lease.upload.form', $rental) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-upload me-1"></i>Upload Lease Agreement
                            </a>
                        @elseif($rental->status === 'active' && $rental->leaseAgreement)
                            <div class="alert alert-success alert-sm py-2 px-2 mb-0">
                                <i class="fas fa-check-circle me-1"></i>
                                <small>Lease agreement uploaded</small>
                            </div>
                        @endif
                        
                        <a href="{{ route('houses.show', $rental->house) }}" class="btn btn-hrs-outline btn-sm">
                            <i class="fas fa-eye me-1"></i>View Property
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4 d-flex justify-content-center">
        {{ $rentals->links('pagination::bootstrap-5') }}
    </div>
    @else
    <div class="empty-state text-center py-5">
        <i class="fas fa-file-contract fa-4x text-muted mb-3 d-block"></i>
        <h5 class="text-muted">No rental requests yet</h5>
        <p class="text-muted small">Browse houses and submit a rental request.</p>
        <a href="{{ route('houses.index') }}" class="btn btn-hrs-primary mt-2">Search Houses</a>
    </div>
    @endif
</div>

@endsection
