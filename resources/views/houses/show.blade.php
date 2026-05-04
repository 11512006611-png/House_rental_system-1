@extends('layouts.app')

@section('title', $house->title)

@section('content')
@php
    $galleryImages = $house->houseImages ?? collect();
    $mainImage = $galleryImages->first()?->path ? asset('storage/' . $galleryImages->first()->path) : $house->image_url;
@endphp

<style>
:root {
    --house-bg: #f3f6fb;
    --house-surface: #ffffff;
    --house-ink: #0f172a;
    --house-muted: #5b6475;
    --house-brand: #0f4c81;
    --house-brand-2: #14b8a6;
    --house-border: #dbe3ee;
    --house-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
}

.house-view-wrap {
    background: radial-gradient(circle at top right, #d7efff 0%, #eef4ff 30%, #f6f9ff 70%);
    min-height: 100vh;
    font-family: "Poppins", "Segoe UI", Tahoma, sans-serif;
}

.hero-shell,
.detail-section,
.sidebar-price-card,
.owner-card,
.share-card,
.house-card {
    background: var(--house-surface);
    border: 1px solid var(--house-border);
    border-radius: 16px;
    box-shadow: var(--house-shadow);
}

.hero-shell {
    padding: 1.1rem 1.25rem;
}

.house-title {
    color: var(--house-ink);
    font-size: 1.85rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.house-subline {
    color: var(--house-muted);
    font-weight: 500;
}

.house-price-display {
    text-align: right;
}

.price-amount {
    display: block;
    color: var(--house-brand);
    font-size: 1.95rem;
    font-weight: 800;
    line-height: 1;
}

.price-period {
    color: var(--house-muted);
    font-size: 0.9rem;
}

.main-image-wrap {
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid var(--house-border);
    background: #fff;
}

.main-image-wrap img {
    width: 100%;
    height: 460px;
    object-fit: cover;
}

.thumb-row {
    display: flex;
    gap: 0.6rem;
    overflow-x: auto;
    padding-bottom: 0.3rem;
}

.thumb-btn {
    border: 2px solid transparent;
    border-radius: 12px;
    padding: 0;
    background: transparent;
    flex: 0 0 auto;
}

.thumb-btn.active,
.thumb-btn:hover {
    border-color: var(--house-brand-2);
}

.thumb-btn img {
    width: 96px;
    height: 74px;
    border-radius: 10px;
    object-fit: cover;
}

.feature-highlight-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.8rem;
}

.feature-highlight-item {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    padding: 0.85rem;
    border-radius: 12px;
    border: 1px solid var(--house-border);
    background: #f8fbff;
}

.feature-highlight-item i {
    font-size: 1.1rem;
}

.detail-section {
    padding: 1rem;
}

.detail-section-title {
    color: var(--house-ink);
    font-weight: 700;
    margin-bottom: 0.85rem;
}

.info-item {
    border: 1px solid var(--house-border);
    border-radius: 12px;
    padding: 0.75rem;
    background: #fbfdff;
}

.info-label {
    display: block;
    color: var(--house-muted);
    font-size: 0.78rem;
    margin-bottom: 0.15rem;
}

.info-value {
    color: var(--house-ink);
    font-weight: 600;
}

.sidebar-price-card,
.owner-card,
.share-card {
    padding: 1rem;
}

.price-big {
    color: var(--house-brand);
    font-weight: 800;
    font-size: 1.5rem;
}

.price-big .price-period {
    font-size: 0.82rem;
    margin-left: 0.2rem;
}

.price-type-badge {
    display: inline-block;
    margin-top: 0.4rem;
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    background: #dff7f3;
    color: #0f766e;
}

.sidebar-info-list {
    border-top: 1px dashed #cad7ea;
    padding-top: 0.8rem;
}

.sidebar-info-item {
    display: flex;
    justify-content: space-between;
    gap: 0.6rem;
    color: var(--house-ink);
    font-size: 0.9rem;
    margin-bottom: 0.45rem;
}

.owner-card-header {
    color: var(--house-ink);
    font-weight: 700;
    margin-bottom: 0.6rem;
}

.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, #0f4c81, #14b8a6);
}

.house-card {
    overflow: hidden;
}

.house-card-img-wrapper {
    position: relative;
}

.house-card-img {
    width: 100%;
    height: 190px;
    object-fit: cover;
}

