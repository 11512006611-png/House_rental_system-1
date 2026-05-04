<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'House Rental System') | HRS Bhutan</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark hrs-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <div class="brand-icon">
                    <i class="fas fa-home"></i>
                </div>
                <div>
                    <span class="brand-title">HRS</span>
                    <span class="brand-sub d-none d-md-inline"> Bhutan</span>
                </div>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto ms-3">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="fas fa-house-chimney me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">
                            <i class="fas fa-info-circle me-1"></i> About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('services') ? 'active' : '' }}" href="{{ route('services') }}">
                            <i class="fas fa-concierge-bell me-1"></i> Our Services
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">
                            <i class="fas fa-envelope me-1"></i> Contact Us
                        </a>
                    </li>
                    @auth
                        @if(Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-gauge-high me-1"></i> Admin Dashboard
                            </a>
                        </li>
                        @endif
                        @if(Auth::user()->isOwner())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('owner/*') ? 'active' : '' }}" href="{{ route('owner.dashboard') }}">
                                <i class="fas fa-chart-pie me-1"></i> Owner Dashboard
                            </a>
                        </li>
                        @endif
                        @if(Auth::user()->isTenant())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('tenant/*') ? 'active' : '' }}" href="{{ route('tenant.dashboard') }}">
                                <i class="fas fa-gauge me-1"></i> Tenant Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.maintenance.*') ? 'active' : '' }}" href="{{ route('tenant.maintenance.index') }}">
                                <i class="fas fa-screwdriver-wrench me-1"></i> Maintenance
                            </a>
                        </li>
                        @endif
                        @if(Auth::user()->isOwner() || Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('houses.create') }}">
                                <i class="fas fa-plus-circle me-1"></i> Post House
                            </a>
                        </li>
                        @endif
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-hrs-primary btn-sm px-3" href="{{ route('register') }}">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </a>
                        </li>
                    @else
                        @php $currentUser = Auth::user(); @endphp
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                                @if($currentUser->profile_image_url)
                                    <img src="{{ $currentUser->profile_image_url }}" alt="User avatar" class="user-avatar-sm" style="object-fit:cover;">
                                @else
                                    <div class="user-avatar-sm">{{ strtoupper(substr($currentUser->username ?: $currentUser->name, 0, 1)) }}</div>
                                @endif
                                <span class="d-none d-lg-inline">{{ $currentUser->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                <li class="dropdown-header small text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    {{ ucfirst($currentUser->role) }}
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.show') }}">
                                        <i class="fas fa-id-card me-2 text-primary"></i> My Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fas fa-pen me-2 text-primary"></i> Edit Profile
                                    </a>
                                </li>
                                @if(Auth::user()->isOwner() || Auth::user()->isAdmin())
                                <li>
                                    <a class="dropdown-item" href="{{ route('houses.my-listings') }}">
                                        <i class="fas fa-list me-2 text-primary"></i> My Listings
                                    </a>
                                </li>
                                @endif
                                @if(Auth::user()->isTenant())
                                <li>
                                    <a class="dropdown-item" href="{{ route('rentals.my-rentals') }}">
                                        <i class="fas fa-file-contract me-2 text-primary"></i> My Rentals
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('tenant.maintenance.index') }}">
                                        <i class="fas fa-screwdriver-wrench me-2 text-primary"></i> Maintenance Requests
                                    </a>
                                </li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show m-0 rounded-0 border-0" role="alert">
        <div class="container d-flex align-items-center gap-2">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show m-0 rounded-0 border-0" role="alert">
        <div class="container d-flex align-items-center gap-2">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="hrs-footer mt-5">
        <div class="container">
            <div class="row g-4 py-5">
                <!-- Brand -->
                <div class="col-lg-3 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="brand-icon brand-icon-sm">
                            <i class="fas fa-home"></i>
                        </div>
                        <h5 class="text-white mb-0 fw-bold">HRS Bhutan</h5>
                    </div>
                    <p class="text-muted small lh-lg">
                        House Rental System — connecting landlords and tenants across the Kingdom of Bhutan.
                        Find your perfect home in Thimphu, Paro, Punakha, and beyond.
                    </p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white fw-semibold mb-3 text-uppercase small ls-wide">Quick Links</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="{{ route('home') }}"><i class="fas fa-chevron-right me-1"></i> Home</a></li>
                        <li><a href="{{ route('houses.index') }}"><i class="fas fa-chevron-right me-1"></i> Search Houses</a></li>
                        @guest
                        <li><a href="{{ route('register') }}"><i class="fas fa-chevron-right me-1"></i> Register</a></li>
                        <li><a href="{{ route('login') }}"><i class="fas fa-chevron-right me-1"></i> Login</a></li>
                        @endguest
                    </ul>
                </div>

                <!-- About Us -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white fw-semibold mb-3 text-uppercase small">About Us</h6>
                    <p class="text-muted small lh-lg mb-2">
                        HRS Bhutan connects landlords &amp; tenants across all 20 Dzongkhags — making house hunting simple and trusted.
                    </p>
                    <a href="{{ route('about') }}" class="footer-read-more small">
                        <i class="fas fa-arrow-right me-1"></i> Read More
                    </a>
                </div>

                <!-- Our Services -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white fw-semibold mb-3 text-uppercase small">Our Services</h6>
                    <p class="text-muted small lh-lg mb-2">
                        Browse listings, post your property, manage rentals, and get verified — all in one platform.
                    </p>
                    <a href="{{ route('services') }}" class="footer-read-more small">
                        <i class="fas fa-arrow-right me-1"></i> View All Services
                    </a>
                </div>

                <!-- Contact Us -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-white fw-semibold mb-3 text-uppercase small">Contact Us</h6>
                    <ul class="list-unstyled footer-contact mb-3">
                        <li><i class="fas fa-map-marker-alt me-2 text-hrs-secondary"></i> Thimphu, Bhutan</li>
                        <li><i class="fas fa-phone me-2 text-hrs-secondary"></i> +975 2 123456</li>
                        <li><i class="fas fa-envelope me-2 text-hrs-secondary"></i> info@hrsbhutan.bt</li>
                    </ul>
                    <a href="{{ route('contact') }}" class="footer-read-more small">
                        <i class="fas fa-envelope me-1"></i> Get in Touch
                    </a>
                </div>
            </div>

            <hr class="border-secondary">
            <div class="row py-3">
                <div class="col-12 text-center text-md-start">
                    <small class="text-muted">&copy; {{ date('Y') }} House Rental System Bhutan. All rights reserved.</small>
                </div>
            </div>
        </div>
    </footer>



    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
