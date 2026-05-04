@extends('layouts.tenant')

@section('title', 'Tenant Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('styles')
<style>
/* ── Dashboard hero ──────────────────────────────────────────────── */
.td-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 60%, #2d5a8e 100%);
    padding: 2.5rem 0 3.5rem;
    position: relative;
    overflow: hidden;
}
.td-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}
.td-hero-avatar {
    width: 60px; height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; font-weight: 700; color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 15px rgba(245,158,11,.4);
}
.td-hero-avatar-img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,.35);
    flex-shrink: 0;
    box-shadow: 0 4px 15px rgba(15,23,42,.35);
}

/* ── Stat cards ──────────────────────────────────────────────────── */
.td-stat {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
    transition: transform .2s, box-shadow .2s;
}
.td-stat:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.12); }
.td-stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
}
.td-stat-value { font-size: 1.8rem; font-weight: 700; line-height: 1.1; }
.td-stat-label { font-size: .75rem; font-weight: 500; text-transform: uppercase; letter-spacing: .05em; }

/* ── Notification items ──────────────────────────────────────────── */
.td-notif-item {
    display: flex; align-items: flex-start; gap: .75rem;
    padding: .85rem 1rem;
    border-radius: 12px;
    background: #f8fafc;
    border-left: 4px solid transparent;
    transition: background .15s;
}
.td-notif-item:hover { background: #f1f5f9; }
.td-notif-item.success { border-color: #10b981; }
.td-notif-item.info    { border-color: #3b82f6; }
.td-notif-item.danger  { border-color: #ef4444; }
.td-notif-item.warning { border-color: #f59e0b; }
.td-notif-icon {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; flex-shrink: 0;
}

/* ── Rental request card ─────────────────────────────────────────── */
.td-rental-card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    transition: box-shadow .2s;
}
.td-rental-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); }
.td-rental-header {
    background: #f8fafc;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
}

/* ── 5-step stepper ──────────────────────────────────────────────── */
.td-stepper {
    display: flex;
    align-items: flex-start;
    position: relative;
    padding: .75rem 0 .5rem;
}
.td-stepper::before {
    content: '';
    position: absolute;
    top: 1.35rem;
    left: calc(10% + 16px);
    right: calc(10% + 16px);
    height: 2px;
    background: #e2e8f0;
    z-index: 0;
}
.td-step {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
}
.td-step-dot {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem; font-weight: 700;
    border: 2px solid #e2e8f0;
    background: #fff;
    color: #94a3b8;
    transition: all .25s;
}
.td-step.done   .td-step-dot { background: #10b981; border-color: #10b981; color: #fff; }
.td-step.active .td-step-dot { background: #f59e0b; border-color: #f59e0b; color: #fff; box-shadow: 0 0 0 4px rgba(245,158,11,.2); }
.td-step.failed .td-step-dot { background: #ef4444; border-color: #ef4444; color: #fff; }
.td-step-label {
    font-size: .65rem;
    font-weight: 600;
    text-align: center;
    margin-top: .4rem;
    color: #94a3b8;
    line-height: 1.2;
    max-width: 64px;
}
.td-step.done   .td-step-label,
.td-step.active .td-step-label { color: #475569; }

/* ── Process timeline ────────────────────────────────────────────── */
.td-timeline { list-style: none; padding: 0; margin: 0; }
.td-timeline li {
    display: flex; gap: 1rem; padding-bottom: 1.25rem;
    position: relative;
}
.td-timeline li:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 19px; top: 38px;
    width: 2px;
    height: calc(100% - 14px);
    background: #e2e8f0;
}
.td-tl-num {
    width: 38px; height: 38px; border-radius: 50%;
    background: linear-gradient(135deg, #1e3a5f, #2d5a8e);
    color: #fff; font-weight: 700; font-size: .85rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
</style>
@endpush

@section('content')

{{-- ── Hero Header ──────────────────────────────────────────────────── --}}
<div class="td-hero">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                @if(Auth::user()->profile_image_url)
                    <img src="{{ Auth::user()->profile_image_url }}" alt="User avatar" class="td-hero-avatar-img">
                @else
                    <div class="td-hero-avatar">{{ strtoupper(substr(Auth::user()->username ?: Auth::user()->name, 0, 1)) }}</div>
                @endif
                <div>
                    <p class="text-white text-opacity-75 small mb-0">Welcome back</p>
                    <h1 class="text-white fw-bold mb-0 fs-3">{{ Auth::user()->name }}</h1>
                    <p class="text-white text-opacity-50 small mb-0 mt-1">
                        <i class="fas fa-calendar-alt me-1"></i>{{ now()->format('l, d F Y') }}
                        &nbsp;·&nbsp;
                        <i class="fas fa-user-tag me-1"></i>Tenant
                    </p>
                </div>
            </div>
            <a href="{{ route('houses.index') }}" class="btn btn-hrs-primary px-4">
                <i class="fas fa-search me-2"></i>Browse Houses
            </a>
        </div>
    </div>
</div>

{{-- ── Pull stat cards up over hero ────────────────────────────────── --}}
<div class="container" style="margin-top: -1.75rem;">

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        {{-- Pending --}}
        <div class="col-6 col-md-3">
            <div class="card td-stat h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="td-stat-icon" style="background:#fff7ed;">
                            <i class="fas fa-hourglass-half" style="color:#f59e0b;"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#fff7ed;color:#d97706;">Requests</span>
                    </div>
                    <div class="td-stat-value">{{ $pendingRequests }}</div>
                    <div class="td-stat-label text-muted mt-1">Pending</div>
                </div>
            </div>
        </div>
        {{-- Accepted --}}
        <div class="col-6 col-md-3">
            <div class="card td-stat h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="td-stat-icon" style="background:#f0fdf4;">
                            <i class="fas fa-check-circle" style="color:#10b981;"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#f0fdf4;color:#059669;">Active</span>
                    </div>
                    <div class="td-stat-value">{{ $acceptedRequests }}</div>
                    <div class="td-stat-label text-muted mt-1">Accepted</div>
                </div>
            </div>
        </div>
        {{-- Rejected --}}
        <div class="col-6 col-md-3">
            <div class="card td-stat h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="td-stat-icon" style="background:#fef2f2;">
                            <i class="fas fa-times-circle" style="color:#ef4444;"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#fef2f2;color:#dc2626;">Rejected</span>
                    </div>
                    <div class="td-stat-value">{{ $rejectedRequests }}</div>
                    <div class="td-stat-label text-muted mt-1">Declined</div>
                </div>
            </div>
        </div>
        {{-- Total Paid --}}
        <div class="col-6 col-md-3">
            <div class="card td-stat h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="td-stat-icon" style="background:#eff6ff;">
                            <i class="fas fa-wallet" style="color:#3b82f6;"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#eff6ff;color:#2563eb;">Payments</span>
                    </div>
                    <div class="td-stat-value" style="font-size:1.3rem;">Nu.&nbsp;{{ number_format((float)$totalPaid, 0) }}</div>
                    <div class="td-stat-label text-muted mt-1">Total Paid</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- ── Left column: Process Guide ──────────────── --}}
        <div class="col-lg-4">

            {{-- Process Guide --}}
            <div class="card border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="fas fa-route me-2" style="color:#3b82f6;"></i>Rental Process
                    </h5>
                    <ul class="td-timeline">
                        @foreach([
                            ['icon'=>'fa-paper-plane','color'=>'#f59e0b','bg'=>'#fff7ed','title'=>'Submit Request','desc'=>'Send a rental request to the property owner.'],
                            ['icon'=>'fa-user-check','color'=>'#10b981','bg'=>'#f0fdf4','title'=>'Owner Reviews','desc'=>'Owner accepts or declines your request.'],
                            ['icon'=>'fa-credit-card','color'=>'#3b82f6','bg'=>'#eff6ff','title'=>'Make Payment','desc'=>'Pay the rental amount once accepted.'],
                            ['icon'=>'fa-file-signature','color'=>'#8b5cf6','bg'=>'#f5f3ff','title'=>'Lease Sent','desc'=>'System sends the lease agreement to owner.'],
                            ['icon'=>'fa-trophy','color'=>'#10b981','bg'=>'#f0fdf4','title'=>'Lease Approved','desc'=>'Owner signs off and rental is complete.'],
                        ] as $i => $step)
                        <li>
                            <div class="td-tl-num">{{ $i+1 }}</div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div style="width:28px;height:28px;border-radius:8px;background:{{ $step['bg'] }};display:flex;align-items:center;justify-content:center;">
                                        <i class="fas {{ $step['icon'] }} small" style="color:{{ $step['color'] }};"></i>
                                    </div>
                                    <span class="fw-semibold small">{{ $step['title'] }}</span>
                                </div>
                                <p class="text-muted small mb-0 ms-1">{{ $step['desc'] }}</p>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4" style="border-radius:16px;" id="share-review">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-1">
                        <i class="fas fa-pen me-2" style="color:#1d4ed8;"></i>Share Your Review
                    </h5>
                    <p class="text-muted small mb-3">Write your experience any time. Your latest review appears on the home page testimonials.</p>

                    @if(session('tenant_review_success'))
                        <div class="alert alert-success small py-2">{{ session('tenant_review_success') }}</div>
                    @endif

                    <form action="{{ route('tenant.reviews.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Review Title</label>
                            <input
                                type="text"
                                name="title"
                                class="form-control @error('title') is-invalid @enderror"
                                maxlength="120"
                                value="{{ old('title') }}"
                                placeholder="Example: I found my rental quickly"
                                required
                            >
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Your Review</label>
                            <textarea
                                name="message"
                                rows="4"
                                class="form-control @error('message') is-invalid @enderror"
                                maxlength="1000"
                                placeholder="Share your experience with HRS Bhutan..."
                                required
                            >{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Your Location (optional)</label>
                            <input
                                type="text"
                                name="location"
                                class="form-control @error('location') is-invalid @enderror"
                                maxlength="80"
                                value="{{ old('location') }}"
                                placeholder="Example: Thimphu"
                            >
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-hrs-primary w-100">
                            <i class="fas fa-paper-plane me-2"></i>Post Review
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Right column: Rental Requests ───────────────────────────── --}}
        <div class="col-lg-8" id="monthly-payment-to-admin">
            <div class="card border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-file-contract me-2" style="color:#1e3a5f;"></i>My Rental Requests
                        </h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('tenant.payment.form') }}" class="btn btn-sm btn-hrs-primary">
                                <i class="fas fa-credit-card me-1"></i>Make Payment
                            </a>
                            <a href="{{ route('rentals.my-rentals') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-external-link-alt me-1"></i>Full History
                            </a>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-2 p-3 rounded-3 mb-3" style="background:#eff6ff;border:1px solid #bfdbfe;">
                        <i class="fas fa-building-columns mt-1" style="color:#2563eb;"></i>
                        <div>
                            <p class="small mb-1" style="color:#1e3a8a;">
                                Monthly rent payments from this dashboard use the payment workflow with proof upload and admin verification.
                            </p>
                            <p class="small mb-0 text-muted">
                                Current time: {{ now()->format('d M Y, h:i A') }} · Billing month: {{ now()->format('F Y') }} · Due by: {{ now()->endOfMonth()->format('d M Y') }}
                            </p>
                        </div>
                    </div>

                    @if($rentals->isEmpty())
                        <div class="text-center py-5">
                            <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#f8fafc,#e2e8f0);margin:0 auto 1.25rem;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-home text-muted fs-3"></i>
                            </div>
                            <h6 class="fw-semibold">No rental requests yet</h6>
                            <p class="text-muted small mb-0">Choose a house below and send your first request.</p>
                        </div>

                        @if($requestableHouses->isNotEmpty())
                            <div class="row g-3 mt-1">
                                @foreach($requestableHouses->take(6) as $house)
                                    <div class="col-12 col-md-6 col-xl-4">
                                        <div class="border rounded-4 overflow-hidden h-100" style="border-color:#e2e8f0;">
                                            @if($house->image)
                                                <img src="{{ $house->getImageUrlAttribute() }}"
                                                     alt="{{ $house->title }}"
                                                     style="width:100%;height:165px;object-fit:cover;">
                                            @else
                                                <div class="d-flex align-items-center justify-content-center"
                                                     style="height:165px;background:linear-gradient(135deg,#f8fafc,#e2e8f0);">
                                                    <i class="fas fa-house text-muted fs-3"></i>
                                                </div>
                                            @endif
                                            <div class="p-3">
                                                <h6 class="fw-semibold small mb-1 text-truncate">{{ $house->title }}</h6>
                                                <p class="text-muted small mb-2 text-truncate">
                                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $house->location }}
                                                </p>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="fw-semibold" style="color:#1e3a5f;">Nu. {{ number_format((float) $house->price, 0) }}/mo</span>
                                                    <button type="button"
                                                            class="btn btn-sm btn-hrs-primary"
                                                            data-house-id="{{ $house->id }}"
                                                            data-house-title="{{ $house->title }}"
                                                            data-house-location="{{ $house->location }}"
                                                            data-house-price="{{ number_format((float) $house->price, 0) }}"
                                                            data-request-url="{{ route('rentals.store', $house) }}"
                                                            onclick="openRentalRequestModal(this)">
                                                        <i class="fas fa-paper-plane me-1"></i>Request
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="text-center mt-3">
                                <a href="{{ route('houses.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-search me-2"></i>See More Houses
                                </a>
                            </div>
                        @else
                            <div class="text-center mt-4">
                                <a href="{{ route('houses.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-search me-2"></i>Browse Houses
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="d-flex flex-column gap-4">
                            @foreach($rentals as $rental)
                                @php
                                    $advancePayments = $rental->payments->whereIn('payment_type', ['first_month_rent', 'security_deposit']);
                                    $totalAdvancePayments = $advancePayments->count();
                                    $verifiedAdvancePayments = $advancePayments->where('verification_status', 'verified')->count();
                                    $isAllPaymentsVerified = $totalAdvancePayments > 0 && $totalAdvancePayments === $verifiedAdvancePayments;
                                    $isPaymentVerified = $verifiedAdvancePayments > 0; // At least one payment is verified
                                    $hasPendingPayments = $advancePayments->whereIn('verification_status', ['pending', ''])->count() > 0;
                                    $hasRejectedPayments = $advancePayments->where('verification_status', 'rejected')->count() > 0;
                                    $inspectionCompleted = isset($completedInspectionHouseIds)
                                        ? $completedInspectionHouseIds->contains($rental->house_id)
                                        : false;
                                    $leaseAgreement = $rental->leaseAgreement;
                                    $tenantSigned = (bool) optional($leaseAgreement)->tenant_signed_at;
                                    $ownerSigned = (bool) optional($leaseAgreement)->owner_signed_at;
                                    $bothSigned = $tenantSigned && $ownerSigned;
                                    $latestMoveOut = $rental->moveOutRequests->sortByDesc('created_at')->first();
                                    $leaseNotRequested = in_array($rental->lease_status, [null, '', 'not_requested'], true);
                                    $decisionPending = ! in_array($rental->stay_decision, ['yes', 'no'], true);
                                    $needsStayDecision = $rental->status === 'active' && $inspectionCompleted && $decisionPending;

                                    /* ── Compute step 1–5 for visual status tracker ── */
                                    $isRejectedFlow = in_array($rental->status, ['cancelled', 'rejected'], true)
                                        || $rental->lease_status === 'rejected';

                                    $step = 1;
                                    if (in_array($rental->status, ['active', 'expired'], true)) {
                                        $step = 2;
                                    }
                                    if ($inspectionCompleted) {
                                        $step = 3;
                                    }

                                    $stayOrLeaseProgress = $needsStayDecision
                                        || in_array($rental->lease_status, ['requested', 'approved'], true)
                                        || $tenantSigned
                                        || $ownerSigned;

                                    if ($stayOrLeaseProgress) {
                                        $step = 4;
                                    }

                                    if ($bothSigned && $rental->lease_status === 'approved') {
                                        $step = 5;
                                    }

                                    $statusBadge = match ($rental->status) {
                                        'pending'    => ['bg'=>'#fff7ed','color'=>'#d97706','label'=>'Pending'],
                                        'active'     => ['bg'=>'#f0fdf4','color'=>'#059669','label'=>'Approved'],
                                        'expired'    => ['bg'=>'#f1f5f9','color'=>'#64748b','label'=>'Expired'],
                                        'cancelled'  => ['bg'=>'#fef2f2','color'=>'#dc2626','label'=>'Rejected'],
                                        default      => ['bg'=>'#f1f5f9','color'=>'#64748b','label'=>ucfirst($rental->status)],
                                    };

                                    $steps = [
                                        1 => 'Requested',
                                        2 => 'Accepted',
                                        3 => 'Inspection Done',
                                        4 => 'Stay Confirmed',
                                        5 => 'Agreement Signed',
                                    ];
                                @endphp

                                <div class="td-rental-card">
                                    {{-- Card header --}}
                                    <div class="td-rental-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <div>
                                            <h6 class="fw-bold mb-0">{{ $rental->house->title ?? 'Property' }}</h6>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-map-marker-alt me-1"></i>{{ $rental->house->location ?? 'Location not set' }}
                                                &nbsp;·&nbsp;
                                                Nu. {{ number_format((float)($rental->house->price ?? 0), 0) }}/mo
                                            </p>
                                            <p class="text-muted small mb-0 mt-1">
                                                <i class="fas fa-user me-1"></i>Owner: {{ $rental->house->owner->name ?? 'Owner' }}
                                            </p>
                                        </div>
                                        <span class="badge rounded-pill px-3 py-2"
                                              style="background:{{ $statusBadge['bg'] }};color:{{ $statusBadge['color'] }};font-size:.75rem;">
                                            {{ $statusBadge['label'] }}
                                        </span>
                                    </div>

                                    <div class="p-3 pt-4">
                                        {{-- 5-step progress stepper --}}
                                        <div class="td-stepper mb-3">
                                            @foreach($steps as $num => $label)
                                                @php
                                                    $dotClass = $num < $step
                                                        ? 'done'
                                                        : ($num === $step
                                                            ? ($isRejectedFlow ? 'failed' : 'active')
                                                            : '');
                                                @endphp
                                                <div class="td-step {{ $dotClass }}">
                                                    <div class="td-step-dot">
                                                        @if($num < $step)
                                                            <i class="fas fa-check" style="font-size:.6rem;"></i>
                                                        @elseif($num === $step && $step === 5 && !$isRejectedFlow)
                                                            <i class="fas fa-trophy" style="font-size:.65rem;"></i>
                                                        @else
                                                            {{ $num }}
                                                        @endif
                                                    </div>
                                                    <span class="td-step-label">{{ $label }}</span>
                                                </div>
                                            @endforeach
                                        </div>

                                        @if($isRejectedFlow)
                                            <div class="d-flex align-items-center gap-2 mb-4 px-1">
                                                <i class="fas fa-exclamation-triangle text-danger"></i>
                                                <span class="text-danger fw-semibold small">
                                                    @if($rental->lease_status === 'rejected')
                                                        Lease agreement was rejected by owner.
                                                    @else
                                                        This request has been cancelled or rejected.
                                                    @endif
                                                </span>
                                            </div>
                                        @endif

                                        {{-- Status pill row --}}
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            {{-- Inspection --}}
                                            @if($inspectionCompleted)
                                                <span class="badge rounded-pill" style="background:#eff6ff;color:#1d4ed8;">
                                                    <i class="fas fa-clipboard-check me-1"></i>Inspection Completed
                                                </span>
                                            @else
                                                <span class="badge rounded-pill" style="background:#f1f5f9;color:#64748b;">
                                                    <i class="fas fa-clipboard me-1"></i>Inspection Pending
                                                </span>
                                            @endif

                                            {{-- Payment --}}
                                            @if($isAllPaymentsVerified)
                                                <span class="badge rounded-pill" style="background:#f0fdf4;color:#059669;">
                                                    <i class="fas fa-check me-1"></i>Advance Payment Verified
                                                </span>
                                            @elseif($hasRejectedPayments)
                                                <span class="badge rounded-pill" style="background:#fef2f2;color:#dc2626;">
                                                    <i class="fas fa-times me-1"></i>Payment Rejected
                                                </span>
                                            @elseif($hasPendingPayments)
                                                <span class="badge rounded-pill" style="background:#fff7ed;color:#d97706;">
                                                    <i class="fas fa-clock me-1"></i>Payment Verification Pending
                                                </span>
                                            @elseif($totalAdvancePayments > 0)
                                                <span class="badge rounded-pill" style="background:#fff7ed;color:#d97706;">
                                                    <i class="fas fa-clock me-1"></i>Payment Submitted
                                                </span>
                                            @else
                                                <span class="badge rounded-pill" style="background:#f1f5f9;color:#64748b;">
                                                    <i class="fas fa-minus me-1"></i>No Payment
                                                </span>
                                            @endif

                                            {{-- Lease --}}
                                            @if($leaseAgreement)
                                                <a href="{{ route('rentals.lease.download', $leaseAgreement) }}"
                                                   class="badge rounded-pill text-decoration-none"
                                                   style="background:#eff6ff;color:#1d4ed8;">
                                                    <i class="fas fa-download me-1"></i>Download Lease
                                                </a>
                                            @endif

                                            @if($bothSigned && $rental->lease_status === 'approved')
                                                <span class="badge rounded-pill" style="background:#f0fdf4;color:#059669;">
                                                    <i class="fas fa-file-signature me-1"></i>Agreement Fully Signed
                                                </span>
                                            @elseif($tenantSigned && !$ownerSigned)
                                                <span class="badge rounded-pill" style="background:#fff7ed;color:#d97706;">
                                                    <i class="fas fa-user-check me-1"></i>Tenant Signed, Waiting Admin Verification
                                                </span>
                                            @elseif(!$tenantSigned && $ownerSigned)
                                                <span class="badge rounded-pill" style="background:#fff7ed;color:#d97706;">
                                                    <i class="fas fa-user-clock me-1"></i>Owner Signed, Waiting Tenant
                                                </span>
                                            @elseif($leaseAgreement && $isPaymentVerified)
                                                <span class="badge rounded-pill" style="background:#eff6ff;color:#2563eb;">
                                                    <i class="fas fa-file-signature me-1"></i>Agreement Ready For Signatures
                                                </span>
                                            @elseif($rental->lease_status === 'requested')
                                                <span class="badge rounded-pill" style="background:#eff6ff;color:#2563eb;">
                                                    <i class="fas fa-hourglass-half me-1"></i>Waiting Payment Verification
                                                </span>
                                            @elseif($rental->lease_status === 'rejected')
                                                <span class="badge rounded-pill" style="background:#fef2f2;color:#dc2626;">
                                                    <i class="fas fa-times me-1"></i>Lease Rejected
                                                </span>
                                            @else
                                                <span class="badge rounded-pill" style="background:#f1f5f9;color:#64748b;">
                                                    <i class="fas fa-file me-1"></i>No Lease Yet
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Action zone --}}
                                        @if($step === 5)
                                            <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                                <i class="fas fa-trophy" style="color:#10b981;font-size:1.2rem;"></i>
                                                <div>
                                                    <p class="fw-semibold text-success mb-0 small">Rental Process Complete!</p>
                                                    <p class="text-muted small mb-0">Both parties have digitally signed the agreement.</p>
                                                </div>
                                            </div>
                                        @elseif($rental->lease_status === 'approved' && $leaseAgreement && $isAllPaymentsVerified)
                                            <div class="p-3 rounded-3" style="background:#eff6ff;border:1px solid #bfdbfe;">
                                                <div class="d-flex align-items-start gap-2 mb-3">
                                                    <i class="fas fa-file-signature mt-1" style="color:#2563eb;"></i>
                                                    <p class="small mb-0" style="color:#1e3a8a;">
                                                        Your lease has been activated after payment verification. You can download the agreement below.
                                                    </p>
                                                </div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <a href="{{ route('rentals.lease.download', $leaseAgreement) }}" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-download me-1"></i>Download Agreement PDF
                                                    </a>
                                                    <span class="badge rounded-pill align-self-center" style="background:#f0fdf4;color:#059669;">Lease Active</span>
                                                </div>
                                            </div>
                                        @elseif($needsStayDecision)
                                            <div class="p-3 rounded-3" style="background:#eff6ff;border:1px solid #bfdbfe;">
                                                <div class="d-flex align-items-start gap-2 mb-3">
                                                    <i class="fas fa-circle-question mt-1" style="color:#2563eb;"></i>
                                                    <p class="small mb-0" style="color:#1e3a8a;">
                                                        The inspection for <strong>{{ $rental->house->title ?? ('Property #' . $rental->house_id) }}</strong> is completed.
                                                        Do you want to stay in this property?
                                                    </p>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-sm-6">
                                                        <button type="button"
                                                                class="btn btn-success w-100"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#stayConfirmModal{{ $rental->id }}">
                                                            <i class="fas fa-check-circle me-2"></i>Stay
                                                        </button>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <button type="button"
                                                                class="btn btn-outline-danger w-100"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#stayNoModal{{ $rental->id }}">
                                                            <i class="fas fa-door-open me-2"></i>
                                                            Move Out
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($rental->status === 'active')
                                            @if($rental->lease_status === 'requested')
                                                @if($leaseAgreement)
                                                    <div class="p-3 rounded-3" style="background:#eff6ff;border:1px solid #bfdbfe;">
                                                        <div class="d-flex align-items-start gap-2 mb-3">
                                                            <i class="fas fa-file-contract mt-1" style="color:#2563eb;"></i>
                                                            <div>
                                                                <p class="small mb-1" style="color:#1e3a8a;">
                                                                    A lease agreement has been uploaded by the owner. Please review and accept it before submitting advance payment.
                                                                </p>
                                                                <p class="small mb-0 text-muted">After you accept the lease agreement, the advance payment button will become available.</p>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <a href="{{ route('rentals.lease.download', $leaseAgreement) }}" class="btn btn-outline-primary btn-sm">
                                                                <i class="fas fa-download me-1"></i>Download Lease
                                                            </a>
                                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#acceptLeaseModal{{ $rental->id }}">
                                                                <i class="fas fa-check-circle me-1"></i>Accept Agreement
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectLeaseModal{{ $rental->id }}">
                                                                <i class="fas fa-times-circle me-1"></i>Reject Agreement
                                                            </button>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background:#eff6ff;border:1px solid #bfdbfe;">
                                                        <i class="fas fa-clock" style="color:#3b82f6;"></i>
                                                        <p class="text-primary small mb-0 fw-semibold">Waiting for owner to upload the lease agreement.</p>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="row g-2">
                                                    @if($rental->stay_decision === 'yes')
                                                        <div class="col-12">
                                                            <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                                                <i class="fas fa-house-user" style="color:#059669;"></i>
                                                                <p class="small mb-0 fw-semibold text-success">You are currently staying in this property.</p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <div class="col-sm-6">
                                                        <button type="button"
                                                                class="btn btn-outline-primary w-100"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#inspectionModal"
                                                                onclick="openInspectionModal({{ $rental->house_id }})">
                                                            <i class="fas fa-clipboard-check me-2"></i>Request Inspection
                                                        </button>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        @if($totalAdvancePayments === 0 || $hasRejectedPayments)
                                                            @if($leaseAgreement && $rental->lease_status === 'approved' && ($leaseAgreement->tenant_review_status ?? 'pending') === 'accepted')
                                                                <button type="button" class="btn btn-success w-100"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#paymentModal{{ $rental->id }}">
                                                                    <i class="fas fa-credit-card me-2"></i>Submit Advance Payment
                                                                </button>
                                                            @elseif($leaseAgreement)
                                                                <button type="button" class="btn btn-secondary w-100" disabled>
                                                                    <i class="fas fa-file-contract me-2"></i>Please accept the lease agreement first
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-secondary w-100" disabled>
                                                                    <i class="fas fa-file-contract me-2"></i>Waiting for Owner Lease Upload
                                                                </button>
                                                            @endif
                                                        @elseif($hasPendingPayments)
                                                            <button type="button" class="btn btn-warning w-100 text-dark" disabled>
                                                                <i class="fas fa-clock me-2"></i>Payment Verification Pending
                                                            </button>
                                                        @else
                                                            @php
                                                                $monthlyRentPayments = $rental->payments
                                                                    ->where('payment_type', 'monthly_rent');

                                                                $monthlyRentPaymentsForMonth = $monthlyRentPayments
                                                                    ->filter(fn($payment) => ($payment->billing_month && $payment->billing_month->isSameMonth(now()))
                                                                        || (! $payment->billing_month && $payment->payment_date && $payment->payment_date->isSameMonth(now())));

                                                                $monthlyRentPaidThisMonth = $monthlyRentPaymentsForMonth
                                                                    ->whereIn('verification_status', ['pending', 'verified'])
                                                                    ->isNotEmpty();

                                                                $monthlyRentVerifiedThisMonth = $monthlyRentPaymentsForMonth
                                                                    ->where('verification_status', 'verified')
                                                                    ->isNotEmpty();

                                                                $monthlyRentPendingThisMonth = $monthlyRentPaymentsForMonth
                                                                    ->where('verification_status', 'pending')
                                                                    ->isNotEmpty();

                                                                $latestMonthlyPayment = $monthlyRentPayments
                                                                    ->whereIn('verification_status', ['pending', 'verified'])
                                                                    ->sortByDesc('created_at')
                                                                    ->first();

                                                                $selectedMonthlyPaymentLabel = $latestMonthlyPayment
                                                                    ? ($latestMonthlyPayment->billing_month
                                                                        ? $latestMonthlyPayment->billing_month->format('F Y')
                                                                        : ($latestMonthlyPayment->payment_date
                                                                            ? $latestMonthlyPayment->payment_date->format('F Y')
                                                                            : now()->format('F Y')))
                                                                    : now()->format('F Y');

                                                                $latestMonthlyPaymentStatus = $latestMonthlyPayment
                                                                    ? $latestMonthlyPayment->verification_status
                                                                    : null;

                                                                $monthlyDueMonthLabel = now()->format('F Y');
                                                                $monthlyDueDateLabel = now()->endOfMonth()->format('d M Y');
                                                                $daysUntilMonthEnd = now()->startOfDay()->diffInDays(now()->copy()->endOfMonth()->startOfDay());
                                                                $isEndOfMonthWindow = now()->day >= 25;
                                                            @endphp

                                                            <div class="col-12">
                                                                @if($latestMonthlyPaymentStatus === 'verified')
                                                                    <div class="p-3 rounded-3" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                                                                            <p class="small mb-0 fw-semibold text-success">
                                                                                <i class="fas fa-circle-check me-1"></i>Monthly Rent to Admin: Paid for {{ $selectedMonthlyPaymentLabel }}
                                                                            </p>
                                                                            <span class="badge rounded-pill" style="background:#dcfce7;color:#166534;">Verified</span>
                                                                        </div>
                                                                        <p class="small mb-0 text-muted">Your monthly rent has been verified by admin.</p>
                                                                    </div>
                                                                @elseif($latestMonthlyPaymentStatus === 'pending')
                                                                    <div class="p-3 rounded-3" style="background:#fff7ed;border:1px solid #fed7aa;">
                                                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                                                                            <p class="small mb-0 fw-semibold" style="color:#9a3412;">
                                                                                <i class="fas fa-hourglass-half me-1"></i>Monthly Rent to Admin: Submitted for {{ $selectedMonthlyPaymentLabel }}
                                                                            </p>
                                                                            <span class="badge rounded-pill" style="background:#ffedd5;color:#9a3412;">Pending Verification</span>
                                                                        </div>
                                                                        <p class="small mb-0 text-muted">Admin will verify your payment proof shortly.</p>
                                                                    </div>
                                                                @else
                                                                    <div class="p-3 rounded-3" style="background:{{ $isEndOfMonthWindow ? '#fef2f2' : '#eff6ff' }};border:1px solid {{ $isEndOfMonthWindow ? '#fecaca' : '#bfdbfe' }};">
                                                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                                                                            <p class="small mb-0 fw-semibold" style="color:{{ $isEndOfMonthWindow ? '#b91c1c' : '#1d4ed8' }};">
                                                                                <i class="fas fa-calendar-day me-1"></i>Monthly Rent to Admin: Due for {{ $monthlyDueMonthLabel }}
                                                                            </p>
                                                                            <span class="badge rounded-pill" style="background:{{ $isEndOfMonthWindow ? '#fee2e2' : '#dbeafe' }};color:{{ $isEndOfMonthWindow ? '#991b1b' : '#1e40af' }};">
                                                                                Due {{ $monthlyDueDateLabel }}
                                                                            </span>
                                                                        </div>
                                                                        <p class="small mb-0 text-muted">
                                                                            {{ $isEndOfMonthWindow ? 'Month-end reminder: submit your monthly rent now to avoid delays.' : 'You can pay anytime this month. Please submit before the end of the month.' }}
                                                                            ({{ $daysUntilMonthEnd }} day{{ $daysUntilMonthEnd === 1 ? '' : 's' }} left)
                                                                        </p>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            {{-- Recent Payment Summary --}}
                                                            @php
                                                                $recentMonthlyPayments = $rental->payments
                                                                    ->where('payment_type', 'monthly_rent')
                                                                    ->sortByDesc('payment_date')
                                                                    ->take(3);
                                                            @endphp
                                                            @if($recentMonthlyPayments->isNotEmpty())
                                                                <div class="mt-3 p-2 rounded-2" style="background:#f1f5f9;border-left:4px solid #3b82f6;">
                                                                    <div class="small fw-semibold text-muted mb-2" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:.05em;">Recent Payments</div>
                                                                    <div class="d-flex flex-column gap-1">
                                                                        @foreach($recentMonthlyPayments as $payment)
                                                                            <div class="d-flex justify-content-between align-items-center text-muted small">
                                                                                <span>{{ $payment->billing_month ? $payment->billing_month->format('M Y') : $payment->payment_date->format('M Y') }} <span style="color:{{ $payment->verification_status === 'verified' ? '#10b981' : ($payment->verification_status === 'pending' ? '#f59e0b' : '#8b5cf6') }};">●</span></span>
                                                                                <span>{{ $payment->payment_date->format('d M') }} - {{ $payment->created_at->format('h:i A') }}</span>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            @if($isAllPaymentsVerified && ! $monthlyRentPaidThisMonth)
                                                                <button type="button" class="btn btn-success w-100"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#monthlyPaymentSelectModal{{ $rental->id }}">
                                                                    <i class="fas fa-calendar-plus me-2"></i>Pay Monthly Rent to Admin
                                                                </button>
                                                            @elseif($monthlyRentPaidThisMonth)
                                                                <button type="button" class="btn btn-warning w-100 text-dark" disabled>
                                                                    <i class="fas fa-clock me-2"></i>Monthly Rent Verification Pending
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-success w-100" disabled>
                                                                    <i class="fas fa-check-circle me-2"></i>Payment Completed - You Can Shift
                                                                </button>
                                                            @endif
                                                        @endif
                                                    </div>
                                                    <div class="col-12">
                                                        <button type="button"
                                                                class="btn btn-outline-danger w-100"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#moveOutModal{{ $rental->id }}"
                                                                @disabled($latestMoveOut && in_array($latestMoveOut->status, ['requested', 'approved']))>
                                                            <i class="fas fa-door-open me-2"></i>
                                                            {{ ($latestMoveOut && in_array($latestMoveOut->status, ['requested', 'approved'])) ? 'Move-Out Request In Progress' : 'Request Move-Out' }}
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        @elseif($rental->lease_status === 'requested')
                                            @if($leaseAgreement)
                                                <div class="p-3 rounded-3" style="background:#eff6ff;border:1px solid #bfdbfe;">
                                                    <div class="d-flex align-items-start gap-2 mb-3">
                                                        <i class="fas fa-file-contract mt-1" style="color:#2563eb;"></i>
                                                        <div>
                                                            <p class="small mb-1" style="color:#1e3a8a;">
                                                                A lease agreement has been uploaded by the owner. Please review and accept it before submitting advance payment.
                                                            </p>
                                                            <p class="small mb-0 text-muted">After you accept the lease agreement, the advance payment button will become available.</p>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <a href="{{ route('rentals.lease.download', $leaseAgreement) }}" class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-download me-1"></i>Download Lease
                                                        </a>
                                                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#acceptLeaseModal{{ $rental->id }}">
                                                            <i class="fas fa-check-circle me-1"></i>Accept Agreement
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectLeaseModal{{ $rental->id }}">
                                                            <i class="fas fa-times-circle me-1"></i>Reject Agreement
                                                        </button>
                                                        @if(($leaseAgreement->tenant_review_status ?? 'pending') === 'accepted' && ($totalAdvancePayments === 0 || $hasRejectedPayments) && !$hasPendingPayments)
                                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $rental->id }}">
                                                                <i class="fas fa-credit-card me-1"></i>Submit Advance Payment
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background:#eff6ff;border:1px solid #bfdbfe;">
                                                    <i class="fas fa-clock" style="color:#3b82f6;"></i>
                                                    <p class="text-primary small mb-0 fw-semibold">Waiting for owner to upload the lease agreement.</p>
                                                </div>
                                            @endif
                                        @elseif($rental->status === 'pending')
                                            <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background:#fff7ed;border:1px solid #fed7aa;">
                                                <i class="fas fa-hourglass-half" style="color:#f59e0b;"></i>
                                                <p class="small mb-0" style="color:#92400e;">Waiting for owner to review your request…</p>
                                            </div>
                                        @elseif($isRejectedFlow)
                                            <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background:#fef2f2;border:1px solid #fecaca;">
                                                <i class="fas fa-exclamation-circle" style="color:#ef4444;"></i>
                                                <p class="small mb-0 text-danger fw-semibold">This rental was declined. You may browse other properties.</p>
                                            </div>
                                        @else
                                            <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
                                                <p class="text-muted small mb-0">No action required at this stage.</p>
                                            </div>
                                        @endif

                                        @if($latestMoveOut)
                                            @php
                                                $moveOutStyle = match($latestMoveOut->status) {
                                                    'requested' => ['#fff7ed', '#d97706', 'Requested'],
                                                    'approved' => ['#eff6ff', '#2563eb', 'Approved'],
                                                    'completed' => ['#f0fdf4', '#059669', 'Completed'],
                                                    'rejected' => ['#fef2f2', '#dc2626', 'Rejected'],
                                                    default => ['#f1f5f9', '#64748b', ucfirst($latestMoveOut->status)],
                                                };
                                            @endphp
                                            <div class="mt-3 p-3 rounded-3" style="background:{{ $moveOutStyle[0] }};border:1px solid #e2e8f0;">
                                                <p class="mb-1 small fw-semibold" style="color:{{ $moveOutStyle[1] }};">
                                                    <i class="fas fa-truck-moving me-1"></i>Move-Out Status: {{ $moveOutStyle[2] }}
                                                </p>
                                                <p class="small text-muted mb-0">Reason: {{ $latestMoveOut->reason }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Stay decision: No modal --}}
                                <div class="modal fade" id="stayConfirmModal{{ $rental->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius:14px;">
                                            <div class="modal-header border-0 pb-0">
                                                <h6 class="modal-title fw-bold">Stay</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('rentals.stay-decision', $rental) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="decision" value="yes">
                                                <div class="modal-body pt-2">
                                                    <p class="small mb-3" style="color:#1e3a8a;">Do you want to continue staying in this property?</p>
                                                    <div>
                                                        <label class="form-label small fw-semibold">Message (optional)</label>
                                                        <textarea name="message" rows="3" maxlength="500" class="form-control" placeholder="Any note for the owner"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">Confirm Stay</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="stayNoModal{{ $rental->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius:14px;">
                                            <div class="modal-header border-0 pb-0">
                                                <h6 class="modal-title fw-bold">Move Out</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('rentals.stay-decision', $rental) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="decision" value="no">
                                                <div class="modal-body pt-2">
                                                    <p class="small text-muted mb-2">Choose move-out date and provide reason to start move-out and refund process.</p>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Move-Out Date <span class="text-danger">*</span></label>
                                                        <input type="date" name="move_out_date" class="form-control" min="{{ now()->toDateString() }}" required>
                                                    </div>
                                                    <div>
                                                        <label class="form-label small fw-semibold">Reason <span class="text-danger">*</span></label>
                                                        <textarea name="message" rows="3" maxlength="1500" class="form-control"
                                                                  placeholder="Why you want to move out" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-outline-danger">Confirm Move Out</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="paymentModal{{ $rental->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;max-height:90vh;">
                                            <div class="modal-header border-0 pb-2" style="background:linear-gradient(135deg,#10b981,#059669);">
                                                <div class="text-white">
                                                    <h5 class="modal-title fw-bold mb-1">
                                                        <i class="fas fa-wallet me-2"></i>Submit Advance Payment
                                                    </h5>
                                                    @php
                                                        $securityDeposit = $rental->house->security_deposit_amount ?? $rental->monthly_rent;
                                                        $commissionRate = $rental->house->admin_commission_rate ?? 5;
                                                        $serviceFee = round($rental->monthly_rent * ($commissionRate / 100), 2);
                                                        // Charge for two months (rent + service fee per month)
                                                        $firstMonthTotal = round(($rental->monthly_rent * 2) + ($serviceFee * 2), 2);
                                                        $totalAdvance = round($firstMonthTotal + $securityDeposit, 2);
                                                    @endphp
                                                    <p class="mb-0 small opacity-75">Total Advance: <strong>Nu. {{ number_format($totalAdvance, 0) }}</strong></p>
                                                </div>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('rentals.pay', $rental) }}" method="POST" enctype="multipart/form-data" id="paymentForm{{ $rental->id }}">
                                                @csrf
                                                <input type="hidden" name="payment_type" value="first_month_rent">
                                                <input type="hidden" name="confirm_payment" value="1">
                                                <div class="modal-body" style="padding:1.5rem;">
                                                    <div class="alert alert-info d-flex gap-2 mb-4" style="border-radius:12px;background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;">
                                                        <i class="fas fa-info-circle mt-1 flex-shrink-0"></i>
                                                        <div class="small">
                                                            <strong>Payment Instructions:</strong><br>
                                                            First choose payment method, then upload your payment screenshot, then click Confirm Payment.
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">
                                                            <i class="fas fa-building-columns me-1" style="color:#0ea5e9;"></i>Payment Method <span class="text-danger">*</span>
                                                        </label>
                                                        <select name="payment_method" id="paymentMethod{{ $rental->id }}"
                                                                class="form-select"
                                                                style="border-radius:8px;border:1px solid #e2e8f0;"
                                                                required>
                                                            <option value="">Select payment method</option>
                                                            <option value="mbob">mBoB</option>
                                                            <option value="mpay">mPay</option>
                                                            <option value="bdbl">BDBL</option>
                                                            <option value="cash">Cash</option>
                                                        </select>
                                                        <small id="methodHelp{{ $rental->id }}" class="text-muted d-block mt-1">Choose the app or cash mode used for this payment.</small>
                                                    </div>

                                                    <div class="mb-3 p-3" style="border-radius:10px;border:1px solid #fde68a;background:#fffbeb;">
                                                        <label class="form-label fw-semibold small mb-2">
                                                            <i class="fas fa-file-image me-1" style="color:#f59e0b;"></i>Payment Screenshot <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="file" name="payment_proof" id="proofFile{{ $rental->id }}"
                                                               class="form-control" style="border-radius:8px;border:1px dashed #e2e8f0;"
                                                               accept=".jpg,.jpeg,.png,.pdf"
                                                               onchange="handleFileChange{{ $rental->id }}(this)" required>
                                                        <small class="text-muted d-block mt-1">Required. Accepted: JPG, PNG, PDF (Max 5MB)</small>
                                                        <small id="fileInfo{{ $rental->id }}" class="text-success d-none d-block mt-1">
                                                            <i class="fas fa-check-circle me-1"></i><span id="fileName{{ $rental->id }}"></span>
                                                        </small>
                                                    </div>

                                                    <button type="button" class="btn btn-lg w-100 mb-3" id="confirmPayBtn{{ $rental->id }}"
                                                            style="background:linear-gradient(135deg,#059669,#047857);color:white;border-radius:12px;font-weight:800;padding:1rem 1.5rem;font-size:1.1rem;box-shadow:0 4px 6px rgba(0,0,0,0.1);border:3px solid #10b981;">
                                                        <i class="fas fa-check-circle me-2"></i>✓ Confirm Payment
                                                    </button>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">
                                                            <i class="fas fa-hashtag me-1" style="color:#3b82f6;"></i>Transaction ID <span class="text-muted">(Optional)</span>
                                                        </label>
                                                        <input type="text" name="transaction_id" id="txId{{ $rental->id }}" maxlength="120" 
                                                               class="form-control" style="border-radius:8px;border:1px solid #e2e8f0;"
                                                               placeholder="e.g., TXN-323492034 or DRUK-PAY-12345"
                                                               onchange="validatePaymentForm{{ $rental->id }}()">
                                                        <small class="text-muted d-block mt-1">From mobile banking, BDT, or other payment app</small>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">
                                                            <i class="fas fa-comment me-1" style="color:#8b5cf6;"></i>Additional Notes <span class="text-muted">(Optional)</span>
                                                        </label>
                                                        <textarea name="notes" id="notes{{ $rental->id }}" rows="3" maxlength="500" 
                                                                  class="form-control" style="border-radius:8px;border:1px solid #e2e8f0;font-size:0.95rem;resize:vertical;"
                                                                  placeholder="E.g., Payment made via BDT. Transaction done on [date]. Bank reference: [...]"></textarea>
                                                        <small class="text-muted d-block mt-1"><span id="charCount{{ $rental->id }}">0</span>/500 characters</small>
                                                    </div>

                                                    <div class="p-3 mb-3" style="border-radius:12px;background:#ecfeff;border:1px solid #bae6fd;">
                                                        <div class="small text-uppercase fw-semibold mb-2" style="letter-spacing:.05em;color:#0369a1;">Advance Payment Breakdown</div>
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span>First Month Rent:</span>
                                                            <span>Nu. {{ number_format($rental->monthly_rent, 0) }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-1 small text-muted">
                                                            <span>Service Fee ({{ $commissionRate }}%):</span>
                                                            <span>Nu. {{ number_format($serviceFee, 0) }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                                            <span><strong>Two Months Total:</strong></span>
                                                            <span><strong>Nu. {{ number_format($firstMonthTotal, 0) }}</strong></span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span>Security Deposit:</span>
                                                            <span>Nu. {{ number_format($securityDeposit, 0) }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between pt-2 border-top">
                                                            <span><strong>Total Advance:</strong></span>
                                                            <span><strong>Nu. {{ number_format($totalAdvance, 0) }}</strong></span>
                                                        </div>
                                                        <small class="text-muted d-block mt-1">Security deposit will be held by admin and refunded at move-out.</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 d-flex flex-column align-items-stretch" style="background:#fff;padding:1.25rem 1.5rem 1.75rem;">
                                                    <div class="small text-muted mb-3" style="line-height:1.5;">
                                                        Please check your payment details one last time. Admin will verify the uploaded payment proof after submission.
                                                    </div>
                                                    <div class="d-flex justify-content-end">
                                                        <button type="button" class="btn btn-light fw-semibold" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="monthlyPaymentModal{{ $rental->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;max-height:90vh;display:flex;flex-direction:column;">
                                            <div class="modal-header border-0 pb-2" style="background:linear-gradient(135deg,#10b981,#059669);flex-shrink:0;">
                                                <div class="text-white">
                                                    <h5 class="modal-title fw-bold mb-1">
                                                        <i class="fas fa-calendar-plus me-2"></i>Submit Monthly Rent
                                                    </h5>
                                                    @php
                                                        $commissionRate = $rental->house->admin_commission_rate ?? 5;
                                                        $monthlyCommission = round($rental->monthly_rent * ($commissionRate / 100), 2);
                                                        $monthlyTotal = round($rental->monthly_rent + $monthlyCommission, 2);
                                                    @endphp
                                                    <p id="monthlyPaymentAmount{{ $rental->id }}" class="mb-0 small opacity-75">Monthly Rent Due: <strong>Nu. {{ number_format($monthlyTotal, 0) }}</strong></p>
                                                    <p id="monthlyPaymentMonth{{ $rental->id }}" class="mb-0 small opacity-75">Billing Month: <strong>{{ now()->format('F Y') }}</strong> · Due by <strong>{{ now()->endOfMonth()->format('d M Y') }}</strong></p>
                                                </div>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('rentals.pay', $rental) }}" method="POST" enctype="multipart/form-data" id="monthlyPaymentForm{{ $rental->id }}" style="display:flex;flex-direction:column;flex:1;">
                                                @csrf
                                                <input type="hidden" name="payment_type" value="monthly_rent">
                                                <input type="hidden" name="confirm_payment" value="1">
                                                <div class="modal-body" style="padding:1.5rem;overflow-y:auto;flex:1;">
                                                    <div class="alert alert-info d-flex gap-2 mb-4" style="border-radius:12px;background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;">
                                                        <i class="fas fa-info-circle mt-1 flex-shrink-0"></i>
                                                        <div class="small" id="monthlyPaymentInfo{{ $rental->id }}">
                                                            <strong>Payment Instructions:</strong><br>
                                                            Pay monthly rent to admin. Choose your payment method, upload proof, then submit for verification.
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">
                                                            <i class="fas fa-building-columns me-1" style="color:#0ea5e9;"></i>Payment Method <span class="text-danger">*</span>
                                                        </label>
                                                        <select name="payment_method" id="monthlyPaymentMethod{{ $rental->id }}"
                                                                class="form-select"
                                                                style="border-radius:8px;border:1px solid #e2e8f0;"
                                                                required>
                                                            <option value="">Select payment method</option>
                                                            <option value="mbob">mBoB</option>
                                                            <option value="mpay">mPay</option>
                                                            <option value="bdbl">BDBL</option>
                                                            <option value="cash">Cash</option>
                                                        </select>
                                                        <small id="monthlyMethodHelp{{ $rental->id }}" class="text-muted d-block mt-1">Choose the app or cash mode used for this payment.</small>
                                                    </div>

                                                    <div class="mb-3 p-3" style="border-radius:10px;border:1px solid #fde68a;background:#fffbeb;">
                                                        <label class="form-label fw-semibold small mb-2">
                                                            <i class="fas fa-file-image me-1" style="color:#f59e0b;"></i>Payment Screenshot <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="file" name="payment_proof" id="monthlyProofFile{{ $rental->id }}"
                                                               class="form-control" style="border-radius:8px;border:1px dashed #e2e8f0;"
                                                               accept=".jpg,.jpeg,.png,.pdf"
                                                               onchange="handleMonthlyFileChange{{ $rental->id }}(this)" required>
                                                        <small class="text-muted d-block mt-1">Required. Accepted: JPG, PNG, PDF (Max 5MB)</small>
                                                        <small id="monthlyFileInfo{{ $rental->id }}" class="text-success d-none d-block mt-1">
                                                            <i class="fas fa-check-circle me-1"></i><span id="monthlyFileName{{ $rental->id }}"></span>
                                                        </small>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">
                                                            <i class="fas fa-hashtag me-1" style="color:#3b82f6;"></i>Transaction ID <span class="text-muted">(Optional)</span>
                                                        </label>
                                                        <input type="text" name="transaction_id" id="monthlyTxId{{ $rental->id }}" maxlength="120"
                                                               class="form-control" style="border-radius:8px;border:1px solid #e2e8f0;"
                                                               placeholder="e.g., TXN-323492034 or DRUK-PAY-12345"
                                                               onchange="validateMonthlyPaymentForm{{ $rental->id }}()">
                                                        <small class="text-muted d-block mt-1">From mobile banking, BDT, or other payment app</small>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">
                                                            <i class="fas fa-comment me-1" style="color:#8b5cf6;"></i>Additional Notes <span class="text-muted">(Optional)</span>
                                                        </label>
                                                        <textarea name="notes" id="monthlyNotes{{ $rental->id }}" rows="3" maxlength="500"
                                                                  class="form-control" style="border-radius:8px;border:1px solid #e2e8f0;font-size:0.95rem;resize:vertical;"
                                                                  placeholder="E.g., Payment made on [date] via BDBL..."></textarea>
                                                        <small class="text-muted d-block mt-1"><span id="monthlyCharCount{{ $rental->id }}">0</span>/500 characters</small>
                                                    </div>

                                                    <div class="p-3 mb-3" style="border-radius:12px;background:#ecfeff;border:1px solid #bae6fd;">
                                                        <div class="small text-uppercase fw-semibold mb-2" style="letter-spacing:.05em;color:#0369a1;">Monthly Rent Breakdown</div>
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span>Monthly Rent:</span>
                                                            <span>Nu. {{ number_format($rental->monthly_rent, 0) }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-1 small text-muted">
                                                            <span>Service Fee ({{ $commissionRate }}%):</span>
                                                            <span>Nu. {{ number_format($monthlyCommission, 0) }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between pt-2 border-top">
                                                            <span><strong>Total Due:</strong></span>
                                                            <span><strong>Nu. {{ number_format($monthlyTotal, 0) }}</strong></span>
                                                        </div>
                                                    </div>

                                                    {{-- Payment History Section --}}
                                                    @php
                                                        $allMonthlyPayments = $rental->payments
                                                            ->where('payment_type', 'monthly_rent')
                                                            ->sortByDesc('created_at');
                                                    @endphp

                                                    @if($allMonthlyPayments->isNotEmpty())
                                                        <div class="border-top my-4 pt-3">
                                                            <div class="small text-uppercase fw-semibold mb-3" style="letter-spacing:.05em;color:#64748b;">
                                                                <i class="fas fa-history me-1" style="color:#0369a1;"></i>Payment History
                                                            </div>
                                                            <div class="d-flex flex-column gap-2">
                                                                @foreach($allMonthlyPayments as $payment)
                                                                    <div class="p-3" style="border-radius:10px;background:{{ $payment->verification_status === 'verified' ? '#f0fdf4' : ($payment->verification_status === 'pending' ? '#fff7ed' : '#f8f1ff') }};border:1px solid {{ $payment->verification_status === 'verified' ? '#bbf7d0' : ($payment->verification_status === 'pending' ? '#fed7aa' : '#e9d5ff') }};">
                                                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                                            <div>
                                                                                <div class="small fw-semibold" style="color:{{ $payment->verification_status === 'verified' ? '#166534' : ($payment->verification_status === 'pending' ? '#9a3412' : '#6b21a8') }};">
                                                                                    {{ $payment->billing_month ? $payment->billing_month->format('F Y') : $payment->payment_date->format('F Y') }}
                                                                                </div>
                                                                                <div class="text-muted small">
                                                                                    <i class="fas fa-calendar me-1"></i>{{ $payment->payment_date->format('d M Y') }}
                                                                                    @if($payment->created_at)
                                                                                        <span class="ms-2"><i class="fas fa-clock me-1"></i>{{ $payment->created_at->format('h:i A') }}</span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                            <div class="text-end">
                                                                                <div class="small fw-bold" style="color:{{ $payment->verification_status === 'verified' ? '#166534' : ($payment->verification_status === 'pending' ? '#9a3412' : '#6b21a8') }};">
                                                                                    Nu. {{ number_format($payment->amount, 0) }}
                                                                                </div>
                                                                                <span class="badge rounded-pill small" style="background:{{ $payment->verification_status === 'verified' ? '#dcfce7' : ($payment->verification_status === 'pending' ? '#ffedd5' : '#f3e8ff') }};color:{{ $payment->verification_status === 'verified' ? '#166534' : ($payment->verification_status === 'pending' ? '#9a3412' : '#6b21a8') }};">
                                                                                    {{ ucfirst($payment->verification_status) }}
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                        @if($payment->transaction_id)
                                                                            <div class="small text-muted"><i class="fas fa-hashtag me-1"></i>{{ $payment->transaction_id }}</div>
                                                                        @endif
                                                                        @if($payment->payment_method)
                                                                            <div class="small text-muted"><i class="fas fa-building-columns me-1"></i>{{ ucfirst($payment->payment_method) }}</div>
                                                                        @endif
                                                                        @if($payment->notes)
                                                                            <div class="small text-muted"><i class="fas fa-comment me-1"></i>{{ $payment->notes }}</div>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer border-0 d-flex flex-column align-items-stretch" style="background:#fff;padding:1.5rem 1.5rem 2rem;flex-shrink:0;">
                                                    <div class="mb-3">
                                                        <button type="button" class="btn btn-lg w-100" id="confirmMonthlyPayBtn{{ $rental->id }}"
                                                                style="background:linear-gradient(135deg,#059669,#047857);color:white;border-radius:12px;font-weight:800;padding:1rem 1.5rem;font-size:1.1rem;box-shadow:0 4px 6px rgba(0,0,0,0.1);border:3px solid #10b981;"
                                                                disabled>
                                                            <i class="fas fa-check-circle me-2"></i>✓ Confirm Monthly Rent
                                                        </button>
                                                        <small class="text-success d-block text-center fw-semibold mt-2" style="font-size:0.95rem;">
                                                            <i class="fas fa-arrow-up me-1"></i>Select payment method and upload screenshot above
                                                        </small>
                                                    </div>
                                                    <div class="small text-muted mb-3" style="line-height:1.6;">
                                                        Monthly rent payment will be reviewed by admin after submission. Your owner will be notified when it is verified.
                                                    </div>
                                                    <div class="d-flex justify-content-end">
                                                        <button type="button" class="btn btn-light fw-semibold" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Monthly Payment Selection Modal (Month + Date Selection) --}}
                                <div class="modal fade" id="monthlyPaymentSelectModal{{ $rental->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                                            <div class="modal-header border-0" style="background:linear-gradient(135deg,#3b82f6,#2563eb);">
                                                <div class="text-white">
                                                    <h5 class="modal-title fw-bold mb-0">
                                                        <i class="fas fa-calendar me-2"></i>Select Month & Payment Date
                                                    </h5>
                                                </div>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form id="monthlySelectForm{{ $rental->id }}">
                                                <div class="modal-body" style="padding:1.5rem;">
                                                    <div class="mb-4">
                                                        <label class="form-label fw-semibold small">
                                                            <i class="fas fa-calendar-check me-1" style="color:#3b82f6;"></i>Billing Month <span class="text-danger">*</span>
                                                        </label>
                                                        <select name="billing_month" id="billingMonth{{ $rental->id }}" class="form-select" style="border-radius:8px;border:1px solid #e2e8f0;" required>
                                                            <option value="">-- Select the month to pay for --</option>
                                                            @php
                                                                // Show current month and last 6 months
                                                                for ($i = 0; $i <= 6; $i++) {
                                                                    $date = now()->subMonths($i);
                                                                    $monthKey = $date->format('Y-m');
                                                                    $monthLabel = $date->format('F Y');
                                                                    $isCurrentMonth = ($i === 0);
                                                                    echo sprintf(
                                                                        '<option value="%s" %s>%s%s</option>',
                                                                        $monthKey,
                                                                        $isCurrentMonth ? 'selected' : '',
                                                                        $monthLabel,
                                                                        $isCurrentMonth ? ' (Current)' : ''
                                                                    );
                                                                }
                                                            @endphp
                                                        </select>
                                                        <small class="text-muted d-block mt-1">Select which billing month you want to pay for.</small>
                                                    </div>

                                                    <div class="mb-4">
                                                        <label class="form-label fw-semibold small">
                                                            <i class="fas fa-hourglass-end me-1" style="color:#f59e0b;"></i>Payment Date <span class="text-muted">(Optional)</span>
                                                        </label>
                                                        <input type="date" name="payment_date" id="paymentDate{{ $rental->id }}" class="form-control" style="border-radius:8px;border:1px solid #e2e8f0;">
                                                        <small class="text-muted d-block mt-1">When did you make the payment? (Today by default)</small>
                                                    </div>

                                                    <div class="mb-4">
                                                        <label class="form-label fw-semibold small">
                                                            <i class="fas fa-clock me-1" style="color:#8b5cf6;"></i>Payment Time <span class="text-muted">(Optional)</span>
                                                        </label>
                                                        <input type="time" name="payment_time" id="paymentTime{{ $rental->id }}" class="form-control" style="border-radius:8px;border:1px solid #e2e8f0;">
                                                        <small class="text-muted d-block mt-1">What time did you make the payment?</small>
                                                    </div>

                                                    <div class="alert alert-info d-flex gap-2" style="border-radius:12px;background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;">
                                                        <i class="fas fa-info-circle mt-1 flex-shrink-0"></i>
                                                        <div class="small">
                                                            <strong>Next Step:</strong> After confirming, you'll upload the payment screenshot and proof on the next screen.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0" style="background:#f8fafc;padding:1.25rem 1.5rem;">
                                                    <button type="button" class="btn btn-light fw-semibold" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
                                                    <button type="button" class="btn btn-primary fw-semibold" style="border-radius:8px;padding:0.5rem 1.5rem;" onclick="proceedToMonthlyPayment{{ $rental->id }}();">
                                                        <i class="fas fa-arrow-right me-2"></i>Proceed to Payment
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="confirmPaymentModal{{ $rental->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow" style="border-radius:14px;">
                                            <div class="modal-header border-0">
                                                <h6 class="modal-title fw-bold">Confirm Payment</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body pt-0">
                                                <p class="mb-0">Are you sure you want to submit this advance payment of <strong>Nu. {{ number_format($rental->monthly_rent * 2, 0) }}</strong>?</p>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="button" class="btn btn-success" id="confirmModalSubmitBtn{{ $rental->id }}">Confirm</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                document.getElementById('notes{{ $rental->id }}').addEventListener('input', function() {
                                    document.getElementById('charCount{{ $rental->id }}').textContent = this.value.length;
                                });

                                document.getElementById('paymentMethod{{ $rental->id }}').addEventListener('change', function() {
                                    const method = this.value;
                                    const txInput = document.getElementById('txId{{ $rental->id }}');
                                    const methodHelp = document.getElementById('methodHelp{{ $rental->id }}');

                                    if (method === 'mbob') {
                                        txInput.placeholder = 'e.g., mBoB-TRX-2026001';
                                        methodHelp.textContent = 'Selected: mBoB mobile banking payment.';
                                    } else if (method === 'mpay') {
                                        txInput.placeholder = 'e.g., MPAY-TRX-2026001';
                                        methodHelp.textContent = 'Selected: mPay wallet payment.';
                                    } else if (method === 'bdbl') {
                                        txInput.placeholder = 'e.g., BDBL-REF-2026001';
                                        methodHelp.textContent = 'Selected: BDBL banking payment.';
                                    } else if (method === 'cash') {
                                        txInput.placeholder = 'Enter receipt/cash reference (if available)';
                                        methodHelp.textContent = 'Selected: Cash payment. Upload a receipt photo if possible.';
                                    } else {
                                        txInput.placeholder = 'e.g., TXN-323492034 or DRUK-PAY-12345';
                                        methodHelp.textContent = 'Choose the app or cash mode used for this payment.';
                                    }

                                    validatePaymentForm{{ $rental->id }}();
                                });

                                function handleFileChange{{ $rental->id }}(input) {
                                    if (input.files && input.files[0]) {
                                        const file = input.files[0];
                                        document.getElementById('fileInfo{{ $rental->id }}').classList.remove('d-none');
                                        document.getElementById('fileName{{ $rental->id }}').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                                    } else {
                                        document.getElementById('fileInfo{{ $rental->id }}').classList.add('d-none');
                                    }
                                    validatePaymentForm{{ $rental->id }}();
                                }

                                function validatePaymentForm{{ $rental->id }}() {
                                    const method = document.getElementById('paymentMethod{{ $rental->id }}').value;
                                    const proofFile = document.getElementById('proofFile{{ $rental->id }}').files.length > 0;
                                    const confirmPayBtn = document.getElementById('confirmPayBtn{{ $rental->id }}');
                                    
                                    if (method.length > 0 && proofFile) {
                                        confirmPayBtn.disabled = false;
                                        confirmPayBtn.style.opacity = '1';
                                        confirmPayBtn.style.cursor = 'pointer';
                                    } else {
                                        confirmPayBtn.disabled = true;
                                        confirmPayBtn.style.opacity = '0.5';
                                        confirmPayBtn.style.cursor = 'not-allowed';
                                    }
                                }

                                function openConfirmPaymentModal{{ $rental->id }}() {
                                    const method = document.getElementById('paymentMethod{{ $rental->id }}').value;
                                    const proofFile = document.getElementById('proofFile{{ $rental->id }}').files.length > 0;

                                    if (!method || !proofFile) {
                                        alert('Please select a payment method and upload a payment screenshot first.');
                                        return;
                                    }

                                    const confirmModal = new bootstrap.Modal(document.getElementById('confirmPaymentModal{{ $rental->id }}'));
                                    confirmModal.show();
                                }

                                document.getElementById('confirmPayBtn{{ $rental->id }}').addEventListener('click', openConfirmPaymentModal{{ $rental->id }});

                                document.getElementById('confirmModalSubmitBtn{{ $rental->id }}').addEventListener('click', function () {
                                    const form = document.getElementById('paymentForm{{ $rental->id }}');
                                    const confirmPayBtn = document.getElementById('confirmPayBtn{{ $rental->id }}');
                                    const confirmModalSubmitBtn = document.getElementById('confirmModalSubmitBtn{{ $rental->id }}');

                                    confirmPayBtn.disabled = true;
                                    confirmModalSubmitBtn.disabled = true;
                                    confirmPayBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';

                                    form.submit();
                                });

                                document.getElementById('monthlyNotes{{ $rental->id }}').addEventListener('input', function() {
                                    document.getElementById('monthlyCharCount{{ $rental->id }}').textContent = this.value.length;
                                });

                                document.getElementById('monthlyPaymentMethod{{ $rental->id }}').addEventListener('change', function() {
                                    const method = this.value;
                                    const txInput = document.getElementById('monthlyTxId{{ $rental->id }}');
                                    const methodHelp = document.getElementById('monthlyMethodHelp{{ $rental->id }}');

                                    if (method === 'mbob') {
                                        txInput.placeholder = 'e.g., mBoB-TRX-2026001';
                                        methodHelp.textContent = 'Selected: mBoB mobile banking payment.';
                                    } else if (method === 'mpay') {
                                        txInput.placeholder = 'e.g., MPAY-TRX-2026001';
                                        methodHelp.textContent = 'Selected: mPay wallet payment.';
                                    } else if (method === 'bdbl') {
                                        txInput.placeholder = 'e.g., BDBL-REF-2026001';
                                        methodHelp.textContent = 'Selected: BDBL banking payment.';
                                    } else if (method === 'cash') {
                                        txInput.placeholder = 'Enter receipt/cash reference (if available)';
                                        methodHelp.textContent = 'Selected: Cash payment. Upload a receipt photo if possible.';
                                    } else {
                                        txInput.placeholder = 'e.g., TXN-323492034 or DRUK-PAY-12345';
                                        methodHelp.textContent = 'Choose the app or cash mode used for this payment.';
                                    }

                                    validateMonthlyPaymentForm{{ $rental->id }}();
                                });

                                function handleMonthlyFileChange{{ $rental->id }}(input) {
                                    if (input.files && input.files[0]) {
                                        const file = input.files[0];
                                        document.getElementById('monthlyFileInfo{{ $rental->id }}').classList.remove('d-none');
                                        document.getElementById('monthlyFileName{{ $rental->id }}').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                                    } else {
                                        document.getElementById('monthlyFileInfo{{ $rental->id }}').classList.add('d-none');
                                    }
                                    validateMonthlyPaymentForm{{ $rental->id }}();
                                }

                                function validateMonthlyPaymentForm{{ $rental->id }}() {
                                    const method = document.getElementById('monthlyPaymentMethod{{ $rental->id }}').value;
                                    const proofFile = document.getElementById('monthlyProofFile{{ $rental->id }}').files.length > 0;
                                    const confirmPayBtn = document.getElementById('confirmMonthlyPayBtn{{ $rental->id }}');

                                    if (method.length > 0 && proofFile) {
                                        confirmPayBtn.disabled = false;
                                        confirmPayBtn.style.opacity = '1';
                                        confirmPayBtn.style.cursor = 'pointer';
                                    } else {
                                        confirmPayBtn.disabled = true;
                                        confirmPayBtn.style.opacity = '0.5';
                                        confirmPayBtn.style.cursor = 'not-allowed';
                                    }
                                }

                                document.getElementById('confirmMonthlyPayBtn{{ $rental->id }}').addEventListener('click', function() {
                                    const method = document.getElementById('monthlyPaymentMethod{{ $rental->id }}').value;
                                    const proofFile = document.getElementById('monthlyProofFile{{ $rental->id }}').files.length > 0;
                                    const confirmButton = document.getElementById('confirmMonthlyPayBtn{{ $rental->id }}');

                                    if (!method || !proofFile) {
                                        alert('Please select a payment method and upload a payment screenshot first.');
                                        return;
                                    }

                                    confirmButton.disabled = true;
                                    confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
                                    document.getElementById('monthlyPaymentForm{{ $rental->id }}').submit();
                                });

                                document.getElementById('acceptForm{{ $rental->id }}').addEventListener('submit', function() {
                                    const acceptBtn = document.getElementById('acceptBtn{{ $rental->id }}');
                                    acceptBtn.disabled = true;
                                    acceptBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Accepting...';
                                });

                                function proceedToMonthlyPayment{{ $rental->id }}() {
                                    const billingMonth = document.getElementById('billingMonth{{ $rental->id }}').value;
                                    const paymentDate = document.getElementById('paymentDate{{ $rental->id }}').value;
                                    const paymentTime = document.getElementById('paymentTime{{ $rental->id }}').value;

                                    // Validate month selection
                                    if (!billingMonth) {
                                        alert('Please select a billing month.');
                                        return;
                                    }

                                    // Store the values in hidden fields or data attributes in the payment form
                                    const paymentForm = document.getElementById('monthlyPaymentForm{{ $rental->id }}');
                                    
                                    // Create or update hidden fields
                                    let billingMonthInput = paymentForm.querySelector('input[name="billing_month"]');
                                    if (!billingMonthInput) {
                                        billingMonthInput = document.createElement('input');
                                        billingMonthInput.type = 'hidden';
                                        billingMonthInput.name = 'billing_month';
                                        paymentForm.appendChild(billingMonthInput);
                                    }
                                    billingMonthInput.value = billingMonth;

                                    let paymentDateInput = paymentForm.querySelector('input[name="payment_date_selected"]');
                                    if (!paymentDateInput) {
                                        paymentDateInput = document.createElement('input');
                                        paymentDateInput.type = 'hidden';
                                        paymentDateInput.name = 'payment_date_selected';
                                        paymentForm.appendChild(paymentDateInput);
                                    }
                                    paymentDateInput.value = paymentDate;

                                    let paymentTimeInput = paymentForm.querySelector('input[name="payment_time_selected"]');
                                    if (!paymentTimeInput) {
                                        paymentTimeInput = document.createElement('input');
                                        paymentTimeInput.type = 'hidden';
                                        paymentTimeInput.name = 'payment_time_selected';
                                        paymentForm.appendChild(paymentTimeInput);
                                    }
                                    paymentTimeInput.value = paymentTime;

                                    // Display the selected info in an alert or notification
                                    const selectedMonthText = document.getElementById('billingMonth{{ $rental->id }}').options[document.getElementById('billingMonth{{ $rental->id }}').selectedIndex].text;
                                    const dateText = paymentDate ? ' on ' + paymentDate : '';
                                    const timeText = paymentTime ? ' at ' + paymentTime : '';

                                    // Update the monthly payment modal header with selected month
                                    const monthlyPaymentModalHeader = document.querySelector('#monthlyPaymentModal{{ $rental->id }} .modal-header');
                                    if (monthlyPaymentModalHeader) {
                                        const headerText = monthlyPaymentModalHeader.querySelector('.modal-title');
                                        if (headerText) {
                                            headerText.innerHTML = '<i class="fas fa-calendar-plus me-2"></i>Payment for ' + selectedMonthText;
                                        }
                                    }

                                    const paymentMonthLine = document.getElementById('monthlyPaymentMonth{{ $rental->id }}');
                                    if (paymentMonthLine) {
                                        paymentMonthLine.innerHTML = 'Billing Month: <strong>' + selectedMonthText + '</strong>' + dateText + timeText;
                                    }

                                    const paymentInfoLine = document.getElementById('monthlyPaymentInfo{{ $rental->id }}');
                                    if (paymentInfoLine) {
                                        paymentInfoLine.innerHTML = '<strong>Payment Instructions:</strong><br>Pay monthly rent to admin for ' + selectedMonthText + '. Choose your payment method, upload proof, then submit for verification.';
                                    }

                                    // Close the selection modal
                                    const selectModal = bootstrap.Modal.getInstance(document.getElementById('monthlyPaymentSelectModal{{ $rental->id }}'));
                                    selectModal.hide();

                                    // Open the payment modal
                                    const paymentModal = new bootstrap.Modal(document.getElementById('monthlyPaymentModal{{ $rental->id }}'));
                                    paymentModal.show();
                                }
                                </script>

                                <div class="modal fade" id="acceptLeaseModal{{ $rental->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius:14px;">
                                            <div class="modal-header border-0">
                                                <h6 class="modal-title fw-bold">Accept Lease Agreement</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body pt-0">
                                                <p class="mb-0">Are you sure you want to accept this lease agreement and proceed to submit advance payment?</p>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <form action="{{ route('rentals.agreement.accept', $rental) }}" method="POST" class="m-0" id="acceptForm{{ $rental->id }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success" id="acceptBtn{{ $rental->id }}">Accept Agreement</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="rejectLeaseModal{{ $rental->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius:14px;">
                                            <div class="modal-header border-0">
                                                <h6 class="modal-title fw-bold">Reject Lease Agreement</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('rentals.agreement.reject', $rental) }}" method="POST">
                                                @csrf
                                                <div class="modal-body pt-0">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Reason for rejection <span class="text-danger">*</span></label>
                                                        <textarea name="rejection_reason" rows="4" class="form-control" required placeholder="Tell us why you reject this lease agreement."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Reject Agreement</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="moveOutModal{{ $rental->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius:14px;">
                                            <div class="modal-header border-0 pb-0">
                                                <h6 class="modal-title fw-bold">Move-Out Request</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('rentals.move-out.request', $rental) }}" method="POST">
                                                @csrf
                                                <div class="modal-body pt-2">
                                                    <p class="small mb-2" style="color:#1e3a8a;">
                                                        Do you want to move out of <strong>{{ $rental->house->title ?? ('Property #' . $rental->house_id) }}</strong>?
                                                    </p>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Reason for moving out</label>
                                                        <textarea name="reason" rows="3" maxlength="1500" class="form-control"
                                                                  placeholder="Provide your reason" required></textarea>
                                                    </div>
                                                    <div>
                                                        <label class="form-label small fw-semibold">Move-out date</label>
                                                        <input type="date" name="move_out_date" class="form-control" min="{{ now()->toDateString() }}" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-outline-danger">Submit Move-Out Request</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card border-0 shadow-sm mt-4" style="border-radius:16px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-bolt me-2" style="color:#f59e0b;"></i>Quick Actions
                    </h5>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <a href="{{ route('houses.index') }}"
                               class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none"
                               style="background:#f8fafc;border:1px solid #e2e8f0;transition:background .15s;"
                               onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                                <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#1e3a5f,#2d5a8e);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fas fa-search text-white small"></i>
                                </div>
                                <div>
                                    <p class="fw-semibold small mb-0">Browse Houses</p>
                                    <p class="text-muted" style="font-size:.7rem;margin:0;">Find your next home</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a href="{{ route('rentals.my-rentals') }}"
                               class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none"
                               style="background:#f8fafc;border:1px solid #e2e8f0;transition:background .15s;"
                               onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                                <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fas fa-history text-white small"></i>
                                </div>
                                <div>
                                    <p class="fw-semibold small mb-0">Rental History</p>
                                    <p class="text-muted" style="font-size:.7rem;margin:0;">View all past rentals</p>
                                </div>
                            </a>
                        </div>
                        {{-- New: quick-launch inspection request modal --}}
                        <div class="col-sm-6">
                            <button type="button"
                               class="d-flex align-items-center gap-3 p-3 rounded-3 w-100 text-start border-0"
                               style="background:#f8fafc;border:1px solid #e2e8f0 !important;transition:background .15s;cursor:pointer;"
                               onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'"
                               data-bs-toggle="modal" data-bs-target="#inspectionModal">
                                <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fas fa-clipboard-check text-white small"></i>
                                </div>
                                <div>
                                    <p class="fw-semibold small mb-0">Request Inspection</p>
                                    <p class="text-muted" style="font-size:.7rem;margin:0;">Schedule a property visit</p>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Inspection Requests Section ──────────────────────────── --}}
            <div class="card border-0 shadow-sm mt-4" style="border-radius:16px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-clipboard-check me-2" style="color:#8b5cf6;"></i>Inspection Requests
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            @if($pendingInspections > 0)
                                <span class="badge rounded-pill" style="background:#fff7ed;color:#d97706;">
                                    {{ $pendingInspections }} pending
                                </span>
                            @endif
                            @if($confirmedInspections > 0)
                                <span class="badge rounded-pill" style="background:#f0fdf4;color:#059669;">
                                    {{ $confirmedInspections }} confirmed
                                </span>
                            @endif
                            @if($rescheduledInspections > 0)
                                <span class="badge rounded-pill" style="background:#eff6ff;color:#2563eb;">
                                    {{ $rescheduledInspections }} rescheduled
                                </span>
                            @endif
                            @if($rejectedInspections > 0)
                                <span class="badge rounded-pill" style="background:#fef2f2;color:#dc2626;">
                                    {{ $rejectedInspections }} rejected
                                </span>
                            @endif
                            <button class="btn btn-sm btn-hrs-primary" data-bs-toggle="modal" data-bs-target="#inspectionModal">
                                <i class="fas fa-plus me-1"></i>New Request
                            </button>
                        </div>
                    </div>

                    @if($inspections->isEmpty())
                        <div class="text-center py-5">
                            <div style="width:70px;height:70px;border-radius:50%;background:linear-gradient(135deg,#f5f3ff,#ede9fe);margin:0 auto 1.25rem;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-clipboard-check fs-3" style="color:#8b5cf6;"></i>
                            </div>
                            <h6 class="fw-semibold">No inspection requests yet</h6>
                            <p class="text-muted small mb-4">You can request a property inspection before committing to a rental.</p>
                            <button class="btn btn-hrs-primary" data-bs-toggle="modal" data-bs-target="#inspectionModal">
                                <i class="fas fa-calendar-plus me-2"></i>Request Inspection
                            </button>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach($inspections as $insp)
                                @php
                                    $iColor = match($insp->status) {
                                        'pending'   => ['bg'=>'#fff7ed','color'=>'#d97706','icon'=>'fa-hourglass-half'],
                                        'approved'  => ['bg'=>'#f0fdf4','color'=>'#059669','icon'=>'fa-check-circle'],
                                        'rejected'  => ['bg'=>'#fef2f2','color'=>'#dc2626','icon'=>'fa-times-circle'],
                                        'completed' => ['bg'=>'#eff6ff','color'=>'#2563eb','icon'=>'fa-flag-checkered'],
                                        default     => ['bg'=>'#f1f5f9','color'=>'#64748b','icon'=>'fa-circle'],
                                    };
                                    $inspectionDecisionPending = ! in_array($insp->tenant_decision, ['stay', 'move_out'], true);
                                @endphp
                                <div class="d-flex align-items-start gap-3 p-3 rounded-3"
                                     style="border:1px solid #e2e8f0;background:#fafafa;">
                                    {{-- status icon --}}
                                    <div style="width:44px;height:44px;border-radius:12px;background:{{ $iColor['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas {{ $iColor['icon'] }}" style="color:{{ $iColor['color'] }};font-size:1rem;"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                            <span class="fw-semibold small">{{ $insp->house->title ?? 'Property' }}</span>
                                            <span class="badge rounded-pill px-3"
                                                  style="background:{{ $iColor['bg'] }};color:{{ $iColor['color'] }};font-size:.7rem;">
                                                {{ $insp->statusLabel() }}
                                            </span>
                                        </div>
                                        <p class="text-muted small mb-1">
                                            <i class="fas fa-map-marker-alt me-1"></i>{{ $insp->house->location ?? '—' }}
                                        </p>
                                        <div class="d-flex flex-wrap gap-3 small text-muted">
                                            <span><i class="fas fa-calendar me-1"></i>{{ $insp->preferred_date->format('d M Y') }}</span>
                                            <span><i class="fas fa-clock me-1"></i>{{ $insp->preferred_time }}</span>
                                            @if($insp->scheduled_at)
                                                <span class="text-success fw-semibold">
                                                    <i class="fas fa-calendar-check me-1"></i>Scheduled: {{ $insp->scheduled_at->format('d M Y, g:i A') }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($insp->message)
                                            <p class="text-muted small mt-1 mb-0 fst-italic">"{{ Str::limit($insp->message, 80) }}"</p>
                                        @endif
                                        @if($insp->owner_notes)
                                            <div class="mt-2 p-2 rounded-2 small" style="background:#f1f5f9;border-left:3px solid #8b5cf6;">
                                                <span class="fw-semibold">Owner note:</span> {{ $insp->owner_notes }}
                                            </div>
                                        @endif

                                        @if(in_array($insp->status, ['confirmed', 'completed'], true))
                                            <div class="mt-3 p-3 rounded-3" style="background:#eff6ff;border:1px solid #bfdbfe;">
                                                <div class="d-flex align-items-start gap-2 mb-2">
                                                    <i class="fas fa-circle-question mt-1" style="color:#2563eb;"></i>
                                                    <p class="small mb-0" style="color:#1e3a8a;">Inspection is confirmed. Choose your next step.</p>
                                                </div>

                                                @if($inspectionDecisionPending)
                                                    <div class="row g-2">
                                                        <div class="col-sm-6">
                                                            <button type="button"
                                                                    class="btn btn-success w-100"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#inspectionStayModal{{ $insp->id }}">
                                                                <i class="fas fa-check-circle me-2"></i>I want to stay
                                                            </button>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <button type="button"
                                                                    class="btn btn-outline-danger w-100"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#inspectionMoveOutModal{{ $insp->id }}">
                                                                <i class="fas fa-door-open me-2"></i>No I don't want to stay
                                                            </button>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="badge rounded-pill" style="background:#f0fdf4;color:#059669;">
                                                        Decision submitted: {{ $insp->tenant_decision === 'stay' ? 'Stay' : 'Move Out' }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    {{-- Cancel button for pending --}}
                                    @if($insp->status === 'pending')
                                        <form action="{{ route('inspections.cancel', $insp) }}" method="POST"
                                              onsubmit="return confirm('Cancel this inspection request?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger flex-shrink-0">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                @if(in_array($insp->status, ['confirmed', 'completed'], true) && $inspectionDecisionPending)
                                    <div class="modal fade" id="inspectionStayModal{{ $insp->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg" style="border-radius:14px;">
                                                <div class="modal-header border-0 pb-0">
                                                    <h6 class="modal-title fw-bold">I want to stay</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('inspections.decision', $insp) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="stay">
                                                    <div class="modal-body pt-2">
                                                        <p class="small mb-3" style="color:#1e3a8a;">Do you want to continue staying in this property?</p>
                                                        <div>
                                                            <label class="form-label small fw-semibold">Message (optional)</label>
                                                            <textarea name="message" rows="3" maxlength="500" class="form-control" placeholder="Any note for owner/admin"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">Confirm Stay</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="inspectionMoveOutModal{{ $insp->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg" style="border-radius:14px;">
                                                <div class="modal-header border-0 pb-0">
                                                    <h6 class="modal-title fw-bold">No I don't want to stay</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('inspections.decision', $insp) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="move_out">
                                                    <div class="modal-body pt-2">
                                                        <p class="small text-muted mb-2">Choose move-out date and provide reason to start move-out and refund process.</p>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Move-Out Date <span class="text-danger">*</span></label>
                                                            <input type="date" name="move_out_date" class="form-control" min="{{ now()->toDateString() }}" required>
                                                        </div>
                                                        <div>
                                                            <label class="form-label small fw-semibold">Reason <span class="text-danger">*</span></label>
                                                            <textarea name="message" rows="3" maxlength="1500" class="form-control" placeholder="Why you want to move out" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-outline-danger">Confirm Move Out</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Rental Request Modal ─────────────────────────────────────────────── --}}
<div class="modal fade" id="rentalRequestModal" tabindex="-1" aria-labelledby="rentalRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#1e3a5f,#2d5a8e);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-home text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="rentalRequestModalLabel">Request Rental</h5>
                        <p class="text-muted small mb-0">Send a new rental request to property owner</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                @if($requestableHouses->isEmpty())
                    <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <p class="small text-muted mb-0">No available properties are ready for a new request right now.</p>
                    </div>
                @else
                    <form method="POST" id="rentalRequestForm" action="">
                        @csrf
                        <input type="hidden" name="house_id" id="rentalHouseInput" value="{{ old('house_id') }}">

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Selected Property</label>
                            <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
                                <h6 class="fw-semibold small mb-1" id="rentalHouseTitle">Select a house card first</h6>
                                <p class="text-muted small mb-0" id="rentalHouseMeta">House details will appear here.</p>
                            </div>
                            @error('house_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-1">
                            <label class="form-label fw-semibold small">Notes <span class="text-muted fw-normal">(optional)</span></label>
                            <textarea name="notes"
                                      rows="3"
                                      class="form-control @error('notes') is-invalid @enderror"
                                      maxlength="500"
                                      placeholder="Any details for the owner (optional)">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </form>
                @endif
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                @if($requestableHouses->isNotEmpty())
                    <button type="submit" form="rentalRequestForm" class="btn btn-hrs-primary px-4" id="rentalRequestSubmit" disabled>
                        <i class="fas fa-paper-plane me-2"></i>Submit Request
                    </button>
                @else
                    <a href="{{ route('houses.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-search me-2"></i>Browse Houses
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Inspection Request Modal ──────────────────────────────────────────── --}}
<div class="modal fade" id="inspectionModal" tabindex="-1" aria-labelledby="inspectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-clipboard-check text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="inspectionModalLabel">Request Inspection</h5>
                        <p class="text-muted small mb-0">Schedule a property visit with the owner</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <form action="{{ route('inspections.store') }}" method="POST" id="inspectionForm">
                    @csrf

                    {{-- Property select --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Property <span class="text-danger">*</span></label>
                        <select name="house_id" id="inspectionHouseSelect" class="form-select @error('house_id') is-invalid @enderror" required>
                            <option value="">Select a property to inspect…</option>
                            @foreach($availableHouses as $house)
                                <option value="{{ $house->id }}" {{ old('house_id') == $house->id ? 'selected' : '' }}>
                                    {{ $house->title }} — {{ $house->location }}
                                </option>
                            @endforeach
                        </select>
                        @error('house_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Date + Time row --}}
                    <div class="row g-3 mb-3">
                        <div class="col-7">
                            <label class="form-label fw-semibold small">Preferred Date <span class="text-danger">*</span></label>
                            <input type="date" name="preferred_date"
                                   class="form-control @error('preferred_date') is-invalid @enderror"
                                   min="{{ now()->toDateString() }}"
                                   value="{{ old('preferred_date') }}" required>
                            @error('preferred_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-semibold small">Time Slot <span class="text-danger">*</span></label>
                            <select name="preferred_time" class="form-select @error('preferred_time') is-invalid @enderror" required>
                                @foreach(['09:00' => '9:00 AM', '11:00' => '11:00 AM', '14:00' => '2:00 PM', '16:00' => '4:00 PM', '18:00' => '6:00 PM'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('preferred_time') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('preferred_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Optional message --}}
                    <div class="mb-1">
                        <label class="form-label fw-semibold small">Message to Owner <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea name="message" rows="3"
                                  class="form-control @error('message') is-invalid @enderror"
                                  placeholder="e.g. I am available any time in the morning. Please let me know which date suits you best."
                                  maxlength="1000">{{ old('message') }}</textarea>
                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="inspectionForm" class="btn btn-hrs-primary px-4">
                    <i class="fas fa-paper-plane me-2"></i>Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

<div class="pb-5"></div>
@endsection

@push('scripts')
<script>
const rentalRequestRouteMap = {
@foreach($requestableHouses as $house)
    "{{ $house->id }}": "{{ route('rentals.store', $house) }}",
@endforeach
};

const rentalHouseMetaMap = {
@foreach($requestableHouses as $house)
    "{{ $house->id }}": {
        title: "{{ addslashes($house->title) }}",
        location: "{{ addslashes($house->location ?? 'Location not set') }}",
        price: "{{ number_format((float) $house->price, 0) }}"
    },
@endforeach
};

function syncRentalRequestAction(houseId = null) {
    const houseInput = document.getElementById('rentalHouseInput');
    const rentalForm = document.getElementById('rentalRequestForm');
    const submitBtn = document.getElementById('rentalRequestSubmit');
    const titleEl = document.getElementById('rentalHouseTitle');
    const metaEl = document.getElementById('rentalHouseMeta');

    if (!houseInput || !rentalForm || !submitBtn || !titleEl || !metaEl) {
        return;
    }

    const selectedHouseId = houseId ? String(houseId) : String(houseInput.value || '');
    const requestUrl = rentalRequestRouteMap[selectedHouseId] || '';
    const houseMeta = rentalHouseMetaMap[selectedHouseId] || null;

    houseInput.value = selectedHouseId;

    rentalForm.action = requestUrl || '';
    submitBtn.disabled = !requestUrl;

    if (houseMeta) {
        titleEl.textContent = houseMeta.title;
        metaEl.textContent = houseMeta.location + ' - Nu. ' + houseMeta.price + '/mo';
    } else {
        titleEl.textContent = 'Select a house card first';
        metaEl.textContent = 'House details will appear here.';
    }
}

function openRentalRequestModal(triggerButton) {
    const houseId = triggerButton?.getAttribute('data-house-id');
    const rentalModalElement = document.getElementById('rentalRequestModal');

    if (!houseId || !rentalModalElement || !window.bootstrap?.Modal) {
        return;
    }

    syncRentalRequestAction(houseId);
    window.bootstrap.Modal.getOrCreateInstance(rentalModalElement).show();
}

function openInspectionModal(houseId) {
    const houseSelect = document.getElementById('inspectionHouseSelect');
    if (!houseSelect || !houseId) {
        return;
    }

    const optionExists = Array.from(houseSelect.options).some(option => Number(option.value) === Number(houseId));
    if (optionExists) {
        houseSelect.value = String(houseId);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const houseInput = document.getElementById('rentalHouseInput');
    if (houseInput) {
        syncRentalRequestAction(houseInput.value || null);
    }

    @if(isset($selectedRequestHouseId) && $selectedRequestHouseId > 0)
        const preselectedHouseId = "{{ $selectedRequestHouseId }}";
        const rentalModalElement = document.getElementById('rentalRequestModal');
        if (rentalModalElement && window.bootstrap?.Modal && rentalRequestRouteMap[preselectedHouseId]) {
            syncRentalRequestAction(preselectedHouseId);
            window.bootstrap.Modal.getOrCreateInstance(rentalModalElement).show();
        }
    @endif

    @if(isset($selectedInspectionHouseId) && $selectedInspectionHouseId > 0)
        const inspectionHouseId = "{{ $selectedInspectionHouseId }}";
        const inspectionModalElement = document.getElementById('inspectionModal');
        if (inspectionModalElement && window.bootstrap?.Modal) {
            openInspectionModal(inspectionHouseId);
            window.bootstrap.Modal.getOrCreateInstance(inspectionModalElement).show();
        }
    @endif

    @if($errors->has('house_id') || $errors->has('notes'))
        const rentalModalElement = document.getElementById('rentalRequestModal');
        if (rentalModalElement && window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(rentalModalElement).show();
        }
    @endif

    @if(request()->boolean('move_out'))
        const firstMoveOutModalElement = document.querySelector('[id^="moveOutModal"]');
        if (firstMoveOutModalElement && window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(firstMoveOutModalElement).show();
        }
    @endif

    @if(($focusMonthlyPayment ?? false) && ($autoMonthlyPaymentRentalId ?? null))
        const monthlyPaymentSelectModalElement = document.getElementById('monthlyPaymentSelectModal{{ $autoMonthlyPaymentRentalId }}');
        if (monthlyPaymentSelectModalElement && window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(monthlyPaymentSelectModalElement).show();
        }
    @endif
});
</script>
@endpush
