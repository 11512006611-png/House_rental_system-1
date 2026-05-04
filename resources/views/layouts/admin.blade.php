<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') | HRS Bhutan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg:    #0f172a;
            --sidebar-width: 260px;
            --accent:        #3b82f6;
            --accent-dark:   #2563eb;
            --sidebar-hover: rgba(255,255,255,.06);
            --sidebar-active:rgba(59,130,246,.18);
        }
        *{box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:#f1f5f9;color:#1e293b;}

        /* â”€â”€ Sidebar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .admin-sidebar{width:var(--sidebar-width);height:100vh;background:var(--sidebar-bg);
            position:fixed;top:0;left:0;z-index:1030;display:flex;flex-direction:column;
            overflow:hidden;transition:transform .3s ease;scrollbar-width:thin;
            scrollbar-color:rgba(255,255,255,.1) transparent;}
        .sidebar-brand{padding:1.2rem 1.4rem .9rem;border-bottom:1px solid rgba(255,255,255,.06);flex-shrink:0;}
        .sidebar-nav{padding:.5rem 0;flex:1;overflow-y:auto;min-height:0;}
        .sidebar-section{font-size:.6rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;
            color:rgba(255,255,255,.25);padding:.9rem 1.4rem .25rem;}
        .admin-sidebar .nav-link{display:flex;align-items:center;gap:.7rem;padding:.58rem 1.4rem;
            color:rgba(255,255,255,.58);font-size:.84rem;font-weight:500;
            border-left:3px solid transparent;text-decoration:none;
            transition:background .14s,color .14s,border-color .14s;}
        .admin-sidebar .nav-link:hover{color:#fff;background:var(--sidebar-hover);}
        .admin-sidebar .nav-link.active{color:#fff;background:var(--sidebar-active);border-left-color:var(--accent);}
        .nav-icon{width:18px;text-align:center;flex-shrink:0;font-size:.88rem;}
        .nav-badge{margin-left:auto;}
        .sidebar-footer{padding:.9rem 1.4rem;border-top:1px solid rgba(255,255,255,.06);flex-shrink:0;
            background:var(--sidebar-bg);}
        .sidebar-logout-btn{margin-top:.7rem;display:flex;align-items:center;justify-content:center;gap:.5rem;
            width:100%;padding:.52rem .7rem;border:1px solid rgba(248,113,113,.45);border-radius:9px;
            background:rgba(239,68,68,.12);color:#fecaca;font-size:.8rem;font-weight:600;}
        .sidebar-logout-btn:hover{background:rgba(239,68,68,.2);color:#fff;}

        /* â”€â”€ Content â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .admin-content{margin-left:var(--sidebar-width);min-height:100vh;display:flex;flex-direction:column;}
        .admin-topbar{position:sticky;top:0;z-index:100;background:#fff;
            border-bottom:1px solid #e2e8f0;padding:.65rem 1.6rem;
            display:flex;align-items:center;justify-content:space-between;
            box-shadow:0 1px 4px rgba(0,0,0,.05);}
        .admin-main{padding:1.6rem 1.6rem 3rem;flex:1;}

        /* â”€â”€ Stat cards â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .stat-card{border:none;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.08);
            transition:transform .2s,box-shadow .2s;overflow:hidden;}
        .stat-card:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(0,0,0,.1);}
        .stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;
            justify-content:center;font-size:1.15rem;}
        .stat-value{font-size:1.55rem;font-weight:800;line-height:1.1;}
        .stat-label{font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.07em;color:#64748b;margin-top:.18rem;}
        .stat-footer{font-size:.73rem;color:#94a3b8;margin-top:.45rem;}

        /* â”€â”€ Admin cards / tables â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .admin-card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);overflow:hidden;}
        .admin-card-header{padding:.9rem 1.2rem .7rem;border-bottom:1px solid #f1f5f9;
            display:flex;align-items:center;justify-content:space-between;}
        .admin-card-header h6{margin:0;font-weight:700;font-size:.88rem;color:#0f172a;}
        .admin-table{margin:0;}
        .admin-table thead th{background:#f8fafc;border-bottom:2px solid #e2e8f0;
            font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;
            color:#64748b;padding:.65rem 1rem;white-space:nowrap;}
        .admin-table tbody td{padding:.75rem 1rem;vertical-align:middle;
            border-bottom:1px solid #f1f5f9;font-size:.845rem;}
        .admin-table tbody tr:last-child td{border-bottom:none;}
        .admin-table tbody tr:hover td{background:#fafbfd;}

        /* â”€â”€ Utility â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .u-avatar{width:34px;height:34px;border-radius:50%;display:inline-flex;align-items:center;
            justify-content:center;font-weight:700;font-size:.78rem;flex-shrink:0;}
        .page-header{margin-bottom:1.4rem;}
        .page-header h1{font-size:1.4rem;font-weight:800;color:#0f172a;margin-bottom:.18rem;}
        .page-header p{font-size:.84rem;color:#64748b;margin:0;}

        /* â”€â”€ Quick Actions â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .qa-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:.75rem;}
        .qa-btn{display:flex;flex-direction:column;align-items:center;justify-content:center;
            gap:.4rem;padding:.9rem .5rem;border-radius:12px;text-decoration:none;
            border:2px solid transparent;transition:all .18s;min-height:80px;text-align:center;}
        .qa-btn i{font-size:1.25rem;}
        .qa-btn span{font-size:.73rem;font-weight:600;}
        .qa-btn:hover{transform:translateY(-2px);box-shadow:0 4px 14px rgba(0,0,0,.1);}

        /* â”€â”€ Notification dropdown â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .notif-dropdown{min-width:360px;max-height:480px;overflow-y:auto;padding:0;}
        .notif-header{padding:.65rem 1rem;border-bottom:1px solid #f1f5f9;
            font-size:.8rem;font-weight:700;color:#0f172a;}
        .notif-item{display:flex;align-items:flex-start;gap:.65rem;padding:.65rem 1rem;
            border-bottom:1px solid #f8fafc;text-decoration:none;color:inherit;
            transition:background .12s;}
        .notif-item:hover{background:#f8fafc;}
        .notif-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;
            justify-content:center;font-size:.8rem;flex-shrink:0;}
        .notif-text{font-size:.79rem;color:#374151;line-height:1.35;}
        .notif-time{font-size:.68rem;color:#9ca3af;margin-top:.18rem;}
        .notif-footer{padding:.55rem 1rem;text-align:center;border-top:1px solid #f1f5f9;}

        /* â”€â”€ Badge chip â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .chip{display:inline-flex;align-items:center;padding:.2rem .55rem;border-radius:6px;
            font-size:.68rem;font-weight:600;white-space:nowrap;}
        .chip-green {background:#f0fdf4;color:#16a34a;}
        .chip-blue  {background:#eff6ff;color:#2563eb;}
        .chip-orange{background:#fff7ed;color:#ea580c;}
        .chip-red   {background:#fef2f2;color:#dc2626;}
        .chip-yellow{background:#fef9c3;color:#a16207;}
        .chip-gray  {background:#f1f5f9;color:#64748b;}
        .chip-purple{background:#faf5ff;color:#9333ea;}

        /* â”€â”€ Responsive â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        @media(max-width:768px){
            .admin-sidebar{transform:translateX(-100%);}
            .admin-sidebar.open{transform:translateX(0);}
            .admin-content{margin-left:0;}
        }
    </style>

    @stack('styles')
</head>
<body>

@php
    $__pendingU = \App\Models\User::where('status','pending')->count();
    $__pendingP = \App\Models\House::where('status','pending')->count();
    $__pendingR = \App\Models\Rental::where('status','pending')->count();
    $__pendingI = \App\Models\Inspection::where('status','pending')->count();
    $__pendingA = \App\Models\Rental::where('lease_status','pending')->count();
    $__pendingPay = \App\Models\Payment::where('verification_status','pending')->count();
    $__pendingC = \App\Models\MaintenanceRequest::where('status','pending')->count();
    $__pendingMO = \App\Models\MoveOutRequest::whereIn('status',['requested','approved'])->count();
    $__notifTotal = $__pendingU + $__pendingP + $__pendingR + $__pendingI + $__pendingA + $__pendingPay + $__pendingC + $__pendingMO;

    // For dropdown: last 4 of each
    $__notifUsers = \App\Models\User::where('status','pending')->orderByDesc('created_at')->limit(4)->get();
    $__notifProps = \App\Models\House::where('status','pending')->with('owner')->orderByDesc('created_at')->limit(4)->get();
    $__notifRents = \App\Models\Rental::where('status','pending')->with(['house','tenant'])->orderByDesc('created_at')->limit(3)->get();
    $__notifInspections = \App\Models\Inspection::where('status','pending')->with(['house','tenant'])->orderByDesc('created_at')->limit(2)->get();
    $__notifAgreements = \App\Models\Rental::where('lease_status','pending')->with(['house','tenant'])->orderByDesc('created_at')->limit(2)->get();
    $__notifPayments = \App\Models\Payment::where('verification_status','pending')->with(['rental.house','tenant'])->orderByDesc('created_at')->limit(2)->get();
    $__notifComplaints = \App\Models\MaintenanceRequest::where('status','pending')->with(['rental.house','rental.tenant'])->orderByDesc('created_at')->limit(2)->get();
    $__notifMoveOuts = \App\Models\MoveOutRequest::whereIn('status',['requested','approved'])->with(['rental.house','rental.tenant'])->orderByDesc('created_at')->limit(2)->get();
@endphp

<div class="d-flex" id="adminWrapper">

{{-- â•â•â•â• SIDEBAR â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<nav class="admin-sidebar" id="adminSidebar">

    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
            <div style="width:38px;height:38px;background:var(--accent);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-home text-white" style="font-size:.92rem;"></i>
            </div>
            <div>
                <div class="text-white fw-bold" style="font-size:.9rem;line-height:1.2;">HRS Bhutan</div>
                <div style="font-size:.58rem;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.12em;">Admin Panel</div>
            </div>
        </a>
    </div>

    <div class="sidebar-nav">

        <div class="sidebar-section">Overview</div>
        <a href="{{ route('admin.dashboard') }}"   class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-gauge-high"></i></span> 🏠 Dashboard
        </a>

        <div class="sidebar-section mt-1">Users</div>
        <a href="{{ route('admin.users') }}"   class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-users-gear"></i></span> 👤 Users
        </a>
        <a href="{{ route('admin.pending') }}" class="nav-link {{ request()->routeIs('admin.pending') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-user-clock"></i></span> Pending Approvals
            @if($__pendingU > 0)
                <span class="nav-badge badge rounded-pill bg-danger" style="font-size:.6rem;">{{ $__pendingU }}</span>
            @endif
        </a>
        <a href="{{ route('admin.owners') }}"  class="nav-link {{ request()->routeIs('admin.owners') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-user-tie"></i></span> Owners
        </a>
        <a href="{{ route('admin.tenants') }}" class="nav-link {{ request()->routeIs('admin.tenants') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-person-shelter"></i></span> Tenants
        </a>

        <div class="sidebar-section mt-1">Properties</div>
        <a href="{{ route('admin.properties') }}" class="nav-link {{ request()->routeIs('admin.properties') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-building"></i></span> 🏢 Properties
            @if($__pendingP > 0)
                <span class="nav-badge badge rounded-pill bg-warning text-dark" style="font-size:.6rem;">{{ $__pendingP }}</span>
            @endif
        </a>
        <a href="{{ route('admin.inspections') }}" class="nav-link {{ request()->routeIs('admin.inspections') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-search"></i></span> 🔍 Inspections
            @if($__pendingI > 0)
                <span class="nav-badge badge rounded-pill bg-info" style="font-size:.6rem;">{{ $__pendingI }}</span>
            @endif
        </a>

        <div class="sidebar-section mt-1">Rentals</div>
        <a href="{{ route('admin.rentals') }}"      class="nav-link {{ request()->routeIs('admin.rentals') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-file-contract"></i></span> 📑 Booking Requests
            @if($__pendingR > 0)
                <span class="nav-badge badge rounded-pill bg-success" style="font-size:.6rem;">{{ $__pendingR }}</span>
            @endif
        </a>
        <a href="{{ route('admin.rentals', ['lease_queue' => 1]) }}" class="nav-link {{ request()->routeIs('admin.rentals') && request()->boolean('lease_queue') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-file-signature"></i></span> 📄 Agreements
            @if($__pendingA > 0)
                <span class="nav-badge badge rounded-pill bg-danger" style="font-size:.6rem;">{{ $__pendingA }}</span>
            @endif
        </a>
        <a href="#" class="nav-link">
            <span class="nav-icon"><i class="fas fa-door-open"></i></span> 🚪 Move-Out
            @if($__pendingMO > 0)
                <span class="nav-badge badge rounded-pill bg-warning text-dark" style="font-size:.6rem;">{{ $__pendingMO }}</span>
            @endif
        </a>

        <div class="sidebar-section mt-1">Finance</div>
        <a href="{{ route('admin.transactions') }}" class="nav-link {{ request()->routeIs('admin.transactions') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-money-bill-wave"></i></span> 💰 Payments
            @if($__pendingPay > 0)
                <span class="nav-badge badge rounded-pill bg-primary" style="font-size:.6rem;">{{ $__pendingPay }}</span>
            @endif
        </a>
        <a href="{{ route('admin.settlements.index') }}" class="nav-link {{ request()->routeIs('admin.settlements.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-calculator"></i></span> 💵 Settlements
        </a>

        <div class="sidebar-section mt-1">Support</div>
        <a href="{{ route('admin.maintenance') }}" class="nav-link {{ request()->routeIs('admin.maintenance') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-exclamation-triangle"></i></span> ⚠ Complaints
            @if($__pendingC > 0)
                <span class="nav-badge badge rounded-pill bg-danger" style="font-size:.6rem;">{{ $__pendingC }}</span>
            @endif
        </a>
        <a href="{{ route('admin.reports') }}" class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-chart-bar"></i></span> 📊 Reports
        </a>

        <div class="sidebar-section mt-1">System</div>
        <a href="{{ route('profile.show') }}" class="nav-link {{ request()->routeIs('profile.show') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-id-card"></i></span> My Profile
        </a>
        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-user-pen"></i></span> Edit Profile
        </a>
        <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-gear"></i></span> Settings
        </a>
        <a href="{{ route('home') }}" class="nav-link">
            <span class="nav-icon"><i class="fas fa-arrow-left-long"></i></span> Back to Website
        </a>

    </div>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2">
            @if(Auth::user()->profile_image_url)
                <img src="{{ Auth::user()->profile_image_url }}" alt="Admin avatar" class="u-avatar" style="width:30px;height:30px;font-size:.72rem;object-fit:cover;">
            @else
                <div class="u-avatar" style="background:var(--accent);color:#fff;width:30px;height:30px;font-size:.72rem;">
                    {{ strtoupper(substr(Auth::user()->username ?: Auth::user()->name, 0, 1)) }}
                </div>
            @endif
            <div style="overflow:hidden;">
                <div class="text-white text-truncate" style="font-size:.78rem;font-weight:600;max-width:170px;">{{ Auth::user()->name }}</div>
                <div style="font-size:.62rem;color:rgba(255,255,255,.35);">Administrator</div>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="sidebar-logout-btn border-0" style="cursor:pointer;">
                <i class="fas fa-right-from-bracket"></i> Logout
            </button>
        </form>
    </div>
</nav>

{{-- â•â•â•â• MAIN CONTENT â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="admin-content flex-grow-1">

    {{-- Top Bar --}}
    <header class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-light border d-md-none" id="sidebarToggle" type="button">
                <i class="fas fa-bars"></i>
            </button>
            <nav aria-label="breadcrumb" class="d-none d-md-flex">
                <ol class="breadcrumb mb-0" style="font-size:.78rem;">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-primary">Admin</a>
                    </li>
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>

        <div class="d-flex align-items-center gap-2">

            {{-- Notification Bell --}}
            <div class="dropdown">
                <button class="btn btn-sm btn-light border position-relative" data-bs-toggle="dropdown" aria-expanded="false" style="width:36px;height:36px;padding:0;">
                    <i class="fas fa-bell" style="font-size:.85rem;"></i>
                    @if($__notifTotal > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.55rem;min-width:16px;padding:.2rem .35rem;">
                            {{ $__notifTotal > 99 ? '99+' : $__notifTotal }}
                        </span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 notif-dropdown" style="border-radius:14px;margin-top:.4rem;">
                    <div class="notif-header d-flex align-items-center justify-content-between">
                        <span>Notifications</span>
                        @if($__notifTotal > 0)
                            <span class="badge bg-danger rounded-pill">{{ $__notifTotal }}</span>
                        @endif
                    </div>

                    @if($__notifTotal === 0)
                        <div class="text-center py-4 text-muted small">
                            <i class="fas fa-check-circle fa-2x mb-2 text-success opacity-50 d-block"></i>
                            No pending notifications
                        </div>
                    @endif

                    {{-- New Users --}}
                    @foreach($__notifUsers as $nu)
                    <a href="{{ route('admin.pending') }}" class="notif-item">
                        <div class="notif-icon" style="background:#fef9c3;"><i class="fas fa-user-plus" style="color:#a16207;"></i></div>
                        <div>
                            <div class="notif-text"><strong>{{ $nu->name }}</strong> registered as {{ ucfirst($nu->role) }}</div>
                            <div class="notif-time">{{ \Carbon\Carbon::parse($nu->created_at)->diffForHumans() }}</div>
                        </div>
                    </a>
                    @endforeach

                    {{-- New Properties --}}
                    @foreach($__notifProps as $np)
                    <a href="{{ route('admin.properties') }}?status=pending" class="notif-item">
                        <div class="notif-icon" style="background:#eff6ff;"><i class="fas fa-house-circle-check" style="color:#2563eb;"></i></div>
                        <div>
                            <div class="notif-text"><strong>{{ Str::limit($np->title, 28) }}</strong> by {{ $np->owner->name ?? 'â€”' }}</div>
                            <div class="notif-time">Property pending approval Â· {{ \Carbon\Carbon::parse($np->created_at)->diffForHumans() }}</div>
                        </div>
                    </a>
                    @endforeach

                    {{-- Pending Rentals --}}
                    @foreach($__notifRents as $nr)
                    <a href="{{ route('admin.rentals') }}?status=pending" class="notif-item">
                        <div class="notif-icon" style="background:#f0fdf4;"><i class="fas fa-file-contract" style="color:#16a34a;"></i></div>
                        <div>
                            <div class="notif-text"><strong>{{ $nr->tenant->name ?? 'â€”' }}</strong> requested <strong>{{ Str::limit($nr->house->title ?? 'â€”', 22) }}</strong></div>
                            <div class="notif-time">Rental pending Â· {{ \Carbon\Carbon::parse($nr->created_at)->diffForHumans() }}</div>
                        </div>
                    </a>
                    @endforeach
                    {{-- Pending Inspections --}}
                    @foreach($__notifInspections as $ni)
                    <a href="{{ route('admin.inspections') }}" class="notif-item">
                        <div class="notif-icon" style="background:#faf5ff;"><i class="fas fa-search" style="color:#9333ea;"></i></div>
                        <div>
                            <div class="notif-text">Inspection requested for <strong>{{ Str::limit($ni->house->title ?? '—', 25) }}</strong></div>
                            <div class="notif-time">Inspection pending · {{ \Carbon\Carbon::parse($ni->created_at)->diffForHumans() }}</div>
                        </div>
                    </a>
                    @endforeach

                    {{-- Pending Agreements --}}
                    @foreach($__notifAgreements as $na)
                    <a href="{{ route('admin.rentals', ['lease_queue' => 1]) }}" class="notif-item">
                        <div class="notif-icon" style="background:#fef2f2;"><i class="fas fa-file-signature" style="color:#dc2626;"></i></div>
                        <div>
                            <div class="notif-text">Lease agreement pending for <strong>{{ $na->tenant->name ?? '—' }}</strong></div>
                            <div class="notif-time">Agreement pending · {{ \Carbon\Carbon::parse($na->created_at)->diffForHumans() }}</div>
                        </div>
                    </a>
                    @endforeach

                    {{-- Pending Payments --}}
                    @foreach($__notifPayments as $np)
                    <a href="{{ route('admin.transactions') }}" class="notif-item">
                        <div class="notif-icon" style="background:#f0fdf4;"><i class="fas fa-money-bill-wave" style="color:#16a34a;"></i></div>
                        <div>
                            <div class="notif-text">Payment verification needed for <strong>{{ $np->tenant->name ?? '—' }}</strong></div>
                            <div class="notif-time">Payment pending · {{ \Carbon\Carbon::parse($np->created_at)->diffForHumans() }}</div>
                        </div>
                    </a>
                    @endforeach

                    {{-- Pending Complaints --}}
                    @foreach($__notifComplaints as $nc)
                    <a href="{{ route('admin.maintenance') }}" class="notif-item">
                        <div class="notif-icon" style="background:#fef2f2;"><i class="fas fa-exclamation-triangle" style="color:#dc2626;"></i></div>
                        <div>
                            <div class="notif-text">Maintenance request from <strong>{{ $nc->rental->tenant->name ?? '—' }}</strong></div>
                            <div class="notif-time">Complaint pending · {{ \Carbon\Carbon::parse($nc->created_at)->diffForHumans() }}</div>
                        </div>
                    </a>
                    @endforeach

                    {{-- Pending Move-Outs --}}
                    @foreach($__notifMoveOuts as $nmo)
                    <a href="#" class="notif-item">
                        <div class="notif-icon" style="background:#fff7ed;"><i class="fas fa-door-open" style="color:#ea580c;"></i></div>
                        <div>
                            <div class="notif-text">Move-out request from <strong>{{ $nmo->rental->tenant->name ?? '—' }}</strong></div>
                            <div class="notif-time">Move-out pending · {{ \Carbon\Carbon::parse($nmo->created_at)->diffForHumans() }}</div>
                        </div>
                    </a>
                    @endforeach
                    <div class="notif-footer">
                        <a href="{{ route('admin.dashboard') }}" class="text-primary text-decoration-none" style="font-size:.78rem;font-weight:600;">
                            View Dashboard <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 ms-1">
                @if(Auth::user()->profile_image_url)
                    <img src="{{ Auth::user()->profile_image_url }}" alt="Admin avatar" class="u-avatar" style="font-size:.78rem;object-fit:cover;">
                @else
                    <div class="u-avatar" style="background:var(--accent);color:#fff;font-size:.78rem;">
                        {{ strtoupper(substr(Auth::user()->username ?: Auth::user()->name, 0, 1)) }}
                    </div>
                @endif
                <div class="d-none d-sm-block">
                    <div style="font-size:.83rem;font-weight:600;color:#0f172a;line-height:1.2;">{{ Auth::user()->name }}</div>
                    <div style="font-size:.68rem;color:#94a3b8;">Administrator</div>
                </div>
            </div>
        </div>
    </header>

    {{-- Flash Messages --}}
    @foreach(['success' => 'success', 'error' => 'danger'] as $key => $type)
    @if(session($key))
    <div class="alert alert-{{ $type }} alert-dismissible fade show rounded-0 border-0 border-start border-{{ $type }} border-4 m-0">
        <div class="d-flex align-items-center gap-2 ps-2">
            <i class="fas {{ $type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' }} text-{{ $type }}"></i>
            <span style="font-size:.875rem;">{{ session($key) }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @endforeach

    <main class="admin-main">
        @yield('content')
    </main>

</div>{{-- /.admin-content --}}
</div>{{-- /#adminWrapper --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('adminSidebar').classList.toggle('open');
    });
    // Close sidebar on outside click (mobile)
    document.addEventListener('click', e => {
        const sb  = document.getElementById('adminSidebar');
        const btn = document.getElementById('sidebarToggle');
        if (sb?.classList.contains('open') && !sb.contains(e.target) && !btn?.contains(e.target)) {
            sb.classList.remove('open');
        }
    });
</script>
@stack('scripts')
</body>
</html>
