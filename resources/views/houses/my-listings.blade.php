@extends('layouts.app')

@section('title', 'My Listings')

@section('content')

<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title">My Listings</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">My Listings</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto">
                <a href="{{ route('houses.create') }}" class="btn btn-hrs-primary">
                    <i class="fas fa-plus me-1"></i> New Listing
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    @if($houses->count() > 0)
    <div class="row g-4">
        @foreach($houses as $house)
        <div class="col-md-6 col-lg-4">
            <div class="house-card h-100">
                <div class="house-card-img-wrapper">
                    <img src="{{ $house->image_url }}" alt="{{ $house->title }}" class="house-card-img">
                    <div class="house-card-badges">
                        <span class="badge-status {{ $house->status === 'available' ? 'badge-available' : ($house->status === 'rented' ? 'badge-rented' : 'badge-pending') }}">
                            {{ ucfirst($house->status) }}
                        </span>
                        <span class="badge-type">{{ $house->type }}</span>
                    </div>
                    <div class="house-card-price">
                        <span>Nu. {{ number_format($house->price, 0) }}</span>
                        <small>/month</small>
                    </div>
                </div>
                <div class="house-card-body">
                    <h6 class="house-card-title">{{ $house->title }}</h6>
                    <p class="house-card-location">
                        <i class="fas fa-map-marker-alt text-hrs-primary me-1"></i>
                        {{ $house->location }}
                    </p>
                    <div class="house-card-features">
                        <span><i class="fas fa-bed"></i> {{ $house->bedrooms }}</span>
                        <span><i class="fas fa-bath"></i> {{ $house->bathrooms }}</span>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <a href="{{ route('houses.show', $house) }}" class="btn btn-hrs-outline flex-fill btn-sm">View</a>
                        <a href="{{ route('houses.edit', $house) }}" class="btn btn-outline-primary flex-fill btn-sm">Edit</a>
                        <form action="{{ route('houses.destroy', $house) }}" method="POST"
                              onsubmit="return confirm('Delete this listing?')" class="flex-fill">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100 btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4 d-flex justify-content-center">
        {{ $houses->links('pagination::bootstrap-5') }}
    </div>
    @else
    <div class="empty-state text-center py-5">
        <i class="fas fa-home fa-4x text-muted mb-3 d-block"></i>
        <h5 class="text-muted">No listings yet</h5>
        <p class="text-muted small">Start by posting your first house for rent.</p>
        <a href="{{ route('houses.create') }}" class="btn btn-hrs-primary mt-2">
            <i class="fas fa-plus me-1"></i> Post a House
        </a>
    </div>
    @endif
</div>

@endsection
