@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')

<!-- Page Header -->
<section class="py-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 60%, #2d5a8e 100%); min-height: 220px; display:flex; align-items:center;">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb justify-content-center" style="background:none;">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active text-white">Contact Us</li>
                    </ol>
                </nav>
                <h1 class="text-white fw-bold mb-2" style="font-size:2.5rem;">Get in <span style="color:#fbbf24;">Touch</span></h1>
                <p class="text-white-50 fs-5">We'd love to hear from you. Our team is always here to help.</p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Cards -->
<section class="py-5 bg-light-warm">
    <div class="container">
        <div class="row g-4 justify-content-center mb-5">
            <div class="col-lg-3 col-md-6">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon bg-primary-soft mx-auto">
                        <i class="fas fa-map-marker-alt text-hrs-primary fs-4"></i>
                    </div>
                    <h5 class="feature-title mt-3">Our Office</h5>
                    <p class="feature-text mb-0">Norzin Lam, Thimphu<br>Kingdom of Bhutan, 11001</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon bg-success-soft mx-auto">
                        <i class="fas fa-phone-alt text-success fs-4"></i>
                    </div>
                    <h5 class="feature-title mt-3">Phone</h5>
                    <p class="feature-text mb-0">
                        <a href="tel:+97521234567" class="text-decoration-none text-muted">+975 2 123456</a><br>
                        <a href="tel:+97517654321" class="text-decoration-none text-muted">+975 17 654321</a>
                    </p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon bg-warning-soft mx-auto">
                        <i class="fas fa-envelope text-warning fs-4"></i>
                    </div>
                    <h5 class="feature-title mt-3">Email</h5>
                    <p class="feature-text mb-0">
                        <a href="mailto:info@hrsbhutan.bt" class="text-decoration-none text-muted">info@hrsbhutan.bt</a><br>
                        <a href="mailto:support@hrsbhutan.bt" class="text-decoration-none text-muted">support@hrsbhutan.bt</a>
                    </p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon bg-info-soft mx-auto">
                        <i class="fas fa-clock text-info fs-4"></i>
                    </div>
                    <h5 class="feature-title mt-3">Working Hours</h5>
                    <p class="feature-text mb-0">Mon – Fri: 9AM – 5PM<br>Sat: 9AM – 1PM</p>
                </div>
            </div>
        </div>

        <!-- Contact Form & Map -->
        <div class="row g-5 align-items-start">
            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5">
                    <h4 class="fw-bold mb-1">Send Us a Message</h4>
                    <p class="text-muted small mb-4">Fill in the form below and we will get back to you within 24 hours.</p>

                    @if(session('contact_success'))
                    <div class="alert alert-success rounded-3">
                        <i class="fas fa-check-circle me-2"></i> {{ session('contact_success') }}
                    </div>
                    @endif

                    <form action="{{ route('contact.send') }}" method="POST" novalidate>
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Your full name" value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       placeholder="you@example.com" value="{{ old('email') }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Phone Number</label>
                                <input type="text" name="phone" class="form-control"
                                       placeholder="+975 17 000000" value="{{ old('phone') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Subject <span class="text-danger">*</span></label>
                                <select name="subject" class="form-select @error('subject') is-invalid @enderror" required>
                                    <option value="">— Select a subject —</option>
                                    <option value="General Inquiry" {{ old('subject') === 'General Inquiry' ? 'selected' : '' }}>General Inquiry</option>
                                    <option value="Listing Issue" {{ old('subject') === 'Listing Issue' ? 'selected' : '' }}>Listing Issue</option>
                                    <option value="Rental Support" {{ old('subject') === 'Rental Support' ? 'selected' : '' }}>Rental Support</option>
                                    <option value="Report a Problem" {{ old('subject') === 'Report a Problem' ? 'selected' : '' }}>Report a Problem</option>
                                    <option value="Partnership" {{ old('subject') === 'Partnership' ? 'selected' : '' }}>Partnership</option>
                                    <option value="Other" {{ old('subject') === 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Message <span class="text-danger">*</span></label>
                                <textarea name="message" rows="5" class="form-control @error('message') is-invalid @enderror"
                                          placeholder="Write your message here..." required>{{ old('message') }}</textarea>
                                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 mt-2">
                                <button type="submit" class="btn btn-hrs-primary btn-lg px-5 fw-semibold">
                                    <i class="fas fa-paper-plane me-2"></i> Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, #1e3a5f, #0f172a);">
                    <h5 class="text-white fw-bold mb-3"><i class="fas fa-headset me-2 text-hrs-secondary"></i> Quick Support</h5>
                    <p class="text-white-50 small lh-lg mb-3">For urgent queries, our support team is available via phone or WhatsApp during office hours.</p>
                    <a href="tel:+97521234567" class="btn btn-outline-light btn-sm px-4">
                        <i class="fas fa-phone me-2"></i> Call Now
                    </a>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-share-alt me-2 text-hrs-primary"></i> Follow Us</h5>
                    <p class="text-muted small mb-3">Stay connected with us on social media for the latest listings and updates.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 mt-4 bg-light-warm">
                    <h5 class="fw-bold mb-3"><i class="fas fa-question-circle me-2 text-hrs-primary"></i> Have a Question?</h5>
                    <p class="text-muted small mb-3">Browse our available listings and find your dream home today.</p>
                    <a href="{{ route('houses.index') }}" class="btn btn-hrs-primary btn-sm px-4">
                        <i class="fas fa-compass me-2"></i> Explore Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
