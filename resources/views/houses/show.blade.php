@extends('layouts.app')

@section('title', $house->title)

@section('content')

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('houses.index') }}">Houses</a></li>
                <li class="breadcrumb-item active text-truncate" style="max-width:180px;">{{ $house->title }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">

        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Main Image -->
            <div class="house-detail-img-wrapper mb-4">
                <img src="{{ $house->image_url }}" alt="{{ $house->title }}" class="house-detail-img" id="mainHouseImage">
                <div class="house-detail-badges">
                    <span class="badge-status {{ $house->status === 'available' ? 'badge-available' : 'badge-rented' }} badge-lg">
                        {{ ucfirst($house->status) }}
                    </span>
                    <span class="badge-type badge-lg">{{ $house->type }}</span>
                </div>
            </div>

            @if($house->houseImages->count() > 1)
            <div class="d-flex flex-wrap gap-2 mb-4">
                @foreach($house->houseImages as $galleryImage)
                <button type="button"
                        class="btn p-0 border rounded overflow-hidden"
                        onclick="setMainHouseImage('{{ asset('storage/' . $galleryImage->path) }}', this)">
                    <img src="{{ asset('storage/' . $galleryImage->path) }}"
                         alt="{{ $house->title }} photo {{ $loop->iteration }}"
                         style="width:92px;height:70px;object-fit:cover;">
                </button>
                @endforeach
            </div>
            @endif

            <!-- Title & Location -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h2 class="fw-bold text-dark mb-1">{{ $house->title }}</h2>
                        <p class="text-muted mb-0">
                            <i class="fas fa-map-marker-alt text-hrs-primary me-1"></i>
                            {{ $house->address ?? $house->location }}
                            @if($house->locationModel)
                                , {{ $house->locationModel->dzongkhag_name }} Dzongkhag
                            @endif
                        </p>
                    </div>
                    <div class="text-end">
                        <div class="house-price-display">
                            <span class="price-amount">Nu. {{ number_format($house->price, 0) }}</span>
                            <span class="price-period">/month</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Row -->
            <div class="feature-highlight-row mb-4">
                <div class="feature-highlight-item">
                    <i class="fas fa-bed text-hrs-primary"></i>
                    <div>
                        <div class="fw-semibold">{{ $house->bedrooms }}</div>
                        <div class="small text-muted">Bedrooms</div>
                    </div>
                </div>
                <div class="feature-highlight-item">
                    <i class="fas fa-bath text-hrs-secondary"></i>
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
                    <i class="fas fa-home text-info"></i>
                    <div>
                        <div class="fw-semibold">{{ $house->type }}</div>
                        <div class="small text-muted">House Type</div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            @if($house->description)
            <div class="detail-section mb-4">
                <h5 class="detail-section-title">Description</h5>
                <p class="text-muted lh-lg">{{ $house->description }}</p>
            </div>
            @endif

            <!-- Location Info -->
            <div class="detail-section mb-4">
                <h5 class="detail-section-title"><i class="fas fa-map-marked-alt me-2 text-hrs-primary"></i>Location Details</h5>
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

        <!-- Sidebar -->
        <div class="col-lg-4">

            <!-- Price Card -->
            <div class="sidebar-price-card mb-4">
                <div class="price-card-top">
                    <div class="price-big">Nu. {{ number_format($house->price, 0) }}<span class="price-period">/month</span></div>
                    <div class="price-type-badge">{{ $house->type }}</div>
                </div>
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
                        <form action="{{ route('houses.destroy', $house) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this listing?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                                <i class="fas fa-trash me-1"></i> Delete Listing
                            </button>
                        </form>
                        @endif
                    @else
                    <a href="{{ route('login') }}" class="btn btn-hrs-primary w-100 btn-lg mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i> Login to Enquire
                    </a>
                    @endauth
                @else
                <div class="alert alert-warning text-center mb-0">
                    <i class="fas fa-ban me-1"></i> This house is currently <strong>{{ $house->status }}</strong>.
                </div>
                @endif

                <div class="sidebar-info-list mt-3">
                    <div class="sidebar-info-item">
                        <span><i class="fas fa-calendar-alt text-hrs-primary me-2"></i>Listed</span>
                        <span>{{ $house->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="sidebar-info-item">
                        <span><i class="fas fa-tag text-hrs-secondary me-2"></i>Type</span>
                        <span>{{ $house->type }}</span>
                    </div>
                    <div class="sidebar-info-item">
                        <span><i class="fas fa-check-circle text-success me-2"></i>Status</span>
                        <span class="{{ $house->status === 'available' ? 'text-success' : 'text-warning' }} fw-semibold">
                            {{ ucfirst($house->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Owner Card -->
            <div class="owner-card mb-4">
                <div class="owner-card-header">
                    <i class="fas fa-user-circle me-2"></i> Listed by
                </div>
                <div class="owner-card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar">{{ strtoupper(substr($house->owner->name ?? 'O', 0, 1)) }}</div>
                        <div>
                            <div class="fw-semibold">{{ $house->owner->name ?? 'Owner' }}</div>
                            <div class="small text-muted">Property Owner</div>
                            @if($house->owner->phone)
                            <div class="small">
                                <i class="fas fa-phone text-hrs-primary me-1"></i> {{ $house->owner->phone }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Share -->
            <div class="share-card">
                <div class="small text-muted mb-2 fw-semibold">Share this listing</div>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-sm btn-outline-primary flex-fill">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="btn btn-sm btn-outline-info flex-fill">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary flex-fill" onclick="copyLink()">
                        <i class="fas fa-link"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Houses -->
    @if($relatedHouses->count() > 0)
    <div class="mt-5">
        <h4 class="fw-bold mb-4">More in {{ $house->locationModel->dzongkhag_name ?? $house->location }}</h4>
        <div class="row g-4">
            @foreach($relatedHouses as $related)
            <div class="col-md-4">
                <div class="house-card">
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
                            <i class="fas fa-map-marker-alt text-hrs-primary me-1"></i>{{ $related->location }}
                        </p>
                        <div class="house-card-features">
                            <span><i class="fas fa-bed"></i> {{ $related->bedrooms }}</span>
                            <span><i class="fas fa-bath"></i> {{ $related->bathrooms }}</span>
                            <span class="badge-type">{{ $related->type }}</span>
                        </div>
                        <a href="{{ route('houses.show', $related) }}" class="btn btn-hrs-outline w-100 mt-2 btn-sm">
                            Explore More
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<!-- Rent Modal -->
@auth
@if(Auth::user()->isTenant())
<div class="modal fade" id="rentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-hrs-gradient text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-file-contract me-2"></i> Send Rental Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('rentals.store', $house) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="rent-summary mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Property</span>
                            <span class="fw-semibold">{{ $house->title }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Type</span>
                            <span>{{ $house->type }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Monthly Rent</span>
                            <span class="text-hrs-primary fw-bold">Nu. {{ number_format($house->price, 0) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Preferred Move-in Date <span class="text-danger">*</span></label>
                        <input type="date" name="rental_date" class="form-control"
                               min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Additional Notes</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Any special requirements or questions..."></textarea>
                    </div>
                    <div class="alert alert-info small py-2">
                        <i class="fas fa-info-circle me-1"></i>
                        The owner will review your request and contact you. This does not confirm the rental.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-hrs-primary">
                        <i class="fas fa-paper-plane me-1"></i> Send Rental Request
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
    if (!mainImage) {
        return;
    }

    mainImage.src = imageUrl;
}
</script>
@endpush

@endsection

@push('scripts')
<script>
function copyLink() {
    navigator.clipboard.writeText(window.location.href)
        .then(() => alert('Link copied!'));
}
</script>
@endpush
