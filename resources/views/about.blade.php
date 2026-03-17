@extends('layouts.app')

@section('title', 'About Us')

@section('content')

<!-- Page Header -->
<section class="page-header-section py-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 60%, #2d5a8e 100%); min-height: 220px; display:flex; align-items:center;">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb justify-content-center" style="background:none;">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active text-white">About Us</li>
                    </ol>
                </nav>
                <h1 class="text-white fw-bold mb-2" style="font-size:2.5rem;">About <span style="color:#fbbf24;">HRS Bhutan</span></h1>
                <p class="text-white-50 fs-5">Your trusted house rental partner across the Kingdom of Bhutan</p>
            </div>
        </div>
    </div>
</section>

<!-- Mission Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="section-label">Our Story</span>
                <h2 class="section-title mt-1 mb-3">Connecting Homes &amp; Families Across Bhutan</h2>
                <p class="text-muted lh-lg mb-3">
                    HRS Bhutan was founded with a simple yet powerful mission — to make house hunting in Bhutan transparent, accessible, and hassle-free. Whether you are a tenant searching for your next home or a landlord looking for trusted tenants, we are here to bridge that gap.
                </p>
                <p class="text-muted lh-lg mb-4">
                    From the bustling streets of Thimphu to the serene valleys of Punakha, our platform covers listings across all 20 Dzongkhags, helping thousands of families find their perfect home in the Land of the Thunder Dragon.
                </p>
                <div class="d-flex gap-4">
                    <div class="text-center">
                        <div class="fw-bold fs-2 text-hrs-primary">500+</div>
                        <div class="text-muted small">Active Listings</div>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold fs-2 text-hrs-primary">20+</div>
                        <div class="text-muted small">Dzongkhags</div>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold fs-2 text-hrs-primary">1K+</div>
                        <div class="text-muted small">Happy Families</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="rounded-4 overflow-hidden shadow-lg" style="height:380px; background: linear-gradient(135deg,#1e3a5f,#2d5a8e); display:flex; align-items:center; justify-content:center;">
                    <div class="text-center text-white p-4">
                        <i class="fas fa-home fa-5x mb-4 opacity-75"></i>
                        <h4 class="fw-bold">Druk Yul's #1 Rental Platform</h4>
                        <p class="text-white-50">Serving the Kingdom since 2024</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-5 bg-light-warm">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-label">What Drives Us</span>
            <h2 class="section-title">Our Core Values</h2>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon bg-primary-soft mx-auto">
                        <i class="fas fa-shield-alt text-hrs-primary fs-4"></i>
                    </div>
                    <h5 class="feature-title mt-3">Trust &amp; Safety</h5>
                    <p class="feature-text">Every listing is verified by our dedicated team to ensure accuracy and authenticity before going live.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon bg-success-soft mx-auto">
                        <i class="fas fa-handshake text-success fs-4"></i>
                    </div>
                    <h5 class="feature-title mt-3">Transparency</h5>
                    <p class="feature-text">Clear pricing, honest descriptions, and no hidden fees — what you see is what you get.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon bg-warning-soft mx-auto">
                        <i class="fas fa-users text-warning fs-4"></i>
                    </div>
                    <h5 class="feature-title mt-3">Community First</h5>
                    <p class="feature-text">We are built for Bhutanese families, by Bhutanese. We understand your needs and values.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon bg-info-soft mx-auto">
                        <i class="fas fa-headset text-info fs-4"></i>
                    </div>
                    <h5 class="feature-title mt-3">24/7 Support</h5>
                    <p class="feature-text">Our support team speaks both Dzongkha and English to assist you any time you need.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-label">The People Behind HRS</span>
            <h2 class="section-title">Meet Our Team</h2>
        </div>
        <div class="row g-4 justify-content-center">
            @php
                $team = [
                    ['name' => 'Karma Wangchuk', 'role' => 'Founder & CEO', 'icon' => 'fa-user-tie', 'color' => '#1e3a5f'],
                    ['name' => 'Sonam Dema', 'role' => 'Head of Operations', 'icon' => 'fa-user-cog', 'color' => '#d97706'],
                    ['name' => 'Tenzin Phuntsho', 'role' => 'Lead Developer', 'icon' => 'fa-laptop-code', 'color' => '#0ea5e9'],
                    ['name' => 'Pema Yangzom', 'role' => 'Customer Support', 'icon' => 'fa-headset', 'color' => '#10b981'],
                ];
            @endphp
            @foreach($team as $member)
            <div class="col-lg-3 col-md-6">
                <div class="text-center p-4 rounded-4 border h-100" style="transition: box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 8px 32px rgba(0,0,0,0.12)'" onmouseout="this.style.boxShadow='none'">
                    <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center text-white fw-bold fs-3"
                         style="width:80px;height:80px;background:{{ $member['color'] }};">
                        <i class="fas {{ $member['icon'] }}"></i>
                    </div>
                    <h6 class="fw-bold mb-1">{{ $member['name'] }}</h6>
                    <p class="text-muted small mb-0">{{ $member['role'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section py-5">
    <div class="container">
        <div class="cta-card">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="text-white fw-bold mb-2">Ready to Find Your Perfect Home?</h3>
                    <p class="text-white-50 mb-0">Join thousands of satisfied tenants and landlords across Bhutan today.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="{{ route('houses.index') }}" class="btn btn-light btn-lg px-4 fw-semibold">
                        <i class="fas fa-compass me-2"></i> Explore Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