.house-card-price {
    position: absolute;
    left: 0.65rem;
    bottom: 0.65rem;
    background: rgba(15, 23, 42, 0.9);
    color: #fff;
    border-radius: 10px;
    padding: 0.35rem 0.6rem;
    line-height: 1;
}

.house-card-price small {
    opacity: 0.85;
}

.house-card-body {
    padding: 0.85rem;
}

.house-card-title {
    color: var(--house-ink);
    font-weight: 700;
    margin-bottom: 0.35rem;
}

.house-card-location {
    color: #334155;
    margin-bottom: 0.5rem;
}

.house-card-features {
    display: flex;
    gap: 0.65rem;
    font-size: 0.84rem;
    color: #334155;
    align-items: center;
}

.badge-type {
    background: #e0ecff;
    color: #1d4ed8;
    border-radius: 999px;
    padding: 0.15rem 0.5rem;
    font-weight: 600;
}

#inspectionModal .modal-content {
    border: none;
    border-radius: 18px;
    overflow: hidden;
    max-height: 92vh;
}

#inspectionModal .modal-header {
    background: linear-gradient(135deg, #0f4c81, #0b6fb4);
    color: #fff;
}

#inspectionModal .modal-body {
    background: #f8fbff;
    color: var(--house-ink);
    overflow-y: auto;
}

#inspectionModal .form-label,
#inspectionModal .small,
#inspectionModal .text-muted {
    color: #334155 !important;
}

#inspectionModal .modal-footer {
    position: sticky;
    bottom: 0;
    z-index: 2;
}

.rent-send-quick {
    background: #0b6fb4;
    border-color: #0b6fb4;
    color: #fff;
    font-weight: 700;
}

.rent-send-quick:hover,
.rent-send-quick:focus {
    background: #09598f;
    border-color: #09598f;
    color: #fff;
}

.rent-summary {
    background: #fff;
    border: 1px solid var(--house-border);
    border-radius: 12px;
    padding: 0.9rem;
}

@media (max-width: 991px) {
    .main-image-wrap img {
        height: 300px;
    }

    .price-amount,
    .house-price-display {
        text-align: left;
    }
}
</style>

