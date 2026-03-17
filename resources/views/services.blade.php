@extends('layouts.app')

@section('title', 'Our Services')

@section('content')

<!-- Page Header -->
<section class="py-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 60%, #2d5a8e 100%); min-height: 220px; display:flex; align-items:center;">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb justify-content-center" style="background:none;">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active text-white">Our Services</li>
                    </ol>
                </nav>
                <h1 class="text-white fw-bold mb-2" style="font-size:2.5rem;">Our <span style="color:#fbbf24;">Services</span></h1>
                <p class="text-white-50 fs-5">Everything you need to rent, list, and manage your property in Bhutan</p>
            </div>
        </div>
    </div>
</section>

<!-- Core Services -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-label">What We Offer</span>
            <h2 class="section-title">Services Tailored for You</h2>
            <p class="section-subtitle">Whether you are a tenant searching for a home or a landlord listing your property, we have you covered.</p>
        </div>

        <div class="row g-4">
            <!-- For Tenants -->
            <div class="col-lg-4 col-md-6">
                <div class="feature-card text-center h-100 p-4">
                    <div class="feature-icon bg-primary-soft mx-auto mb-3" style="width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-search-location text-hrs-primary fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">House Search</h5>
                    <p class="text-muted small lh-lg">Browse thousands of verified rental listings across all Dzongkhags. Filter by location, type, price, and more to find your perfect home.</p>
                    <a href="{{ route('houses.index') }}" class="btn btn-hrs-primary btn-sm mt-2 px-4">
                        <i class="fas fa-compass me-1"></i> Explore Now
                    </a>
                </div>
            </div>

            <!-- For Owners -->
            <div class="col-lg-4 col-md-6">
                <div class="feature-card text-center h-100 p-4">
                    <div class="feature-icon bg-success-soft mx-auto mb-3" style="width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-home text-success fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">List Your Property</h5>
                    <p class="text-muted small lh-lg">Are you a landlord? List your house, apartment, or villa in minutes. Reach thousands of verified tenants looking for homes across Bhutan.</p>
                    @auth
                        @if(Auth::user()->isOwner() || Auth::user()->isAdmin())
                        <a href="{{ route('houses.create') }}" class="btn btn-success btn-sm mt-2 px-4">
                            <i class="fas fa-plus me-1"></i> Post a House
                        </a>
                        @endif
                    @else
                    <a href="{{ route('register') }}" class="btn btn-success btn-sm mt-2 px-4">
                        <i class="fas fa-user-plus me-1"></i> Get Started
                    </a>
                    @endauth
                </div>
            </div>

            <!-- Rental Management -->
            <div class="col-lg-4 col-md-6">
                <div class="feature-card text-center h-100 p-4">
                    <div class="feature-icon bg-warning-soft mx-auto mb-3" style="width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-file-contract text-warning fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Rental Management</h5>
                    <p class="text-muted small lh-lg">Easily track and manage your active rental agreements. Tenants can view all their currently rented properties from one place.</p>
                    @auth
                    <a href="{{ route('rentals.my-rentals') }}" class="btn btn-warning btn-sm mt-2 px-4 text-white">
                        <i class="fas fa-list me-1"></i> My Rentals
                    </a>
                    @else
                    <a href="{{ route('login') }}" class="btn btn-warning btn-sm mt-2 px-4 text-white">
                        <i class="fas fa-sign-in-alt me-1"></i> Login to Access
                    </a>
                    @endauth
                </div>
            </div>

            <!-- Verified Listings -->
            <div class="col-lg-4 col-md-6">
                <div class="feature-card text-center h-100 p-4">
                    <div class="feature-icon bg-info-soft mx-auto mb-3" style="width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-shield-alt text-info fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Verified Listings</h5>
                    <p class="text-muted small lh-lg">Every property listed on HRS Bhutan is reviewed and verified by our team to ensure accuracy, safety, and authenticity before going live.</p>
                </div>
            </div>

            <!-- Location Coverage -->
            <div class="col-lg-4 col-md-6">
                <div class="feature-card text-center h-100 p-4">
                    <div class="feature-icon mx-auto mb-3" style="width:72px;height:72px;border-radius:50%;background:#f3e8ff;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-map-marked-alt fs-3" style="color:#7b2d8b;"></i>
                    </div>
                    <h5 class="fw-bold mb-2">All 20 Dzongkhags</h5>
                    <p class="text-muted small lh-lg">From Thimphu to Trashigang, our listings cover all 20 Dzongkhags of Bhutan so you can find a home wherever you need to be.</p>
                </div>
            </div>

            <!-- 24/7 Support -->
            <div class="col-lg-4 col-md-6">
                <div class="feature-card text-center h-100 p-4">
                    <div class="feature-icon mx-auto mb-3" style="width:72px;height:72px;border-radius:50%;background:#ffe8e8;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-headset fs-3" style="color:#c0392b;"></i>
                    </div>
                    <h5 class="fw-bold mb-2">24/7 Support</h5>
                    <p class="text-muted small lh-lg">Our dedicated support team is available in both Dzongkha and English to assist you anytime — from listing a property to signing a rental agreement.</p>
                    <a href="{{ route('contact') }}" class="btn btn-outline-danger btn-sm mt-2 px-4">
                        <i class="fas fa-envelope me-1"></i> Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5 bg-light-warm">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-label">Simple Steps</span>
            <h2 class="section-title">How It Works</h2>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-lg-3 col-md-6 text-center">
                <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center text-white fw-bold fs-4"
                     style="width:64px;height:64px;background:#1e3a5f;">1</div>
                <h6 class="fw-bold">Create an Account</h6>
                <p class="text-muted small">Register as a tenant or owner in under a minute — completely free.</p>
            </div>
            <div class="col-lg-3 col-md-6 text-center">
                <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center text-white fw-bold fs-4"
                     style="width:64px;height:64px;background:#f59e0b;">2</div>
                <h6 class="fw-bold">Search or List</h6>
                <p class="text-muted small">Browse available houses or post your own property with photos and details.</p>
            </div>
            <div class="col-lg-3 col-md-6 text-center">
                <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center text-white fw-bold fs-4"
                     style="width:64px;height:64px;background:#1e3a5f;">3</div>
                <h6 class="fw-bold">Connect &amp; Agree</h6>
                <p class="text-muted small">Tenant and landlord connect through the platform and finalize the rental agreement.</p>
            </div>
            <div class="col-lg-3 col-md-6 text-center">
                <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center text-white fw-bold fs-4"
                     style="width:64px;height:64px;background:#f59e0b;">4</div>
                <h6 class="fw-bold">Move In!</h6>
                <p class="text-muted small">Once confirmed, the tenant moves into their new home in Bhutan hassle-free.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section py-5">
    <div class="container">
        <div class="cta-card">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="text-white fw-bold mb-2">Ready to Get Started?</h3>
                    <p class="text-white-50 mb-0">Join thousands of satisfied tenants and landlords across the Kingdom of Bhutan.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="{{ route('houses.index') }}" class="btn btn-light btn-lg px-4 fw-semibold me-2">
                        <i class="fas fa-compass me-1"></i> Explore Now
                    </a>
                    @guest
                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-user-plus me-1"></i> Register
                    </a>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
