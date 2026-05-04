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
                <p class="text-white-50 fs-5">Admin-controlled rentals for secure owner-tenant coordination across Bhutan</p>
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
                <h2 class="section-title mt-1 mb-3">Admin-Managed Rentals, Simplified for Everyone</h2>
                <p class="text-muted lh-lg mb-3">
                    HRS Bhutan is an admin-controlled house rental platform where the admin manages the full operation: property approval, tenant verification, lease agreement processing, payments, and inspections. Every listing is reviewed and approved before it is visible to tenants.
                </p>
                <p class="text-muted lh-lg mb-4">
                    Property owners mainly focus on listing houses and receiving secure payouts, while tenants can search, book, and rent houses easily through a guided workflow. The admin ensures smooth and secure communication between owners and tenants from first inquiry to move-in.
                </p>
                <div class="d-flex gap-4">
                    <div class="text-center">
                        <div class="fw-bold fs-2 text-hrs-primary">500+</div>
                        <div class="text-muted small">Active Listings</div>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold fs-2 text-hrs-primary">20</div>
                        <div class="text-muted small">Dzongkhags</div>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold fs-2 text-hrs-primary">1K+</div>
                        <div class="text-muted small">Happy Families</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="rounded-4 overflow-hidden shadow-lg" style="height:380px;">
                    <img src="{{ asset('images/experience-bhutan-night.jpg') }}" alt="Bhutan City View" class="w-100 h-100" style="object-fit: cover; object-position: center;" loading="lazy" decoding="async">
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
                    <h5 class="feature-title mt-3">Verified Workflow</h5>
                    <p class="feature-text">Admin checks every listing, tenant profile, and key step to keep rentals safe, compliant, and reliable.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon bg-success-soft mx-auto">
                        <i class="fas fa-handshake text-success fs-4"></i>
                    </div>
                    <h5 class="feature-title mt-3">Centralized Control</h5>
                    <p class="feature-text">Approvals, lease handling, payments, and inspections are managed in one place for full visibility.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon bg-warning-soft mx-auto">
                        <i class="fas fa-users text-warning fs-4"></i>
                    </div>
                    <h5 class="feature-title mt-3">Owner Friendly</h5>
                    <p class="feature-text">Owners list properties with confidence while the platform and admin handle operational complexity.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon bg-info-soft mx-auto">
                        <i class="fas fa-headset text-info fs-4"></i>
                    </div>
                    <h5 class="feature-title mt-3">Tenant Convenience</h5>
                    <p class="feature-text">Tenants can discover, book, and rent homes through a simple, secure, and well-supported process.</p>
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
                         style="width:80px;height:80px;background:#0ea5e9;">
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
                    <h3 class="text-white fw-bold mb-2">Ready to List or Rent with Confidence?</h3>
                    <p class="text-white-50 mb-0">Join a secure admin-managed rental ecosystem built for owners and tenants across Bhutan.</p>
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
