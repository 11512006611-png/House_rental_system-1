@extends('layouts.admin')
@section('title','Dashboard')
@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('styles')
<style>
.quick-actions .qa-btn.qa-green  { background:#f0fdf4; color:#16a34a; border-color:#bbf7d0; }
.quick-actions .qa-btn.qa-blue   { background:#eff6ff; color:#2563eb; border-color:#bfdbfe; }
.quick-actions .qa-btn.qa-orange { background:#fff7ed; color:#ea580c; border-color:#fed7aa; }
.quick-actions .qa-btn.qa-purple { background:#faf5ff; color:#9333ea; border-color:#e9d5ff; }
.quick-actions .qa-btn.qa-teal   { background:#f0fdfa; color:#0d9488; border-color:#99f6e4; }
.quick-actions .qa-btn.qa-red    { background:#fef2f2; color:#dc2626; border-color:#fecaca; }
</style>
@endpush

@section('content')

{{-- â”€â”€ Page Header â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
    <div class="page-header mb-0">
        <h1><i class="fas fa-gauge-high me-2 text-primary"></i>Dashboard</h1>
        <p>Welcome back, {{ Auth::user()->name }}. &nbsp;
            <span class="text-muted small">{{ now()->format('l, d F Y') }}</span>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($pendingUsers + $pendingProperties + $pendingRentals > 0)
        <span class="badge bg-danger align-self-center fs-6 px-3 py-2">
            <i class="fas fa-bell me-1"></i>
            {{ $pendingUsers + $pendingProperties + $pendingRentals }} Actions Needed
        </span>
        @endif
        <a href="{{ route('admin.settings') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-gear me-1"></i>Settings
        </a>
    </div>
</div>

{{-- â”€â”€ Quick Actions â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
<div class="admin-card mb-4">
    <div class="admin-card-header">
        <h6><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h6>
    </div>
    <div class="p-3 quick-actions">
        <div class="qa-grid">
            <a href="{{ route('admin.pending') }}" class="qa-btn qa-red">
                <i class="fas fa-user-clock"></i>
                <span>Approve Users<br>
                    @if($pendingUsers > 0)<span class="badge bg-danger">{{ $pendingUsers }}</span>@endif
                </span>
            </a>
            <a href="{{ route('admin.properties') }}?status=pending" class="qa-btn qa-blue">
                <i class="fas fa-house-circle-check"></i>
                <span>Review Properties<br>
                    @if($pendingProperties > 0)<span class="badge bg-warning text-dark">{{ $pendingProperties }}</span>@endif
                </span>
            </a>
            <a href="{{ route('admin.owners') }}" class="qa-btn qa-green">
                <i class="fas fa-user-tie"></i>
                <span>Manage Owners</span>
            </a>
            <a href="{{ route('admin.tenants') }}" class="qa-btn qa-orange">
                <i class="fas fa-users"></i>
                <span>Manage Tenants</span>
            </a>
            <a href="{{ route('admin.transactions') }}" class="qa-btn qa-purple">
                <i class="fas fa-money-bill-transfer"></i>
                <span>Transactions</span>
            </a>
            <a href="{{ route('admin.settings') }}" class="qa-btn qa-teal">
                <i class="fas fa-percent"></i>
                <span>Commission<br><small class="fw-bold">{{ $rate }}%</small></span>
            </a>
            <a href="{{ route('admin.rentals') }}" class="qa-btn qa-orange">
                <i class="fas fa-truck-moving"></i>
                <span>Move-Out Requests<br>
                    @if(($openMoveOutRequests ?? 0) > 0)<span class="badge bg-warning text-dark">{{ $openMoveOutRequests }}</span>@endif
                </span>
            </a>
        </div>
    </div>
</div>

{{-- â”€â”€ Summary Cards â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
<div class="row g-3 mb-4">

    {{-- Commission --}}
    <div class="col-xl col-md-4 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="stat-icon" style="background:#faf5ff;"><i class="fas fa-percent" style="color:#9333ea;"></i></div>
                    <span class="chip chip-purple">Commission</span>
                </div>
                <div class="stat-value" style="color:#9333ea;">Nu. {{ number_format($totalCommission, 0) }}</div>
                <div class="stat-label">Platform Commission</div>
                <div class="stat-footer"><i class="fas fa-info-circle me-1"></i>{{ $rate }}% of all payments</div>
            </div>
        </div>
    </div>

    {{-- Revenue --}}
    <div class="col-xl col-md-4 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="stat-icon" style="background:#eff6ff;"><i class="fas fa-sack-dollar" style="color:#3b82f6;"></i></div>
                    <span class="chip chip-blue">Revenue</span>
                </div>
                <div class="stat-value" style="color:#0f172a;">Nu. {{ number_format($totalRevenue, 0) }}</div>
                <div class="stat-label">Total Rental Revenue</div>
                <div class="stat-footer"><i class="fas fa-circle-check text-success me-1"></i>All paid payments</div>
            </div>
        </div>
    </div>

    {{-- Owners --}}
    <div class="col-xl col-md-4 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="stat-icon" style="background:#f0fdf4;"><i class="fas fa-user-tie" style="color:#16a34a;"></i></div>
                    <span class="chip chip-green">Owner</span>
                </div>
                <div class="stat-value">{{ $totalOwners }}</div>
                <div class="stat-label">Total Owners</div>
                <div class="stat-footer"><i class="fas fa-circle-check text-success me-1"></i>Approved accounts</div>
            </div>
        </div>
    </div>

    {{-- Tenants --}}
    <div class="col-xl col-md-4 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="stat-icon" style="background:#fff7ed;"><i class="fas fa-users" style="color:#ea580c;"></i></div>
                    <span class="chip chip-orange">Tenant</span>
                </div>
                <div class="stat-value">{{ $totalTenants }}</div>
                <div class="stat-label">Total Tenants</div>
                <div class="stat-footer"><i class="fas fa-circle-check text-success me-1"></i>Approved accounts</div>
            </div>
        </div>
    </div>

    {{-- Properties --}}
    <div class="col-xl col-md-4 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="stat-icon" style="background:#faf5ff;"><i class="fas fa-building" style="color:#9333ea;"></i></div>
                    <span class="chip chip-purple">Property</span>
                </div>
                <div class="stat-value">{{ $totalProperties }}</div>
                <div class="stat-label">Total Properties</div>
                <div class="stat-footer"><i class="fas fa-house me-1 text-muted"></i>All listings</div>
            </div>
        </div>
    </div>

</div>

{{-- Commission Overview --}}
<div class="admin-card mb-4" style="border:1px solid #e9d5ff;">
    <div class="admin-card-header" style="background:#faf5ff;">
        <h6><i class="fas fa-percent text-purple me-2" style="color:#9333ea;"></i>Commission Overview</h6>
    </div>
    <div class="p-3">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="small text-muted text-uppercase" style="letter-spacing:.05em;">Total Commission</div>
                <div class="fw-bold" style="color:#7e22ce;font-size:1.1rem;">Nu. {{ number_format($totalCommission, 0) }}</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small text-muted text-uppercase" style="letter-spacing:.05em;">This Month</div>
                <div class="fw-bold" style="color:#7e22ce;font-size:1.1rem;">Nu. {{ number_format($commissionThisMonth, 0) }}</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small text-muted text-uppercase" style="letter-spacing:.05em;">Owner Net Payout</div>
                <div class="fw-bold" style="color:#0f172a;font-size:1.1rem;">Nu. {{ number_format($netOwnerPayout, 0) }}</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small text-muted text-uppercase" style="letter-spacing:.05em;">Commission Transactions</div>
                <div class="fw-bold" style="color:#0f172a;font-size:1.1rem;">{{ number_format($commissionTransactions) }}</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small text-muted text-uppercase" style="letter-spacing:.05em;">Open Move-Out Requests</div>
                <div class="fw-bold" style="color:#ea580c;font-size:1.1rem;">{{ number_format($openMoveOutRequests ?? 0) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- â”€â”€ Notification Alerts â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
@if($notifPendingUsers->count() > 0 || $notifPendingProperties->count() > 0 || $notifPendingRentals->count() > 0)
<div class="row g-3 mb-4">
    @if($notifPendingUsers->count() > 0)
    <div class="col-md-4">
        <div class="admin-card border border-warning border-opacity-50 h-100">
            <div class="admin-card-header" style="background:#fffbeb;">
                <h6><i class="fas fa-user-clock text-warning me-2"></i>New Registrations
                    <span class="badge bg-warning text-dark ms-1">{{ $pendingUsers }}</span>
                </h6>
                <a href="{{ route('admin.pending') }}" class="btn btn-sm btn-warning" style="font-size:.72rem;">Review</a>
            </div>
            <div class="p-0">
                @foreach($notifPendingUsers as $u)
                <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom" style="font-size:.8rem;">
                    <div class="u-avatar" style="background:#fef9c3;color:#a16207;">{{ strtoupper(substr($u->name,0,1)) }}</div>
                    <div class="flex-fill overflow-hidden">
                        <div class="fw-semibold text-truncate">{{ $u->name }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ ucfirst($u->role) }} Â· {{ \Carbon\Carbon::parse($u->created_at)->diffForHumans() }}</div>
                    </div>
                    <form action="{{ route('admin.users.approve', $u->id) }}" method="POST" class="m-0">
                        @csrf<button class="btn btn-xs btn-outline-success" style="font-size:.68rem;padding:.18rem .45rem;" title="Approve"><i class="fas fa-check"></i></button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($notifPendingProperties->count() > 0)
    <div class="col-md-4">
        <div class="admin-card border border-blue border-opacity-50 h-100" style="border-color:#bfdbfe!important">
            <div class="admin-card-header" style="background:#eff6ff;">
                <h6><i class="fas fa-house-circle-check text-primary me-2"></i>Property Requests
                    <span class="badge bg-primary ms-1">{{ $pendingProperties }}</span>
                </h6>
                <a href="{{ route('admin.properties') }}?status=pending" class="btn btn-sm btn-primary" style="font-size:.72rem;">Review</a>
            </div>
            <div class="p-0">
                @foreach($notifPendingProperties as $p)
                <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom" style="font-size:.8rem;">
                    <div class="u-avatar" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-building" style="font-size:.75rem;"></i></div>
                    <div class="flex-fill overflow-hidden">
                        <div class="fw-semibold text-truncate">{{ Str::limit($p->title, 24) }}</div>
                        <div class="text-muted" style="font-size:.72rem;">by {{ $p->owner->name ?? 'â€”' }} Â· Nu. {{ number_format($p->price, 0) }}/mo</div>
                    </div>
                    <form action="{{ route('admin.properties.approve', $p->id) }}" method="POST" class="m-0">
                        @csrf<button class="btn btn-xs btn-outline-primary" style="font-size:.68rem;padding:.18rem .45rem;" title="Approve"><i class="fas fa-check"></i></button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($notifPendingRentals->count() > 0)
    <div class="col-md-4">
        <div class="admin-card border border-success border-opacity-25 h-100">
            <div class="admin-card-header" style="background:#f0fdf4;">
                <h6><i class="fas fa-file-contract text-success me-2"></i>Rental Requests
                    <span class="badge bg-success ms-1">{{ $pendingRentals }}</span>
                </h6>
                <a href="{{ route('admin.rentals') }}?status=pending" class="btn btn-sm btn-success" style="font-size:.72rem;">View</a>
            </div>
            <div class="p-0">
                @foreach($notifPendingRentals as $r)
                <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom" style="font-size:.8rem;">
                    <div class="u-avatar" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-file-contract" style="font-size:.75rem;"></i></div>
                    <div class="flex-fill overflow-hidden">
                        <div class="fw-semibold text-truncate">{{ $r->tenant->name ?? 'â€”' }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ Str::limit($r->house->title ?? 'â€”', 22) }} Â· {{ \Carbon\Carbon::parse($r->created_at)->diffForHumans() }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endif

{{-- â”€â”€ Chart + Owner Summary â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
<div class="row g-3 mb-4">

    {{-- Revenue Chart --}}
    <div class="col-xl-8">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h6><i class="fas fa-chart-bar text-primary me-2"></i>Monthly Revenue & Commission (Last 12 Months)</h6>
                <div class="d-flex gap-2" style="font-size:.72rem;">
                    <span class="d-flex align-items-center gap-1"><span style="width:12px;height:12px;background:#3b82f6;border-radius:3px;display:inline-block;"></span>Revenue</span>
                    <span class="d-flex align-items-center gap-1"><span style="width:12px;height:12px;background:#16a34a;border-radius:3px;display:inline-block;"></span>Commission</span>
                </div>
            </div>
            <div class="p-3">
                <canvas id="revenueChart" height="90"></canvas>
            </div>
        </div>
    </div>

    {{-- Owner Revenue Summary --}}
    <div class="col-xl-4">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h6><i class="fas fa-ranking-star text-warning me-2"></i>Top Owners by Revenue</h6>
                <a href="{{ route('admin.owners') }}" class="btn btn-sm btn-outline-primary" style="font-size:.72rem;">All</a>
            </div>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead><tr><th>Owner</th><th class="text-end">Revenue</th><th class="text-end">Commission</th></tr></thead>
                    <tbody>
                        @forelse($ownersSummary as $o)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="u-avatar" style="background:#dbeafe;color:#2563eb;">{{ strtoupper(substr($o->name,0,1)) }}</div>
                                    <div>
                                        <div class="fw-600" style="font-size:.82rem;">{{ $o->name }}</div>
                                        <div class="text-muted" style="font-size:.7rem;">{{ $o->total_properties }} props</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end fw-600" style="color:#0f172a;font-size:.82rem;">Nu.{{ number_format($o->total_revenue,0) }}</td>
                            <td class="text-end fw-600" style="color:#9333ea;font-size:.82rem;">Nu.{{ number_format($o->total_commission,0) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3 small">No data yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- â”€â”€ Recent Transactions â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
<div class="admin-card mb-4">
    <div class="admin-card-header">
        <h6><i class="fas fa-money-bill-transfer text-success me-2"></i>Recent Transactions</h6>
        <a href="{{ route('admin.transactions') }}" class="btn btn-sm btn-outline-success" style="font-size:.72rem;">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Owner</th>
                    <th class="text-end">Rent Amount</th>
                    <th class="text-end">Commission ({{ $rate }}%)</th>
                    <th>Date</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions as $p)
                @php
                    $house = $p->rental->house ?? null;
                    $owner = $house->owner ?? null;
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="u-avatar" style="background:#fff7ed;color:#ea580c;">{{ strtoupper(substr($p->tenant->name ?? '?', 0, 1)) }}</div>
                            <span style="font-size:.84rem;">{{ $p->tenant->name ?? 'â€”' }}</span>
                        </div>
                    </td>
                    <td class="text-muted small">{{ Str::limit($house->title ?? 'â€”', 28) }}</td>
                    <td class="text-muted small">{{ $owner->name ?? 'â€”' }}</td>
                    <td class="text-end fw-600" style="color:#0f172a;">Nu. {{ number_format($p->amount, 0) }}</td>
                    <td class="text-end fw-600" style="color:#9333ea;">Nu. {{ number_format($p->commission_amount, 0) }}</td>
                    <td class="text-muted small">{{ $p->payment_date->format('d M Y') }}</td>
                    <td class="text-center">
                        @if($p->status === 'paid')
                            <span class="chip chip-green">Paid</span>
                        @elseif($p->status === 'pending')
                            <span class="chip chip-yellow">Pending</span>
                        @else
                            <span class="chip chip-red">Overdue</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-receipt fa-2x mb-2 d-block opacity-25"></i>No transactions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function() {
    const data   = @json($chartData);
    const labels = data.map(d => d.label);
    const rev    = data.map(d => d.revenue);
    const comm   = data.map(d => d.commission);

    new Chart(document.getElementById('revenueChart'), {
        data: {
            labels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Total Revenue (Nu.)',
                    data: rev,
                    backgroundColor: 'rgba(59,130,246,0.75)',
                    borderRadius: 6,
                    order: 2,
                },
                {
                    type: 'line',
                    label: 'Admin Commission (Nu.)',
                    data: comm,
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22,163,74,0.08)',
                    pointBackgroundColor: '#16a34a',
                    pointRadius: 4,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    order: 1,
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Nu. ' + Number(ctx.raw).toLocaleString('en-IN')
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: v => 'Nu.' + (v >= 1000 ? (v/1000).toFixed(0)+'K' : v),
                        font: { size: 11 }
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    ticks: { font: { size: 10 } },
                    grid: { display: false }
                }
            }
        }
    });
})();
</script>
@endpush

