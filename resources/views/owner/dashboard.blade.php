@extends('layouts.owner')

@section('title', 'Owner Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('styles')
<style>
    .profile-card {
        background: linear-gradient(135deg, var(--ob-sidebar-bg), #065f46);
        border-radius: 16px;
        color: #fff;
        padding: 1.5rem;
        position: relative; overflow: hidden;
    }
    .profile-card::before {
        content: '';
        position: absolute; top: -40px; right: -40px;
        width: 160px; height: 160px;
        background: rgba(255,255,255,.06); border-radius: 50%;
    }
    .profile-card::after {
        content: '';
        position: absolute; bottom: -30px; right: 40px;
        width: 100px; height: 100px;
        background: rgba(255,255,255,.04); border-radius: 50%;
    }
    .profile-avatar {
        width: 62px; height: 62px; border-radius: 50%;
        background: rgba(255,255,255,.18); border: 2.5px solid rgba(255,255,255,.3);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; font-weight: 800; flex-shrink: 0;
    }
    .mini-stat {
        background: rgba(255,255,255,.12); border-radius: 8px;
        padding: .45rem .8rem; text-align: center;
    }
    .mini-stat-val { font-size: 1.1rem; font-weight: 800; line-height: 1; }
    .mini-stat-lbl { font-size: .6rem; opacity: .7; text-transform: uppercase; letter-spacing: .05em; }
    .revenue-bar { height: 8px; border-radius: 100px; background: #e2e8f0; overflow: hidden; }
    .revenue-bar-fill { height: 100%; border-radius: 100px; background: var(--ob-accent); }
</style>
@endpush

@section('content')

{{-- ── Page Header ────────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="mb-0" style="font-size:1.4rem;font-weight:800;color:#0f172a;">
            Welcome back, {{ $owner->name }} <span style="font-size:1.25rem;">👋</span>
        </h1>
        <p class="text-muted mb-0" style="font-size:.82rem;">
            Manage your properties and track payments — {{ now()->format('l, d F Y') }}
        </p>
    </div>
    <a href="{{ route('houses.create') }}" class="btn btn-sm d-flex align-items-center gap-2"
       style="background:var(--ob-accent);color:#fff;border-radius:10px;font-weight:600;font-size:.82rem;padding:.5rem 1rem;">
        <i class="fas fa-plus"></i> Add Property
    </a>
</div>

<div class="row g-3">

    {{-- ── Recent Notifications ─────────────────────────────────────────── --}}
    <div class="col-12 col-lg-5">
        <div class="ob-card h-100">
            <div class="ob-card-header">
                <h6><i class="fas fa-bell me-2" style="color:var(--ob-accent);"></i>Recent Notifications</h6>
                @if($recentNotifications->isNotEmpty())
                <form action="{{ route('owner.notifications.clear') }}" method="POST" onsubmit="return confirm('Clear all unread notifications?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;">
                        <i class="fas fa-check-double me-1"></i>Clear All
                    </button>
                </form>
                @endif
            </div>
            <div class="p-3">
                @forelse($recentNotifications as $notification)
                @php
                    $message = $notification->data['message'] ?? 'Notification';
                    $title = $notification->data['title'] ?? 'Update';
                    $type = $notification->type;
                @endphp
                <div class="border rounded-3 p-3 mb-2" style="background:#f8fafc;">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="fw-semibold" style="font-size:.85rem;">{{ $title }}</div>
                            <div style="font-size:.78rem;color:#475569;white-space:pre-line;">{{ $message }}</div>
                            @if($type === 'property_inspection_scheduled' && !empty($notification->data['scheduled_at']))
                            <div class="mt-1" style="font-size:.78rem;color:#0f766e;">
                                <i class="fas fa-calendar-check me-1"></i>
                                Inspection time: {{ \Carbon\Carbon::parse($notification->data['scheduled_at'])->format('d M Y, h:i A') }}
                            </div>
                            @endif
                        </div>
                        <span class="chip chip-gray" style="font-size:.65rem;">{{ $notification->created_at?->diffForHumans() }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted" style="font-size:.85rem;">
                    <i class="fas fa-bell d-block mb-2" style="font-size:1.8rem;opacity:.3;"></i>
                    No notifications yet.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Properties Overview ────────────────────────────────────────────── --}}
    <div class="col-12 col-lg-7">
        <div class="ob-card">
            <div class="ob-card-header">
                <h6><i class="fas fa-building me-2" style="color:var(--ob-accent);"></i>Your Properties</h6>
                <a href="{{ route('owner.properties') }}" style="font-size:.75rem;color:var(--ob-accent);">Manage all →</a>
            </div>
            <div class="p-3">
                @forelse($properties as $prop)
                @php
                    $activeLease = $prop->rentals->first();
                    $displayStatus = in_array($prop->status, ['pending', 'rejected'], true)
                        ? $prop->status
                        : ($activeLease ? 'rented' : 'available');
                    $statusColors = [
                        'available' => 'chip-green',
                        'rented'    => 'chip-blue',
                        'pending'   => 'chip-yellow',
                        'rejected'  => 'chip-red',
                    ];
                @endphp
                <div class="border rounded-3 p-3 mb-3" style="background:#f8fafc;">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            @if($prop->image)
                            <img src="{{ $prop->getImageUrlAttribute() }}" alt="" class="rounded-2"
                                 style="width:80px;height:60px;object-fit:cover;">
                            @else
                            <div class="rounded-2 d-flex align-items-center justify-content-center"
                                 style="width:80px;height:60px;background:#e2e8f0;">
                                <i class="fas fa-home" style="color:#94a3b8;"></i>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-1">{{ $prop->title }}</h6>
                            <p class="text-muted mb-1" style="font-size:.85rem;">{{ $prop->location?->name ?? 'Location not set' }}</p>
                            <p class="mb-0" style="font-size:.9rem;font-weight:600;color:var(--ob-accent);">Nu {{ number_format($prop->price) }}/month</p>
                            @if(in_array($prop->status, ['pending', 'rejected'], true))
                            <p class="mb-0 mt-1" style="font-size:.78rem;color:#475569;">
                                <i class="fas fa-calendar-check me-1"></i>
                                Inspection: {{ $prop->inspection_scheduled_at ? $prop->inspection_scheduled_at->format('d M Y, h:i A') : 'Waiting for admin schedule' }}
                            </p>
                            @endif
                        </div>
                        <div class="col-md-3 text-end">
                            <span class="chip {{ $statusColors[$displayStatus] ?? 'chip-gray' }} mb-2">{{ ucfirst($displayStatus) }}</span>
                            @if($activeLease && $activeLease->tenant)
                            <div class="small text-muted">
                                <i class="fas fa-user me-1"></i>{{ $activeLease->tenant->name }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted" style="font-size:.85rem;">
                    <i class="fas fa-building d-block mb-2" style="font-size:2rem;opacity:.3;"></i>
                    No properties listed yet.<br>
                    <a href="{{ route('houses.create') }}" style="color:var(--ob-accent);">Add your first property →</a>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Payment Tracking ──────────────────────────────────────────────── --}}
    <div class="col-12 col-md-6">
        <div class="ob-card h-100">
            <div class="ob-card-header">
                <h6><i class="fas fa-calendar-alt me-2" style="color:var(--ob-accent);"></i>Monthly Payments</h6>
                <a href="{{ route('owner.payments') }}" style="font-size:.75rem;color:var(--ob-accent);">View all →</a>
            </div>
            @if($monthlyPayments->isNotEmpty())
            <div class="p-3">
                @foreach($monthlyPayments as $payment)
                @php
                    $statusClass = $payment->status === 'paid' ? 'text-success' : ($payment->status === 'overdue' ? 'text-danger' : 'text-warning');
                @endphp
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <div class="fw-600" style="font-size:.85rem;">{{ $payment->tenant?->name ?? 'Tenant' }}</div>
                        <div class="small text-muted">{{ $payment->rental?->house?->title ?? 'Property' }}</div>
                        <div class="small text-muted">{{ $payment->paymentTypeLabel() }} · {{ $payment->billingMonthLabel() }}</div>
                    </div>
                    <div class="text-end">
                        <div class="fw-600 {{ $statusClass }}">Nu {{ number_format($payment->amount) }}</div>
                        <div class="small text-muted">{{ $payment->payment_date?->format('d M Y') ?? 'Date not set' }}</div>
                        <span class="chip chip-{{ $payment->status === 'paid' ? 'green' : ($payment->status === 'overdue' ? 'red' : 'yellow') }}" style="font-size:.65rem;">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-4 text-muted" style="font-size:.85rem;">
                <i class="fas fa-calendar-alt d-block mb-2" style="font-size:1.8rem;opacity:.3;"></i>
                No monthly payments yet.
            </div>
            @endif
        </div>
    </div>

</div>

@endsection
