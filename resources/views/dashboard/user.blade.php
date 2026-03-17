@extends('layouts.app')

@section('title', 'User Dashboard')

@section('content')
<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">User Dashboard</h1>
            <p class="text-muted mb-0">Welcome, {{ Auth::user()->name }}. Continue managing your listings from here.</p>
        </div>
        <a href="{{ route('houses.my-listings') }}" class="btn btn-hrs-primary">
            <i class="fas fa-list me-2"></i>My Listings
        </a>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total Listings</p>
                    <h2 class="mb-0">{{ $myListings }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Occupied Listings</p>
                    <h2 class="mb-0">{{ $occupiedListings }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Pending Approval</p>
                    <h2 class="mb-0">{{ $pendingListings }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h3 class="h5">Quick Actions</h3>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <a href="{{ route('houses.create') }}" class="btn btn-outline-primary btn-sm">Post New House</a>
                <a href="{{ route('houses.my-listings') }}" class="btn btn-outline-primary btn-sm">Manage Listings</a>
                <a href="{{ route('owner.dashboard') }}" class="btn btn-outline-primary btn-sm">Advanced Owner Dashboard</a>
            </div>
        </div>
    </div>
</div>
@endsection
