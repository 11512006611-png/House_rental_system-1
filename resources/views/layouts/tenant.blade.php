<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tenant Dashboard') | HRS Bhutan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --td-sidebar-bg: #0f172a;
            --td-accent: #3b82f6;
            --td-accent-light: #dbeafe;
            --td-sidebar-w: 255px;
            --td-topbar-h: 60px;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; color: #1e293b; margin: 0; }

        .td-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--td-sidebar-w);
            background: var(--td-sidebar-bg);
            display: flex; flex-direction: column;
            z-index: 1040; overflow-y: auto;
            transition: transform .28s ease;
        }
        .td-brand {
            padding: 1.1rem 1.2rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
            flex-shrink: 0;
        }
        .td-nav-section {
            padding: .7rem 1rem .25rem;
            font-size: .62rem;
            font-weight: 700;
            letter-spacing: .09em;
            color: rgba(255,255,255,.35);
            text-transform: uppercase;
        }
        .td-nav-link {
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
        .td-nav-link:hover { background: rgba(255,255,255,.09); color: #fff; }
        .td-nav-link.active { background: var(--td-accent); color: #fff; font-weight: 600; }
        .td-nav-link .icon { width: 20px; text-align: center; font-size: .9rem; flex-shrink: 0; }
        .td-nav-link .badge {
            margin-left: auto; font-size: .6rem; padding: .2rem .45rem;
            background: rgba(255,255,255,.15); color: #fff; border-radius: 20px;
        }
        .td-sidebar-footer {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,.08);
            padding: .9rem 1rem;
        }

        .td-content {
            margin-left: var(--td-sidebar-w);
            min-height: 100vh;
            display: flex; flex-direction: column;
        }
        .td-topbar {
            position: sticky; top: 0; z-index: 1030;
            height: var(--td-topbar-h);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center;
            padding: 0 1.5rem; gap: 1rem;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        .td-topbar .breadcrumb { margin: 0; font-size: .8rem; }
        .td-topbar .breadcrumb-item a { color: var(--td-accent); text-decoration: none; }
        .td-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--td-accent-light); color: var(--td-accent);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .82rem; flex-shrink: 0;
        }
        .td-main {
            flex: 1;
            padding: 1.5rem;
        }

        @media (max-width: 768px) {
            .td-sidebar { transform: translateX(-100%); }
            .td-sidebar.open { transform: translateX(0); }
            .td-content { margin-left: 0; }
        }
    </style>

    @stack('styles')
</head>
<body>

@php
    $__tenantId = auth()->id();
    $__pendingRequests = \App\Models\Rental::where('tenant_id', $__tenantId)->where('status', 'pending')->count();
    $__pendingMaintenance = \App\Models\MaintenanceRequest::where('tenant_id', $__tenantId)->where('status', 'pending')->count();
    $__workflowUnreadCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
    $__workflowUnreadItems = auth()->check() ? auth()->user()->unreadNotifications()->latest()->take(5)->get() : collect();
@endphp

<div class="d-flex" id="tenantWrapper">
    <nav class="td-sidebar" id="tenantSidebar">
        <div class="td-brand">
            <a href="{{ route('tenant.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <div style="width:38px;height:38px;background:var(--td-accent);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-house text-white" style="font-size:.95rem;"></i>
                </div>
                <div>
                    <div class="text-white fw-bold" style="font-size:.92rem;line-height:1.2;">HRS Bhutan</div>
                    <div style="font-size:.58rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.12em;">Tenant Panel</div>
                </div>
            </a>
        </div>

        <div class="pt-2 pb-3 flex-fill">
            <div class="td-nav-section">Overview</div>
            <a href="{{ route('tenant.dashboard') }}" class="td-nav-link {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-gauge-high"></i></span>
                Dashboard
            </a>

            <div class="td-nav-section">Rentals</div>
            <a href="{{ route('rentals.my-rentals') }}" class="td-nav-link {{ request()->routeIs('rentals.my-rentals') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-file-contract"></i></span>
                My Rentals
                @if($__pendingRequests > 0)
                <span class="badge">{{ $__pendingRequests }}</span>
                @endif
            </a>
            <a href="{{ route('tenant.maintenance.index') }}" class="td-nav-link {{ request()->routeIs('tenant.maintenance.*') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-screwdriver-wrench"></i></span>
                Maintenance
                @if($__pendingMaintenance > 0)
                <span class="badge" style="background:#f59e0b;">{{ $__pendingMaintenance }}</span>
                @endif
            </a>
            <a href="{{ route('tenant.payments') }}" class="td-nav-link {{ request()->routeIs('tenant.payments') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-credit-card"></i></span>
                Payment History
            </a>
            <a href="{{ route('tenant.dashboard', ['focus' => 'monthly-payment']) }}#monthly-payment-to-admin"
               class="td-nav-link {{ request()->routeIs('tenant.dashboard') && request('focus') === 'monthly-payment' ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-calendar-check"></i></span>
                Make Payment
            </a>
            <a href="{{ route('tenant.dashboard', ['move_out' => 1]) }}" class="td-nav-link {{ request()->routeIs('tenant.dashboard') && request('move_out') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-door-open"></i></span>
                Request Move-Out
            </a>

            <div class="td-nav-section">Account</div>
            <a href="{{ route('profile.show') }}" class="td-nav-link {{ request()->routeIs('profile.show') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-id-card"></i></span>
                My Profile
            </a>
            <a href="{{ route('profile.edit') }}" class="td-nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-user-pen"></i></span>
                Edit Profile
            </a>
            <a href="{{ route('home') }}" class="td-nav-link">
                <span class="icon"><i class="fas fa-house-chimney"></i></span>
                Back to Site
            </a>
        </div>

        <div class="td-sidebar-footer">
            <div class="d-flex align-items-center gap-2 mb-3">
                @if(Auth::user()->profile_image_url)
                    <img src="{{ Auth::user()->profile_image_url }}" alt="User avatar" class="td-avatar" style="object-fit:cover;">
                @else
                    <div class="td-avatar">{{ strtoupper(substr(Auth::user()->username ?: Auth::user()->name, 0, 1)) }}</div>
                @endif
                <div style="overflow:hidden;">
                    <div class="text-white fw-semibold text-truncate" style="font-size:.8rem;">{{ Auth::user()->name }}</div>
                    <div style="font-size:.67rem;color:rgba(255,255,255,.45);">Tenant</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn w-100 d-flex align-items-center justify-content-center gap-2" style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.75);font-size:.8rem;border-radius:8px;border:none;padding:.5rem;">
                    <i class="fas fa-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="td-content" id="tenantContent">
        <div class="td-topbar">
            <button class="btn btn-sm btn-light d-md-none me-1" id="sidebarToggle" style="border:none;" onclick="document.getElementById('tenantSidebar').classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>

            <nav aria-label="breadcrumb" class="flex-fill">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Tenant</a></li>
                    @yield('breadcrumb')
                </ol>
            </nav>

            <div class="dropdown">
                <button class="btn btn-sm position-relative" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:.38rem .6rem;" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell" style="color:#475569;font-size:.95rem;"></i>
                    @if($__workflowUnreadCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.55rem;">{{ $__workflowUnreadCount }}</span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:290px;border-radius:12px;border:1px solid #e2e8f0;">
                    <li class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between gap-2">
                        <span class="fw-700" style="font-size:.8rem;color:#0f172a;">Notifications</span>
                    </li>
                    @if($__workflowUnreadItems->isNotEmpty())
                        @foreach($__workflowUnreadItems as $__note)
                        <li>
                            <a href="{{ route('tenant.dashboard') }}" class="dropdown-item py-2" style="white-space:normal;line-height:1.3;">
                                <div class="fw-semibold" style="font-size:.76rem;color:#0f172a;">{{ $__note->data['title'] ?? 'Update' }}</div>
                                <div style="font-size:.72rem;color:#475569;">{{ \Illuminate\Support\Str::limit($__note->data['message'] ?? 'You have a new update.', 95) }}</div>
                                <div style="font-size:.66rem;color:#94a3b8;">{{ $__note->created_at?->diffForHumans() }}</div>
                            </a>
                        </li>
                        @endforeach
                    @else
                    <li class="px-3 py-3 text-center text-muted" style="font-size:.8rem;">
                        <i class="fas fa-circle-check text-success me-1"></i> All caught up!
                    </li>
                    @endif
                    @if($__workflowUnreadCount > 0)
                    <li><hr class="dropdown-divider my-1"></li>
                    <li class="px-3 py-2">
                        <form action="{{ route('tenant.notifications.clear') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary w-100" style="font-size:.72rem;">
                                <i class="fas fa-broom me-1"></i>Clear All
                            </button>
                        </form>
                    </li>
                    @endif
                </ul>
            </div>

            <div class="d-flex align-items-center gap-2 ms-1">
                @if(Auth::user()->profile_image_url)
                    <img src="{{ Auth::user()->profile_image_url }}" alt="User avatar" class="td-avatar" style="object-fit:cover;">
                @else
                    <div class="td-avatar">{{ strtoupper(substr(Auth::user()->username ?: Auth::user()->name, 0, 1)) }}</div>
                @endif
                <div class="d-none d-md-block">
                    <div class="fw-600" style="font-size:.82rem;color:#0f172a;line-height:1.2;">{{ Auth::user()->name }}</div>
                    <div style="font-size:.68rem;color:#94a3b8;">Tenant</div>
                </div>
            </div>
        </div>

        <div class="px-3 pt-3">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-2" role="alert" style="border-radius:10px;font-size:.85rem;">
                <i class="fas fa-circle-check"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-2" role="alert" style="border-radius:10px;font-size:.85rem;">
                <i class="fas fa-triangle-exclamation"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            @endif
        </div>

        <div class="td-main">
            @yield('content')
        </div>

        <div class="text-center py-3 border-top" style="font-size:.75rem;color:#94a3b8;background:#fff;">
            &copy; {{ date('Y') }} HRS Bhutan - Tenant Portal
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('tenantSidebar');
    const toggle = document.getElementById('sidebarToggle');
    if (window.innerWidth <= 768 && sidebar.classList.contains('open') && !sidebar.contains(e.target) && toggle && !toggle.contains(e.target)) {
        sidebar.classList.remove('open');
    }
});
</script>
@stack('scripts')
</body>
</html>
