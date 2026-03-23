@extends('layouts.app')

@section('title', 'Find Your Perfect Home in Bhutan')

@section('content')

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-overlay"></div>
    <div class="container position-relative hero-content">
        <div class="row justify-content-center text-center">
            <div class="col-lg-9">
                <div class="hero-badge mb-3">
                    <img src="https://flagcdn.com/20x15/bt.png" alt="Bhutan"> &nbsp;Kingdom of Bhutan
                </div>
                <h1 class="hero-title">Find Your Perfect Home<br><span class="text-hrs-highlight">in Bhutan</span></h1>
                <p class="hero-subtitle">Discover comfortable houses, apartments, and villas across all Dzongkhags. Trusted by thousands of families in the Land of the Thunder Dragon.</p>

                <!-- Hero CTA Buttons -->
                <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
                    <a href="{{ route('houses.index') }}" class="btn btn-hrs-primary btn-lg px-5 py-3 fw-semibold shadow-lg">
                        <i class="fas fa-compass me-2"></i> Explore Now
                    </a>
                    @guest
                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg px-5 py-3 fw-semibold">
                        <i class="fas fa-user-plus me-2"></i> List Your Place
                    </a>
                    @endguest
                </div>

                <!-- Stats -->
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="stat-num">{{ $totalHouses }}+</span>
                        <span class="stat-label">Available Houses</span>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat">
                        <span class="stat-num">20+</span>
                        <span class="stat-label">Dzongkhags</span>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat">
                        <span class="stat-num">100%</span>
                        <span class="stat-label">Verified Listings</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== What We Offer ===== -->
<section class="benefits-section">
    <div class="container">

        <!-- Section Header -->
        <div class="text-center mb-5">
            <span class="benefits-label">— What We Serve —</span>
            <h2 class="benefits-title">The Benefit From<br>Our Service</h2>
        </div>

        <div class="row g-4 justify-content-center">

            <!-- Card 1 -->
            <div class="col-lg-3 col-md-6">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-search-location"></i>
                    </div>
                    <h5 class="benefit-card-title">Smart Search</h5>
                    <p class="benefit-card-text">
                        Filter by Dzongkhag, house type, bedrooms, and price range to find exactly what you need — fast.
                    </p>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="col-lg-3 col-md-6">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5 class="benefit-card-title">Verified Listings</h5>
                    <p class="benefit-card-text">
                        Every property is reviewed and verified so you can browse with confidence and peace of mind.
                    </p>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="col-lg-3 col-md-6">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <h5 class="benefit-card-title">Easy Rental</h5>
                    <p class="benefit-card-text">
                        Request a rental, track your application, and manage your agreement — all in one place.
                    </p>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="col-lg-3 col-md-6">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5 class="benefit-card-title">24 / 7 Support</h5>
                    <p class="benefit-card-text">
                        Our dedicated support team is ready to assist landlords and tenants across all Dzongkhags.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ===== Best Experience ===== -->
<section class="experience-section">
    <div class="container">
        <div class="row align-items-center g-5">

            <!-- Image -->
            <div class="col-lg-5">
                <div class="experience-img-wrap">
                    <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=600&auto=format&fit=crop" alt="Interior" class="experience-img">
                    <div class="experience-img-dot experience-img-dot--tl"></div>
                    <div class="experience-img-dot experience-img-dot--br"></div>
                </div>
            </div>

            <!-- Content -->
            <div class="col-lg-7">
                <h2 class="experience-title">We Provide You The<br><span>Best Experience</span></h2>
                <p class="experience-text">
                    HRS Bhutan has been trusted by thousands of families and landlords across all 20 Dzongkhags.
                    Our platform simplifies every step — from discovering the perfect home to signing a rental agreement.
                    We combine local knowledge with a modern platform so you always get the best.
                </p>

                <!-- Stats row -->
                <div class="experience-stats">
                    <div class="experience-stat">
                        <span class="exp-num">5+</span>
                        <span class="exp-label">Years<br>Experience</span>
                    </div>
                    <div class="experience-stat">
                        <span class="exp-num">{{ $totalHouses }}+</span>
                        <span class="exp-label">Houses<br>Listed</span>
                    </div>
                    <div class="experience-stat">
                        <span class="exp-num">20+</span>
                        <span class="exp-label">Dzongkhags<br>Covered</span>
                    </div>
                </div>

                <a href="{{ route('about') }}" class="btn-experience mt-4 d-inline-flex align-items-center gap-2">
                    See More <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ===== Testimonials ===== -->
<section class="testimonials-section">
    <div class="container">
        <div class="mb-5">
            <span class="testi-label">What</span>
            <h2 class="testi-title">People Say About Us?</h2>
        </div>

        @php
            $slides = $testimonialItems->chunk(3);
            $avatarColors = ['#1e90be', '#7c3aed', '#f59e0b', '#10b981', '#ef4444', '#2563eb'];
        @endphp

        @if($slides->isNotEmpty())
            <div id="testiCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @foreach($slides as $slideIndex => $slideReviews)
                        <div class="carousel-item {{ $slideIndex === 0 ? 'active' : '' }}">
                            <div class="row g-4">
                                @foreach($slideReviews as $reviewIndex => $review)
                                    @php
                                        $name = $review->name ?? 'Anonymous Tenant';
                                        $initial = strtoupper(substr($name, 0, 1));
                                        $location = $review->location ?: 'Bhutan';
                                        $avatarColor = $review->avatarColor ?: $avatarColors[($slideIndex * 3 + $reviewIndex) % count($avatarColors)];
                                    @endphp
                                    <div class="col-lg-4 col-md-6 {{ $reviewIndex === 2 ? 'd-none d-lg-block' : ($reviewIndex === 1 ? 'd-none d-md-block' : '') }}">
                                        <div class="testi-card">
                                            <div class="testi-quote"><i class="fas fa-quote-left"></i></div>
                                            <h6 class="testi-heading">{{ $review->title }}</h6>
                                            <p class="testi-body">{{ $review->message }}</p>
                                            <div class="testi-author">
                                                <div class="testi-avatar" style="background:{{ $avatarColor }};">{{ $initial }}</div>
                                                <div>
                                                    <div class="testi-name">{{ $name }}</div>
                                                    <div class="testi-role">Tenant — {{ $location }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($testimonialItems->count() > 3)
                    <div class="testi-controls mt-4 d-flex align-items-center gap-3">
                        <button class="testi-arrow" data-bs-target="#testiCarousel" data-bs-slide="prev">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="testi-arrow testi-arrow--active" data-bs-target="#testiCarousel" data-bs-slide="next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                @endif
            </div>
        @else
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="testi-card">
                        <div class="testi-quote"><i class="fas fa-quote-left"></i></div>
                        <h6 class="testi-heading">No tenant reviews yet</h6>
                        <p class="testi-body">Be the first tenant to share your experience from the Tenant Dashboard.</p>
                        <div class="testi-author">
                            <div class="testi-avatar" style="background:#1e90be;">H</div>
                            <div>
                                <div class="testi-name">HRS Bhutan</div>
                                <div class="testi-role">Community Updates</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>

@endsection
