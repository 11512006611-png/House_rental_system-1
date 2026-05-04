@extends('layouts.app')

@section('title', 'Forbidden')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5 px-4">
                    <div class="mb-3" style="font-size:3rem;color:#dc2626;">
                        <i class="fas fa-ban"></i>
                    </div>
                    <h1 class="h3 fw-bold mb-2">403 - Action not allowed</h1>
                    <p class="text-muted mb-4">
                        This property is now managed by admin. Owners cannot edit or delete it after approval.
                    </p>
                    <a href="{{ url()->previous() ?: route('home') }}" class="btn btn-primary me-2">
                        Go Back
                    </a>
                    <a href="{{ route('houses.my-listings') }}" class="btn btn-outline-secondary">
                        My Listings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
