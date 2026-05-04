<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Owner Dashboard') | HRS Bhutan</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --ob-sidebar-bg   : #064e3b;
            --ob-sidebar-dark : #022c22;
            --ob-accent       : #10b981;
            --ob-accent-light : #d1fae5;
            --ob-sidebar-w    : 255px;
            --ob-topbar-h     : 60px;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; color: #1e293b; margin: 0; }

        /* ── Sidebar ── */
        .ob-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--ob-sidebar-w);
            background: var(--ob-sidebar-bg);
            display: flex; flex-direction: column;
            z-index: 1040; overflow-y: auto;
            transition: transform .28s ease;
        }
        .ob-brand {
            padding: 1.1rem 1.2rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
            flex-shrink: 0;
        }
        .ob-nav-section {
            padding: .7rem 1rem .25rem;
            font-size: .62rem;
            font-weight: 700;
            letter-spacing: .09em;
            color: rgba(255,255,255,.35);
            text-transform: uppercase;
        }
        .ob-nav-link {
            display: flex; align-items: center; gap: .7rem;
            padding: .55rem 1.1rem;
            color: rgba(255,255,255,.72);
            text-decoration: none;
            font-size: .83rem;
            font-weight: 500;
            border-radius: 8px;
            margin: .1rem .65rem;
            transition: background .18s, color .18s;
        }
        .ob-nav-link:hover   { background: rgba(255,255,255,.09); color: #fff; }
        .ob-nav-link.active  { background: var(--ob-accent); color: #fff; font-weight: 600; }
        .ob-nav-link .icon   { width: 20px; text-align: center; font-size: .9rem; flex-shrink: 0; }
        .ob-nav-link .badge  {
            margin-left: auto; font-size: .6rem; padding: .2rem .45rem;
            background: rgba(255,255,255,.15); color: #fff; border-radius: 20px;
        }
        .ob-nav-link.active .badge { background: rgba(0,0,0,.2); }
        .ob-sidebar-footer {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,.08);
            padding: .9rem 1rem;
        }

        /* ── Content ── */
        .ob-content {
            margin-left: var(--ob-sidebar-w);
            min-height: 100vh;
            display: flex; flex-direction: column;
        }

        /* ── Topbar ── */
        .ob-topbar {
            position: sticky; top: 0; z-index: 1030;
            height: var(--ob-topbar-h);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center;
            padding: 0 1.5rem; gap: 1rem;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        .ob-topbar .breadcrumb { margin: 0; font-size: .8rem; }
        .ob-topbar .breadcrumb-item a { color: var(--ob-accent); text-decoration: none; }
        .ob-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--ob-accent-light); color: var(--ob-accent);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .82rem; flex-shrink: 0;
        }

        /* ── Main area ── */
        .ob-main {
            flex: 1;
            padding: 1.5rem;
        }
        .page-header { margin-bottom: 1.4rem; }
        .page-header h1 { font-size: 1.45rem; font-weight: 800; color: #0f172a; margin-bottom: .2rem; }
        .page-header p  { font-size: .85rem; color: #64748b; margin: 0; }

        /* ── Cards ── */
        .ob-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 1px 6px rgba(0,0,0,.06);
            border: 1px solid #f1f5f9;
        }
        .ob-card-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: .85rem 1.1rem .7rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .ob-card-header h6 { margin: 0; font-size: .85rem; font-weight: 700; color: #0f172a; }

        /* ── Stat cards ── */
        .stat-card {
            background: #fff; border-radius: 14px;
            padding: 1.1rem 1.2rem;
            box-shadow: 0 1px 6px rgba(0,0,0,.06);
            border: 1px solid #f1f5f9;
            transition: box-shadow .2s, transform .2s;
        }
        .stat-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.1); transform: translateY(-2px); }
        .stat-icon {
            width: 42px; height: 42px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0;
        }
        .stat-value { font-size: 1.7rem; font-weight: 800; line-height: 1.2; margin: .4rem 0 .1rem; }
        .stat-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b; }
        .stat-footer { font-size: .72rem; color: #94a3b8; margin-top: .4rem; }

        /* ── Chips ── */
        .chip {
            display: inline-flex; align-items: center ;
            padding: .18rem .6rem; border-radius: 20px;
            font-size: .7rem; font-weight: 700; letter-spacing: .03em;
        }
        .chip-green  { background:#dcfce7; color:#15803d; }
        .chip-blue   { background:#dbeafe; color:#1d4ed8; }
        .chip-orange { background:#ffedd5; color:#c2410c; }
        .chip-red    { background:#fee2e2; color:#b91c1c; }
        .chip-yellow { background:#fef9c3; color:#a16207; }
        .chip-gray   { background:#f1f5f9; color:#475569; }
        .chip-teal   { background:#ccfbf1; color:#0f766e; }

        /* ── Table ── */
        .ob-table th {
            font-size: .7rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .05em; color: #64748b;
            background: #f8fafc; border-bottom: 1px solid #e2e8f0 !important;
            padding: .65rem 1rem;
        }
        .ob-table td { padding: .7rem 1rem; font-size: .84rem; vertical-align: middle; border-color: #f1f5f9; }
        .ob-table tbody tr:hover { background: #f8fafc; }

        /* ── Quick action buttons ── */
        .qa-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: .7rem;
        }
        .qa-btn {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: .4rem; padding: .9rem .5rem;
            border-radius: 12px; border: 1.5px solid;
            text-decoration: none; font-size: .78rem; font-weight: 600;
            text-align: center; transition: all .18s;
        }
        .qa-btn i { font-size: 1.4rem; }
        .qa-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 14px rgba(0,0,0,.1); }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .ob-sidebar  { transform: translateX(-100%); }
            .ob-sidebar.open { transform: translateX(0); }
            .ob-content  { margin-left: 0; }
        }
    </style>

    @stack('styles')
</head>
<body>

@php
    $__ownerPendingProps = \App\Models\House::where('owner_id', auth()->id())->where('status', 'pending')->count();
    $__pendingRentals    = \App\Models\Rental::whereIn('house_id', \App\Models\House::where('owner_id', auth()->id())->pluck('id'))->where('status', 'pending')->count();
    $__overduePayments   = \App\Models\Payment::whereHas('rental', fn($q) => $q->whereIn('house_id', \App\Models\House::where('owner_id', auth()->id())->pluck('id')))->where('status', 'overdue')->count();
    $__pendingMoveOuts   = \App\Models\MoveOutRequest::where('owner_id', auth()->id())->whereIn('status', ['requested', 'approved'])->count();
    $__workflowUnreadCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
    $__workflowUnreadItems = auth()->check()
        ? auth()->user()->unreadNotifications()->latest()->take(5)->get()
        : collect();
    $__notifTotal        = $__ownerPendingProps + $__pendingRentals + $__overduePayments + $__pendingMoveOuts + $__workflowUnreadCount;
@endphp

<div class="d-flex" id="ownerWrapper">

    {{-- ══ Sidebar ══════════════════════════════════════════════════════════ --}}
    <nav class="ob-sidebar" id="ownerSidebar">

        {{-- Brand --}}
        <div class="ob-brand">
            <a href="{{ route('owner.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <div style="width:38px;height:38px;background:var(--ob-accent);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-home text-white" style="font-size:.95rem;"></i>
                </div>
                <div>
                    <div class="text-white fw-bold" style="font-size:.92rem;line-height:1.2;">HRS Bhutan</div>
                    <div style="font-size:.58rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.12em;">Owner Panel</div>
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <div class="pt-2 pb-3 flex-fill">
            <div class="ob-nav-section">Overview</div>
            <a href="{{ route('owner.dashboard') }}"
               class="ob-nav-link {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-gauge-high"></i></span>
                Dashboard
            </a>

            <div class="ob-nav-section">Properties</div>
            <a href="{{ route('owner.properties') }}"
               class="ob-nav-link {{ request()->routeIs('owner.properties') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-building"></i></span>
                My Properties
                @if($__ownerPendingProps > 0)
                <span class="badge">{{ $__ownerPendingProps }}</span>
                @endif
            </a>
            <a href="{{ route('houses.create') }}"
               class="ob-nav-link">
                <span class="icon"><i class="fas fa-plus-circle"></i></span>
                Add Property
            </a>
            <a href="{{ route('houses.my-listings') }}"
               class="ob-nav-link {{ request()->routeIs('houses.my-listings') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-list"></i></span>
                All Listings
            </a>

            <div class="ob-nav-section">Tenants & Income</div>
            <a href="{{ route('owner.tenants') }}"
               class="ob-nav-link {{ request()->routeIs('owner.tenants') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-users"></i></span>
                My Tenants
                @if($__pendingRentals > 0)
                <span class="badge">{{ $__pendingRentals }}</span>
                @endif
            </a>
                <a href="{{ route('owner.tenants', ['move_out' => 1]) }}"
                    class="ob-nav-link {{ request()->routeIs('owner.tenants') && request('move_out') ? 'active' : '' }}">
                     <span class="icon"><i class="fas fa-door-open"></i></span>
                     Move-Out Tenants
                     @if($__pendingMoveOuts > 0)
                     <span class="badge" style="background:#ef4444;">{{ $__pendingMoveOuts }}</span>
                     @endif
                </a>
            <a href="{{ route('owner.payments') }}"
               class="ob-nav-link {{ request()->routeIs('owner.payments') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-money-bill-wave"></i></span>
                Payments
                @if($__overduePayments > 0)
                <span class="badge" style="background:#ef4444;">{{ $__overduePayments }}</span>
                @endif
            </a>
            <a href="{{ route('owner.earnings') }}"
               class="ob-nav-link {{ request()->routeIs('owner.earnings') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-chart-line"></i></span>
                Monthly Earnings
            </a>

            <div class="ob-nav-section">Account</div>
                <a href="{{ route('profile.show') }}"
                    class="ob-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                     <span class="icon"><i class="fas fa-id-card"></i></span>
                     My Profile
                </a>
            <a href="{{ route('houses.index') }}"
               class="ob-nav-link">
                <span class="icon"><i class="fas fa-globe"></i></span>
                Browse Listings
            </a>
            <a href="{{ route('home') }}"
               class="ob-nav-link">
                <span class="icon"><i class="fas fa-house-chimney"></i></span>
                Back to Site
            </a>
        </div>

        {{-- Sidebar Footer: Logout --}}
        <div class="ob-sidebar-footer">
            <div class="d-flex align-items-center gap-2 mb-3">
                @if(Auth::user()->profile_image_url)
                    <img src="{{ Auth::user()->profile_image_url }}" alt="User avatar" class="ob-avatar" style="object-fit:cover;">
                @else
                    <div class="ob-avatar">{{ strtoupper(substr(Auth::user()->username ?: Auth::user()->name, 0, 1)) }}</div>
                @endif
                <div style="overflow:hidden;">
                    <div class="text-white fw-semibold text-truncate" style="font-size:.8rem;">{{ Auth::user()->name }}</div>
                    <div style="font-size:.67rem;color:rgba(255,255,255,.45);">Property Owner</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn w-100 d-flex align-items-center justify-content-center gap-2"
                    style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.75);font-size:.8rem;border-radius:8px;border:none;padding:.5rem;">
                    <i class="fas fa-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    {{-- ══ Main Content ══════════════════════════════════════════════════════ --}}
    <div class="ob-content" id="ownerContent">

        {{-- Topbar --}}
        <div class="ob-topbar">
            {{-- Mobile toggle --}}
            <button class="btn btn-sm btn-light d-md-none me-1" id="sidebarToggle" style="border:none;"
                    onclick="document.getElementById('ownerSidebar').classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="flex-fill">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('owner.dashboard') }}">Owner</a>
                    </li>
                    @yield('breadcrumb')
                </ol>
            </nav>

            {{-- Notification bell --}}
            <div class="dropdown">
                <button class="btn btn-sm position-relative"
                        style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:.38rem .6rem;"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell" style="color:#475569;font-size:.95rem;"></i>
                    @if($__notifTotal > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                          style="font-size:.55rem;">{{ $__notifTotal }}</span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:270px;border-radius:12px;border:1px solid #e2e8f0;">
                    <li class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between gap-2">
                        <span class="fw-700" style="font-size:.8rem;color:#0f172a;">Notifications</span>
                    </li>
                    @if($__workflowUnreadItems->isNotEmpty())
                    <li class="px-3 pt-2 pb-1" style="font-size:.68rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">
                        Workflow Alerts
                    </li>
                    @foreach($__workflowUnreadItems as $__note)
                    @php
                        $__type = $__note->data['type'] ?? null;
                        $__title = $__note->data['title'] ?? 'Update';
                        $__message = $__note->data['message'] ?? 'You have a new update.';

                        $__target = match ($__type) {
                            'payment_submitted', 'payment_pending_verification', 'advance_payment_completed', 'advance_payment_rejected' => route('owner.payments'),
                            'tenant_confirmed_stay', 'lease_sent', 'agreement_accepted_by_tenant' => route('owner.tenants', ['lease_queue' => 1]),
                            'move_out_requested', 'move_out_approved', 'move_out_completed', 'move_out_rejected' => route('owner.tenants', ['move_out' => 1]),
                            default => route('owner.dashboard'),
                        };
                    @endphp
                    <li>
                        <a href="{{ $__target }}" class="dropdown-item py-2" style="white-space:normal;line-height:1.3;">
                            <div class="fw-semibold" style="font-size:.76rem;color:#0f172a;">{{ $__title }}</div>
                            <div style="font-size:.72rem;color:#475569;">{{ \Illuminate\Support\Str::limit($__message, 95) }}</div>
                            <div style="font-size:.66rem;color:#94a3b8;">{{ $__note->created_at?->diffForHumans() }}</div>
                        </a>
                    </li>
                    @endforeach
                    <li><hr class="dropdown-divider my-1"></li>
                    @endif
                    @if($__ownerPendingProps > 0)
                    <li>
                        <a href="{{ route('owner.properties') }}?status=pending" class="dropdown-item d-flex align-items-center gap-2 py-2" style="font-size:.8rem;">
                            <span style="width:28px;height:28px;background:#fef9c3;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-hourglass-half text-warning" style="font-size:.75rem;"></i>
                            </span>
                            <span>{{ $__ownerPendingProps }} propert{{ $__ownerPendingProps > 1 ? 'ies' : 'y' }} pending approval</span>
                        </a>
                    </li>
                    @endif
                    @if($__pendingRentals > 0)
                    <li>
                        <a href="{{ route('owner.tenants') }}?status=pending" class="dropdown-item d-flex align-items-center gap-2 py-2" style="font-size:.8rem;">
                            <span style="width:28px;height:28px;background:#dbeafe;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-file-contract text-primary" style="font-size:.75rem;"></i>
                            </span>
                            <span>{{ $__pendingRentals }} rental request{{ $__pendingRentals > 1 ? 's' : '' }} pending</span>
                        </a>
                    </li>
                    @endif
                    @if($__overduePayments > 0)
                    <li>
                        <a href="{{ route('owner.payments') }}?status=overdue" class="dropdown-item d-flex align-items-center gap-2 py-2" style="font-size:.8rem;">
                            <span style="width:28px;height:28px;background:#fee2e2;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-circle-exclamation text-danger" style="font-size:.75rem;"></i>
                            </span>
                            <span>{{ $__overduePayments }} overdue payment{{ $__overduePayments > 1 ? 's' : '' }}</span>
                        </a>
                    </li>
                    @endif
                    @if($__pendingMoveOuts > 0)
                    <li>
                        <a href="{{ route('owner.tenants', ['move_out' => 1]) }}" class="dropdown-item d-flex align-items-center gap-2 py-2" style="font-size:.8rem;">
                            <span style="width:28px;height:28px;background:#fee2e2;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-door-open text-danger" style="font-size:.75rem;"></i>
                            </span>
                            <span>{{ $__pendingMoveOuts }} move-out request{{ $__pendingMoveOuts > 1 ? 's' : '' }}</span>
                        </a>
                    </li>
                    @endif
                    @if($__notifTotal === 0)
                    <li class="px-3 py-3 text-center text-muted" style="font-size:.8rem;">
                        <i class="fas fa-circle-check text-success me-1"></i> All caught up!
                    </li>
                    @endif
                    @if($__workflowUnreadCount > 0)
                    <li><hr class="dropdown-divider my-1"></li>
                    <li class="px-3 py-2">
                        <form action="{{ route('owner.notifications.clear') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary w-100" style="font-size:.72rem;">
                                <i class="fas fa-broom me-1"></i>Clear All
                            </button>
                        </form>
                    </li>
                    @endif
                </ul>
            </div>

            {{-- User info --}}
            <div class="d-flex align-items-center gap-2 ms-1">
                @if(Auth::user()->profile_image_url)
                    <img src="{{ Auth::user()->profile_image_url }}" alt="User avatar" class="ob-avatar" style="object-fit:cover;">
                @else
                    <div class="ob-avatar">{{ strtoupper(substr(Auth::user()->username ?: Auth::user()->name, 0, 1)) }}</div>
                @endif
                <div class="d-none d-md-block">
                    <div class="fw-600" style="font-size:.82rem;color:#0f172a;line-height:1.2;">{{ Auth::user()->name }}</div>
                    <div style="font-size:.68rem;color:#94a3b8;">Property Owner</div>
                </div>
            </div>
        </div>

        {{-- Flash messages --}}
        <div class="px-3 pt-3">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-2" role="alert"
                 style="border-radius:10px;font-size:.85rem;">
                <i class="fas fa-circle-check"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-2" role="alert"
                 style="border-radius:10px;font-size:.85rem;">
                <i class="fas fa-triangle-exclamation"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            @endif
        </div>

        {{-- Page content --}}
        <div class="ob-main">
            @yield('content')
        </div>

        {{-- Footer --}}
        <div class="text-center py-3 border-top" style="font-size:.75rem;color:#94a3b8;background:#fff;">
            &copy; {{ date('Y') }} HRS Bhutan &mdash; Owner Portal
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('ownerSidebar');
    const toggle  = document.getElementById('sidebarToggle');
    if (window.innerWidth <= 768 && sidebar.classList.contains('open') &&
        !sidebar.contains(e.target) && toggle && !toggle.contains(e.target)) {
        sidebar.classList.remove('open');
    }
});
</script>

@stack('scripts')
</body>
</html>