<div class="house-view-wrap py-4">
    <div class="container">
        <div class="mb-3">
            <a href="{{ route('houses.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Listings
            </a>
        </div>

        <div class="hero-shell mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8">
                    <h1 class="house-title">{{ $house->title }}</h1>
                    <div class="house-subline">
                        <i class="fas fa-location-dot text-danger me-1"></i>
                        {{ $house->location }}
                        @if($house->locationModel)
                            , {{ $house->locationModel->dzongkhag_name }} Dzongkhag
                        @endif
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="house-price-display">
                        <span class="price-amount">Nu. {{ number_format($house->price, 0) }}</span>
                        <span class="price-period">/month</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="main-image-wrap mb-3">
                    <img id="mainHouseImage" src="{{ $mainImage }}" alt="{{ $house->title }}">
                </div>

                @if($galleryImages->count() > 1)
                    <div class="thumb-row mb-4">
                        @foreach($galleryImages as $index => $img)
                            @php $thumbUrl = asset('storage/' . $img->path); @endphp
                            <button class="thumb-btn {{ $index === 0 ? 'active' : '' }}" type="button" onclick="setMainHouseImage('{{ $thumbUrl }}', this)">
                                <img src="{{ $thumbUrl }}" alt="Photo {{ $index + 1 }}">
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="feature-highlight-row mb-4">
                    <div class="feature-highlight-item">
                        <i class="fas fa-bed text-primary"></i>
                        <div>
                            <div class="fw-semibold">{{ $house->bedrooms }}</div>
                            <div class="small text-muted">Bedrooms</div>
                        </div>
                    </div>
                    <div class="feature-highlight-item">
                        <i class="fas fa-bath text-info"></i>
                        <div>
                            <div class="fw-semibold">{{ $house->bathrooms }}</div>
                            <div class="small text-muted">Bathrooms</div>
                        </div>
                    </div>
                    @if($house->area)
                    <div class="feature-highlight-item">
                        <i class="fas fa-ruler-combined text-success"></i>
                        <div>
                            <div class="fw-semibold">{{ $house->area }}</div>
                            <div class="small text-muted">Floor Area</div>
                        </div>
                    </div>
                    @endif
                    <div class="feature-highlight-item">
                        <i class="fas fa-home text-warning"></i>
                        <div>
                            <div class="fw-semibold">{{ $house->type }}</div>
                            <div class="small text-muted">House Type</div>
                        </div>
                    </div>
                </div>

                @if($house->description)
                <div class="detail-section mb-4">
                    <h5 class="detail-section-title">Description</h5>
                    <p class="mb-0" style="color:#334155;line-height:1.8;">{{ $house->description }}</p>
                </div>
                @endif

                <div class="detail-section mb-4">
                    <h5 class="detail-section-title"><i class="fas fa-map-marked-alt me-2 text-primary"></i>Location Details</h5>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="info-item">
                                <span class="info-label">Dzongkhag</span>
                                <span class="info-value">{{ $house->locationModel->dzongkhag_name ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-item">
                                <span class="info-label">Area</span>
                                <span class="info-value">{{ $house->location }}</span>
                            </div>
                        </div>
                        @if($house->address)
                        <div class="col-12">
                            <div class="info-item">
                                <span class="info-label">Full Address</span>
                                <span class="info-value">{{ $house->address }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sidebar-price-card mb-4">
                    <div class="price-big">Nu. {{ number_format($house->price, 0) }}<span class="price-period">/month</span></div>
                    <div class="price-type-badge">{{ $house->type }}</div>
                    <hr>

                    @if(Auth::check() && Auth::user()->isTenant() && !empty($tenantRental))
                        @if($advancePaymentEligible)
                            <button type="button" class="btn btn-hrs-primary w-100 btn-lg mb-3" data-bs-toggle="modal" data-bs-target="#advancePaymentModal">
                                <i class="fas fa-credit-card me-2"></i> Pay Advance Payment
                            </button>
                        @elseif(isset($advancePaymentStatus) && $advancePaymentStatus === 'pending')
                            <div class="alert alert-warning text-center mb-3" style="border-radius:16px;">
                                <i class="fas fa-clock me-1"></i> Payment Verification Pending
                            </div>
                        @elseif(isset($advancePaymentStatus) && $advancePaymentStatus === 'verified')
                            <div class="alert alert-success text-center mb-3" style="border-radius:16px;">
                                <i class="fas fa-check me-1"></i> Payment Verified
                            </div>
                        @elseif(isset($advancePaymentStatus) && $advancePaymentStatus === 'rejected')
                            <div class="alert alert-danger text-center mb-3" style="border-radius:16px;">
                                <i class="fas fa-times me-1"></i> Payment Rejected - Try Again
                            </div>
                            <button type="button" class="btn btn-hrs-primary w-100 btn-lg mb-3" data-bs-toggle="modal" data-bs-target="#advancePaymentModal">
                                <i class="fas fa-credit-card me-2"></i> Resubmit Payment
                            </button>
                        @else
                            <div class="alert alert-info text-center mb-3" style="border-radius:16px;font-size:0.9rem;">
                                <i class="fas fa-info-circle me-1"></i> Complete inspection and accept lease to pay.
                            </div>
                        @endif
                    @elseif($house->status === 'available')
                        @auth
                            @if(Auth::user()->isTenant())
                                <button type="button" class="btn btn-hrs-primary w-100 btn-lg mb-3" data-bs-toggle="modal" data-bs-target="#inspectionModal">
                                    <i class="fas fa-search me-2"></i> Request Inspection
                                </button>
                            @elseif(Auth::user()->id === $house->owner_id)
                                <a href="{{ route('houses.edit', $house) }}" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fas fa-edit me-1"></i> Edit Listing
                                </a>
                                <form action="{{ route('houses.destroy', $house) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this listing?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                                        <i class="fas fa-trash me-1"></i> Delete Listing
                                    </button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('login', ['role' => 'tenant', 'intended_house_id' => $house->id]) }}" class="btn btn-hrs-primary w-100 btn-lg mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i> Login to Request Inspection
                            </a>
                        @endauth
                    @else
                        <div class="alert alert-warning text-center mb-0">
                            <i class="fas fa-ban me-1"></i> This house is currently <strong>{{ $house->status }}</strong>.
                        </div>
                    @endif

                    <div class="sidebar-info-list mt-3">
                        <div class="sidebar-info-item">
                            <span><i class="fas fa-calendar-alt text-primary me-2"></i>Listed</span>
                            <span>{{ $house->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="sidebar-info-item">
                            <span><i class="fas fa-tag text-success me-2"></i>Type</span>
                            <span>{{ $house->type }}</span>
                        </div>
                        <div class="sidebar-info-item mb-0">
                            <span><i class="fas fa-check-circle text-success me-2"></i>Status</span>
                            <span class="{{ $house->status === 'available' ? 'text-success' : 'text-warning' }} fw-semibold">{{ ucfirst($house->status) }}</span>
                        </div>
                    </div>
                </div>

                <div class="owner-card mb-4">
                    <div class="owner-card-header">
                        <i class="fas fa-user-circle me-2"></i> Listed by
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar">{{ strtoupper(substr($house->owner->name ?? 'O', 0, 1)) }}</div>
                        <div>
                            <div class="fw-semibold">{{ $house->owner->name ?? 'Owner' }}</div>
                            <div class="small text-muted">Property Owner</div>
                            @if($house->owner->phone)
                                <div class="small">
                                    <i class="fas fa-phone text-primary me-1"></i> {{ $house->owner->phone }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="share-card">
                    <div class="small text-muted mb-2 fw-semibold">Share this listing</div>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-outline-primary flex-fill"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-info flex-fill"><i class="fab fa-twitter"></i></a>
                        <button class="btn btn-sm btn-outline-secondary flex-fill" onclick="copyLink()"><i class="fas fa-link"></i></button>
                    </div>
                </div>
            </div>
        </div>

        @if($relatedHouses->count() > 0)
        <div class="mt-5">
            <h4 class="fw-bold mb-4" style="color:#0f172a;">More in {{ $house->locationModel->dzongkhag_name ?? $house->location }}</h4>
            <div class="row g-4">
                @foreach($relatedHouses as $related)
                <div class="col-md-4">
                    <div class="house-card h-100">
                        <div class="house-card-img-wrapper">
                            <img src="{{ $related->image_url }}" alt="{{ $related->title }}" class="house-card-img">
                            <div class="house-card-price">
                                <span>Nu. {{ number_format($related->price, 0) }}</span>
                                <small>/month</small>
                            </div>
                        </div>
                        <div class="house-card-body">
                            <h6 class="house-card-title">{{ $related->title }}</h6>
                            <p class="house-card-location">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $related->location }}
                            </p>
                            <div class="house-card-features">
                                <span><i class="fas fa-bed"></i> {{ $related->bedrooms }}</span>
                                <span><i class="fas fa-bath"></i> {{ $related->bathrooms }}</span>
                                <span class="badge-type">{{ $related->type }}</span>
                            </div>
                            <a href="{{ route('houses.show', $related) }}" class="btn btn-outline-primary w-100 mt-3 btn-sm">View More</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@auth
@if(Auth::user()->isTenant())
<div class="modal fade" id="inspectionModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-bold mb-1"><i class="fas fa-search me-2"></i>Request Inspection</h5>
                    <p class="mb-0 small" style="opacity:.9;">Send your preferred time directly to admin for review</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inspections.store') }}" method="POST" id="inspectionRequestForm">
                @csrf
                <input type="hidden" name="house_id" value="{{ $house->id }}">
                <input type="hidden" id="inspectionHasErrors" value="{{ $errors->hasAny(['house_id', 'preferred_date', 'preferred_time', 'message']) ? '1' : '0' }}">
                <div class="modal-body p-4">
                    @if($errors->hasAny(['house_id', 'preferred_date', 'preferred_time', 'message']))
                    <div class="alert alert-danger">
                        <strong>Please fix these fields:</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            @error('house_id')<li>{{ $message }}</li>@enderror
                            @error('preferred_date')<li>{{ $message }}</li>@enderror
                            @error('preferred_time')<li>{{ $message }}</li>@enderror
                            @error('message')<li>{{ $message }}</li>@enderror
                        </ul>
                    </div>
                    @endif

                    <div class="rent-summary mb-3">
                        <div class="row g-3 mb-0">
                            <div class="col-6">
                                <div class="small text-muted mb-1">Property</div>
                                <div class="fw-bold">{{ $house->title }}</div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted mb-1">Type</div>
                                <div class="fw-bold">{{ strtoupper($house->type) }}</div>
                            </div>
                            <div class="col-12">
                                <div class="small text-muted mb-1">Monthly Rent</div>
                                <div class="fw-bold text-success">Nu. {{ number_format($house->price, 0) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Preferred Date</label>
                            <input type="date" name="preferred_date" value="{{ old('preferred_date') }}" class="form-control @error('preferred_date') is-invalid @enderror" min="{{ now()->toDateString() }}" required>
                            @error('preferred_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Preferred Time</label>
                            <select name="preferred_time" class="form-select @error('preferred_time') is-invalid @enderror" required>
                                <option value="">Select time</option>
                                <option value="09:00" @selected(old('preferred_time') === '09:00')>9:00 AM</option>
                                <option value="11:00" @selected(old('preferred_time') === '11:00')>11:00 AM</option>
                                <option value="14:00" @selected(old('preferred_time') === '14:00')>2:00 PM</option>
                                <option value="16:00" @selected(old('preferred_time') === '16:00')>4:00 PM</option>
                                <option value="18:00" @selected(old('preferred_time') === '18:00')>6:00 PM</option>
                            </select>
                            @error('preferred_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold">Message to Admin <span class="text-muted" style="font-weight:400;">(Optional)</span></label>
                        <textarea name="message" id="inspectionMessage" class="form-control @error('message') is-invalid @enderror" rows="3" maxlength="1000" placeholder="Any schedule details or instructions for admin...">{{ old('message') }}</textarea>
                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="d-block mt-1"><span id="charCount">0</span>/1000 characters</small>
                    </div>

                    <div class="alert d-flex gap-2 mt-3" style="border-radius:12px;background:#eff6ff;border:1px solid #bfdbfe;color:#1e3a8a;">
                        <i class="fas fa-info-circle mt-1 flex-shrink-0"></i>
                        <div class="small mb-0">
                            <strong>What happens next?</strong><br>
                            Admin will receive this inspection request immediately and review your preferred schedule.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0" style="background:#f1f6ff;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn rent-send-quick" id="inspectionSubmitBtn">
                        <i class="fas fa-paper-plane me-1"></i> Send Inspection Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if(Auth::user()->isTenant() && !empty($tenantRental) && $advancePaymentEligible)
    @php
        $securityDeposit = $house->security_deposit_amount ?? $house->price;
        $commissionRate = $house->admin_commission_rate ?? 5;
        $serviceFee = round($house->price * ($commissionRate / 100), 2);
        // Charge for two months (rent + service fee for each month)
        $firstMonthTotal = round(($house->price * 2) + ($serviceFee * 2), 2);
        $totalAdvance = round($firstMonthTotal + $securityDeposit, 2);
    @endphp
    <div class="modal fade" id="advancePaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-0">
                    <div>
                        <h5 class="modal-title fw-bold mb-1"><i class="fas fa-credit-card me-2"></i>Advance Payment</h5>
                        <p class="mb-0 small" style="opacity:.9;">Pay your first month rent plus security deposit after accepting the lease agreement.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('rentals.pay', $tenantRental) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="confirm_payment" value="1">
                    <div class="modal-body p-4">
                        <div class="alert alert-info d-flex gap-2 mb-4" style="border-radius:12px;">
                            <i class="fas fa-info-circle mt-1 flex-shrink-0"></i>
                            <div class="small mb-0">
                                Please upload your payment proof and submit. Admin will verify both the first month rent and security deposit.
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" id="paymentMethodHouse" class="form-select" required>
                                    <option value="">Select payment method</option>
                                    <option value="mbob">mBoB</option>
                                    <option value="mpay">mPay</option>
                                    <option value="bdbl">BDBL</option>
                                    <option value="cash">Cash</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Transaction ID <span class="text-muted">(Optional)</span></label>
                                <input type="text" name="transaction_id" class="form-control" maxlength="120" placeholder="Transaction reference number">
                            </div>
                        </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Payment Proof <span class="text-danger">*</span></label>
                                <input type="file" name="payment_proof" id="paymentProofHouse" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                <small class="text-muted d-block mt-1">Accepted: JPG, PNG, PDF. Max 5MB.</small>
                            </div>

                            <div class="mb-3">
                                <button type="button" id="confirmInlineBtnHouse" class="btn btn-lg w-100" disabled
                                        style="background:linear-gradient(135deg,#059669,#047857);color:white;border-radius:12px;font-weight:800;padding:0.9rem 1rem;font-size:1rem;box-shadow:0 4px 6px rgba(0,0,0,0.08);border:2px solid #10b981;">
                                    <i class="fas fa-check-circle me-2"></i>✓ Confirm Payment
                                </button>
                                <small class="text-success d-block text-center fw-semibold mt-2" style="font-size:0.95rem;">Select a payment method and upload screenshot, then click Confirm Payment</small>
                            </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Additional Notes <span class="text-muted">(Optional)</span></label>
                            <textarea name="notes" rows="3" class="form-control" maxlength="500" placeholder="Payment details or bank reference..."></textarea>
                        </div>

                        <div class="p-3 rounded-3" style="background:#ecfeff;border:1px solid #bae6fd;">
                            <div class="small text-uppercase fw-semibold mb-2" style="color:#0369a1;">Payment Breakdown</div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>First month rent</span>
                                <span>Nu. {{ number_format($house->price, 0) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1 small text-muted">
                                <span>Service fee ({{ $commissionRate }}%)</span>
                                <span>Nu. {{ number_format($serviceFee, 0) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                <span><strong>Two months total</strong></span>
                                <span><strong>Nu. {{ number_format($firstMonthTotal, 0) }}</strong></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Security deposit</span>
                                <span>Nu. {{ number_format($securityDeposit, 0) }}</span>
                            </div>
                            <div class="d-flex justify-content-between pt-2 border-top">
                                <span><strong>Total advance</strong></span>
                                <span><strong>Nu. {{ number_format($totalAdvance, 0) }}</strong></span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 d-flex flex-column align-items-stretch" style="background:#f1f6ff;padding:1.25rem 1.5rem 1.75rem;min-height:110px;">
                        <small class="text-muted mb-3">Please review your payment details. Admin will verify your proof after submission.</small>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-hrs-primary">Submit Payment</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endauth

@push('scripts')
<script>
function setMainHouseImage(imageUrl, button) {
    const mainImage = document.getElementById('mainHouseImage');
    if (mainImage) {
        mainImage.src = imageUrl;
    }

    document.querySelectorAll('.thumb-btn').forEach(btn => btn.classList.remove('active'));
    if (button) {
        button.classList.add('active');
    }
}

document.getElementById('inspectionMessage')?.addEventListener('input', function() {
    const charCount = document.getElementById('charCount');
    if (charCount) {
        charCount.textContent = this.value.length;
    }
});

window.addEventListener('DOMContentLoaded', function() {
    const inspectionForm = document.getElementById('inspectionRequestForm');
    const submitBtn = document.getElementById('inspectionSubmitBtn');
    const textArea = document.getElementById('inspectionMessage');
    const charCount = document.getElementById('charCount');
    const hasInspectionErrors = document.getElementById('inspectionHasErrors')?.value === '1';

    if (textArea && charCount) {
        charCount.textContent = textArea.value.length;
    }

    if (hasInspectionErrors) {
        const modalEl = document.getElementById('inspectionModal');
        if (modalEl && window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    if (inspectionForm && submitBtn) {
        inspectionForm.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';
        });
    }
});

function copyLink() {
    navigator.clipboard.writeText(window.location.href)
        .then(() => alert('Link copied!'));
}

// Inline Confirm Payment button for advance payment modal
window.addEventListener('DOMContentLoaded', function() {
    const advModal = document.getElementById('advancePaymentModal');
    if (!advModal) return;
    const form = advModal.querySelector('form');
    const method = document.getElementById('paymentMethodHouse');
    const proof = document.getElementById('paymentProofHouse');
    const inlineBtn = document.getElementById('confirmInlineBtnHouse');

    function validateInline() {
        if (!inlineBtn) return;
        const ok = method && method.value !== '' && proof && proof.files && proof.files.length > 0;
        inlineBtn.disabled = !ok;
        inlineBtn.style.opacity = ok ? '1' : '0.6';
        inlineBtn.style.cursor = ok ? 'pointer' : 'not-allowed';
    }

    method?.addEventListener('change', validateInline);
    proof?.addEventListener('change', validateInline);

    inlineBtn?.addEventListener('click', function() {
        if (inlineBtn.disabled) return;
        inlineBtn.disabled = true;
        inlineBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
        // submit the modal form
        if (form) form.submit();
    });
});
</script>
@endpush
@endsection
