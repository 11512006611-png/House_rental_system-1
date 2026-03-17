@extends('layouts.app')

@section('title', 'Tenant Dashboard')

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
                <div class="td-hero-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
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
        {{-- ── Left column: Notifications + Process Guide ──────────────── --}}
        <div class="col-lg-4">

            {{-- Notifications --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-bell me-2" style="color:#f59e0b;"></i>Notifications
                        </h5>
                        @if($notifications->isNotEmpty())
                        <span class="badge rounded-pill bg-hrs-primary text-white">{{ $notifications->count() }}</span>
                        @endif
                    </div>

                    @if($notifications->isNotEmpty())
                        <div class="d-flex flex-column gap-2">
                            @foreach($notifications->take(8) as $notif)
                                @php
                                    $icon = match($notif['type']) {
                                        'success' => 'fa-check-circle',
                                        'info'    => 'fa-info-circle',
                                        'danger'  => 'fa-exclamation-circle',
                                        default   => 'fa-bell',
                                    };
                                    $iconColor = match($notif['type']) {
                                        'success' => '#10b981',
                                        'info'    => '#3b82f6',
                                        'danger'  => '#ef4444',
                                        default   => '#f59e0b',
                                    };
                                    $iconBg = match($notif['type']) {
                                        'success' => '#f0fdf4',
                                        'info'    => '#eff6ff',
                                        'danger'  => '#fef2f2',
                                        default   => '#fff7ed',
                                    };
                                @endphp
                                <div class="td-notif-item {{ $notif['type'] }}">
                                    <div class="td-notif-icon" style="background:{{ $iconBg }};">
                                        <i class="fas {{ $icon }}" style="color:{{ $iconColor }};"></i>
                                    </div>
                                    <p class="mb-0 small lh-sm">{{ $notif['message'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div style="width:56px;height:56px;border-radius:50%;background:#f8fafc;margin:0 auto 1rem;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-bell-slash text-muted fs-5"></i>
                            </div>
                            <p class="text-muted small mb-0">No new notifications at the moment.</p>
                        </div>
                    @endif
                </div>
            </div>

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
        </div>

        {{-- ── Right column: Rental Requests ───────────────────────────── --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-file-contract me-2" style="color:#1e3a5f;"></i>My Rental Requests
                        </h5>
                        <a href="{{ route('rentals.my-rentals') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-external-link-alt me-1"></i>Full History
                        </a>
                    </div>

                    @if($rentals->isEmpty())
                        <div class="text-center py-5">
                            <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#f8fafc,#e2e8f0);margin:0 auto 1.25rem;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-home text-muted fs-3"></i>
                            </div>
                            <h6 class="fw-semibold">No rental requests yet</h6>
                            <p class="text-muted small mb-4">Start by browsing available properties and sending your first request.</p>
                            <a href="{{ route('houses.index') }}" class="btn btn-hrs-primary">
                                <i class="fas fa-search me-2"></i>Browse Houses
                            </a>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-4">
                            @foreach($rentals as $rental)
                                @php
                                    $payment = $rental->payments->sortByDesc('payment_date')->first();
                                    $isPaid  = $payment && $payment->status === 'paid';
                                    $isPaymentVerified = $payment && $payment->verification_status === 'verified';
                                    $inspectionCompleted = isset($completedInspectionHouseIds)
                                        ? $completedInspectionHouseIds->contains($rental->house_id)
                                        : false;
                                    $leaseAgreement = $rental->leaseAgreement;
                                    $latestMoveOut = $rental->moveOutRequests->sortByDesc('created_at')->first();
                                    $needsStayDecision = $rental->status === 'active' && $inspectionCompleted && $rental->lease_status === 'not_requested';

                                    /* ── Compute step 0–5 (0 = stopped/rejected) ── */
                                    $step = 1;
                                    if ($rental->status === 'active')              $step = 2;
                                    if ($rental->lease_status === 'requested')     $step = 3;
                                    if ($isPaid)                                   $step = 3;
                                    if ($isPaid && $rental->lease_status === 'requested') $step = 4;
                                    if ($rental->lease_status === 'approved')      $step = 5;
                                    if (in_array($rental->status, ['cancelled','rejected']) || $rental->lease_status === 'rejected') $step = 0;

                                    $statusBadge = match ($rental->status) {
                                        'pending'    => ['bg'=>'#fff7ed','color'=>'#d97706','label'=>'Pending'],
                                        'active'     => ['bg'=>'#f0fdf4','color'=>'#059669','label'=>'Approved'],
                                        'cancelled'  => ['bg'=>'#fef2f2','color'=>'#dc2626','label'=>'Rejected'],
                                        default      => ['bg'=>'#f1f5f9','color'=>'#64748b','label'=>ucfirst($rental->status)],
                                    };

                                    $steps = [
                                        1 => 'Requested',
                                        2 => 'Accepted',
                                        3 => 'Stay Confirmed',
                                        4 => 'Lease Sent',
                                        5 => 'Complete',
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
                                        @if($step === 0)
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
                                        @else
                                            <div class="td-stepper mb-4">
                                                @foreach($steps as $num => $label)
                                                    @php
                                                        $dotClass = $num < $step ? 'done' : ($num === $step ? 'active' : '');
                                                    @endphp
                                                    <div class="td-step {{ $dotClass }}">
                                                        <div class="td-step-dot">
                                                            @if($num < $step)
                                                                <i class="fas fa-check" style="font-size:.6rem;"></i>
                                                            @elseif($num === $step && $step === 5)
                                                                <i class="fas fa-trophy" style="font-size:.65rem;"></i>
                                                            @else
                                                                {{ $num }}
                                                            @endif
                                                        </div>
                                                        <span class="td-step-label">{{ $label }}</span>
                                                    </div>
                                                @endforeach
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
                                            @if($isPaymentVerified)
                                                <span class="badge rounded-pill" style="background:#f0fdf4;color:#059669;">
                                                    <i class="fas fa-check me-1"></i>Payment Verified
                                                </span>
                                            @elseif($payment && $payment->verification_status === 'pending')
                                                <span class="badge rounded-pill" style="background:#fff7ed;color:#d97706;">
                                                    <i class="fas fa-clock me-1"></i>Payment Verification Pending
                                                </span>
                                            @elseif($payment && $payment->verification_status === 'rejected')
                                                <span class="badge rounded-pill" style="background:#fef2f2;color:#dc2626;">
                                                    <i class="fas fa-times me-1"></i>Payment Rejected
                                                </span>
                                            @elseif($payment)
                                                <span class="badge rounded-pill" style="background:#fff7ed;color:#d97706;">
                                                    <i class="fas fa-clock me-1"></i>Payment {{ ucfirst($payment->status) }}
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

                                            @if($rental->lease_status === 'approved')
                                                <span class="badge rounded-pill" style="background:#f0fdf4;color:#059669;">
                                                    <i class="fas fa-file-signature me-1"></i>Lease Approved
                                                </span>
                                            @elseif($rental->lease_status === 'requested')
                                                <span class="badge rounded-pill" style="background:#eff6ff;color:#2563eb;">
                                                    <i class="fas fa-hourglass-half me-1"></i>Lease Pending Review
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
                                                    <p class="text-muted small mb-0">Your lease agreement has been approved. Congratulations!</p>
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
                                                <div class="d-flex flex-wrap gap-2">
                                                    <form action="{{ route('rentals.stay-decision', $rental) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="decision" value="yes">
                                                        <button type="submit" class="btn btn-success btn-sm px-4">
                                                            <i class="fas fa-check me-1"></i>Yes, I Want To Stay
                                                        </button>
                                                    </form>

                                                    <button type="button"
                                                            class="btn btn-outline-danger btn-sm px-4"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#stayNoModal{{ $rental->id }}">
                                                        <i class="fas fa-times me-1"></i>No, Not Interested
                                                    </button>
                                                </div>
                                            </div>
                                        @elseif($rental->status === 'active')
                                            <div class="row g-2">
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
                                                    @if(!$payment || $payment->verification_status === 'rejected')
                                                        @if($leaseAgreement)
                                                            <button type="button" class="btn btn-success w-100"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#paymentModal{{ $rental->id }}">
                                                                <i class="fas fa-credit-card me-2"></i>Submit Advance Payment
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-secondary w-100" disabled>
                                                                <i class="fas fa-file-contract me-2"></i>Waiting For Lease Upload
                                                            </button>
                                                        @endif
                                                    @elseif($payment->verification_status === 'pending')
                                                        <button type="button" class="btn btn-warning w-100 text-dark" disabled>
                                                            <i class="fas fa-clock me-2"></i>Payment Verification Pending
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-success w-100" disabled>
                                                            <i class="fas fa-check-circle me-2"></i>Payment Verified
                                                        </button>
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
                                        @elseif($rental->lease_status === 'requested')
                                            <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background:#eff6ff;border:1px solid #bfdbfe;">
                                                <i class="fas fa-clock" style="color:#3b82f6;"></i>
                                                <p class="text-primary small mb-0 fw-semibold">Lease agreement is awaiting owner's review…</p>
                                            </div>
                                        @elseif($rental->status === 'pending')
                                            <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background:#fff7ed;border:1px solid #fed7aa;">
                                                <i class="fas fa-hourglass-half" style="color:#f59e0b;"></i>
                                                <p class="small mb-0" style="color:#92400e;">Waiting for owner to review your request…</p>
                                            </div>
                                        @elseif($step === 0)
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
                                <div class="modal fade" id="stayNoModal{{ $rental->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius:14px;">
                                            <div class="modal-header border-0 pb-0">
                                                <h6 class="modal-title fw-bold">Decline This Property</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('rentals.stay-decision', $rental) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="decision" value="no">
                                                <div class="modal-body pt-2">
                                                    <p class="small text-muted mb-2">Optional message for owner:</p>
                                                    <textarea name="message" rows="3" maxlength="500" class="form-control"
                                                              placeholder="Reason or comment (optional)"></textarea>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-outline-danger">Confirm No</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="paymentModal{{ $rental->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius:14px;">
                                            <div class="modal-header border-0 pb-0">
                                                <h6 class="modal-title fw-bold">Submit Advance Payment</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('rentals.pay', $rental) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-body pt-2">
                                                    <p class="small text-muted mb-2">Provide transaction ID or payment proof file.</p>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Transaction ID (optional if file attached)</label>
                                                        <input type="text" name="transaction_id" maxlength="120" class="form-control"
                                                               placeholder="Example: TXN-123456789">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Payment proof file (optional if transaction ID provided)</label>
                                                        <input type="file" name="payment_proof" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                                    </div>
                                                    <div>
                                                        <label class="form-label small fw-semibold">Note (optional)</label>
                                                        <textarea name="notes" rows="3" maxlength="500" class="form-control"
                                                                  placeholder="Additional payment details"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">Submit For Verification</button>
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
                                                        <label class="form-label small fw-semibold">Move-out date (optional)</label>
                                                        <input type="date" name="move_out_date" class="form-control" min="{{ now()->toDateString() }}">
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
                            @if($approvedInspections > 0)
                                <span class="badge rounded-pill" style="background:#f0fdf4;color:#059669;">
                                    {{ $approvedInspections }} approved
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
                            @endforeach
                        </div>
                    @endif
                </div>
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
                                @foreach(['Morning','Afternoon','Evening'] as $slot)
                                    <option value="{{ $slot }}" {{ old('preferred_time', 'Morning') === $slot ? 'selected' : '' }}>
                                        {{ $slot }}
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
</script>
@endpush
