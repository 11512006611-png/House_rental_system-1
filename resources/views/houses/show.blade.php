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

#rentModal .modal-content {
    border: none;
    border-radius: 18px;
    overflow: hidden;
}

#rentModal .modal-header {
    background: linear-gradient(135deg, #0f4c81, #0b6fb4);
    color: #fff;
}

#rentModal .modal-body {
    background: #f8fbff;
    color: var(--house-ink);
}

#rentModal .form-label,
#rentModal .small,
#rentModal .text-muted {
    color: #334155 !important;
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

                    @if($house->status === 'available')
                        @auth
                            @if(Auth::user()->isTenant())
                                <button type="button" class="btn btn-hrs-primary w-100 btn-lg mb-3" data-bs-toggle="modal" data-bs-target="#rentModal">
                                    <i class="fas fa-file-contract me-2"></i> Send Rental Request
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
                            <a href="{{ route('login') }}" class="btn btn-hrs-primary w-100 btn-lg mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i> Login to Request
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
<div class="modal fade" id="rentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-bold mb-1"><i class="fas fa-file-contract me-2"></i>Send Rental Request</h5>
                    <p class="mb-0 small" style="opacity:.9;">Start the process to rent this property</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('rentals.store', $house) }}" method="POST" id="rentalRequestForm">
                @csrf
                <div class="modal-body p-4">
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

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Preferred Move-in Date <span class="text-danger">*</span></label>
                        <input type="date" name="rental_date" id="rentalDate" class="form-control" min="{{ date('Y-m-d') }}" required>
                        <small class="d-block mt-1">The date you want to move in</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Additional Notes <span class="text-muted" style="font-weight:400;">(Optional)</span></label>
                        <textarea name="notes" id="rentalNotes" class="form-control" rows="4" maxlength="500" placeholder="Any special requirements or questions for the owner..."></textarea>
                        <small class="d-block mt-1"><span id="charCount">0</span>/500 characters</small>
                    </div>

                    <div class="alert d-flex gap-2 mt-3" style="border-radius:12px;background:#eff6ff;border:1px solid #bfdbfe;color:#1e3a8a;">
                        <i class="fas fa-info-circle mt-1 flex-shrink-0"></i>
                        <div class="small mb-0">
                            <strong>What happens next?</strong><br>
                            The owner will review your request and either accept or reject it. You will be notified once they respond.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0" style="background:#f1f6ff;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> Send Request
                    </button>
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

document.getElementById('rentalNotes')?.addEventListener('input', function() {
    const charCount = document.getElementById('charCount');
    if (charCount) {
        charCount.textContent = this.value.length;
    }
});

function copyLink() {
    navigator.clipboard.writeText(window.location.href)
        .then(() => alert('Link copied!'));
}
</script>
@endpush
@endsection
