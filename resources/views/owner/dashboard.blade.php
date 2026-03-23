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
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h1 class="mb-0" style="font-size:1.4rem;font-weight:800;color:#0f172a;">
            Welcome back, {{ $owner->name }} <span style="font-size:1.25rem;">👋</span>
        </h1>
        <p class="text-muted mb-0" style="font-size:.82rem;">
            Here's an overview of your rental portfolio — {{ now()->format('l, d F Y') }}
        </p>
    </div>
    <a href="{{ route('houses.create') }}" class="btn btn-sm d-flex align-items-center gap-2"
       style="background:var(--ob-accent);color:#fff;border-radius:10px;font-weight:600;font-size:.82rem;padding:.5rem 1rem;">
        <i class="fas fa-plus"></i> Add Property
    </a>
</div>

@if($pendingMoveOutRequests->isNotEmpty())
<div class="alert d-flex align-items-start justify-content-between gap-3 mb-3"
     style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:12px;">
    <div>
        <div class="fw-bold mb-1"><i class="fas fa-bell me-2"></i>Move-Out Notification</div>
        <div class="small mb-2">{{ $pendingMoveOutRequests->count() }} tenant request(s) to move out need your attention.</div>
        <div class="small">
            @foreach($pendingMoveOutRequests as $pendingMove)
                <div>
                    {{ $pendingMove->tenant?->name ?? 'Tenant' }} wants to move out from {{ $pendingMove->house?->title ?? 'Property' }}
                    ({{ optional($pendingMove->move_out_date)->format('d M Y') ?? 'date not set' }}).
                </div>
            @endforeach
        </div>
    </div>
    <a href="{{ route('owner.tenants') }}" class="btn btn-sm btn-outline-danger" style="white-space:nowrap;">Review Now</a>
</div>
@endif

<div class="row g-3">

    {{-- ── Profile Card ──────────────────────────────────────────────────── --}}
    <div class="col-12 col-lg-4">
        <div class="profile-card h-100">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="profile-avatar">{{ strtoupper(substr($owner->name, 0, 1)) }}</div>
                <div>
                    <div style="font-size:1rem;font-weight:700;line-height:1.3;">{{ $owner->name }}</div>
                    <div style="font-size:.75rem;opacity:.7;">{{ $owner->email }}</div>
                    @if($owner->phone)
                    <div style="font-size:.75rem;opacity:.6;"><i class="fas fa-phone me-1"></i>{{ $owner->phone }}</div>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @php
                    $statusMap = ['approved' => ['#bbf7d0','#15803d'], 'pending' => ['#fef9c3','#a16207'], 'suspended' => ['#fee2e2','#b91c1c']];
                    [$sbg, $sfg] = $statusMap[$owner->status] ?? ['#f1f5f9','#475569'];
                @endphp
                <span class="px-2 py-1 rounded" style="font-size:.65rem;font-weight:700;background:{{ $sbg }};color:{{ $sfg }};">
                    {{ ucfirst($owner->status) }}
                </span>
                <span class="px-2 py-1 rounded" style="font-size:.65rem;font-weight:700;background:rgba(255,255,255,.15);color:#fff;">
                    <i class="fas fa-calendar-alt me-1"></i>Since {{ $owner->created_at->format('M Y') }}
                </span>
            </div>
            <hr style="border-color:rgba(255,255,255,.12);margin:1rem 0;">
            <div class="row g-2">
                <div class="col-4"><div class="mini-stat">
                    <div class="mini-stat-val">{{ $totalProperties }}</div>
                    <div class="mini-stat-lbl">Properties</div>
                </div></div>
                <div class="col-4"><div class="mini-stat">
                    <div class="mini-stat-val">{{ $totalActiveTenants }}</div>
                    <div class="mini-stat-lbl">Tenants</div>
                </div></div>
                <div class="col-4"><div class="mini-stat">
                    <div class="mini-stat-val">Nu {{ number_format($totalRevenue / 1000, 0) }}K</div>
                    <div class="mini-stat-lbl">Revenue</div>
                </div></div>
            </div>
        </div>
    </div>

    {{-- ── Stat Cards ────────────────────────────────────────────────────── --}}
    <div class="col-12 col-lg-8">
        <div class="row g-3 h-100">
            <div class="col-6">
                <div class="stat-card h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">Total Properties</div>
                            <div class="stat-value" style="color:#0f172a;">{{ $totalProperties }}</div>
                            <div class="d-flex gap-2 flex-wrap mt-1">
                                <span class="chip chip-green">{{ $availableProperties }} avail</span>
                                <span class="chip chip-blue">{{ $rentedProperties }} rented</span>
                                @if($pendingProperties > 0)
                                <span class="chip chip-yellow">{{ $pendingProperties }} pending</span>
                                @endif
                            </div>
                        </div>
                        <div class="stat-icon" style="background:#dcfce7;">
                            <i class="fas fa-building" style="color:#15803d;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="stat-card h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">Active Tenants</div>
                            <div class="stat-value" style="color:#1d4ed8;">{{ $totalActiveTenants }}</div>
                            <div class="stat-footer">Currently renting your properties</div>
                        </div>
                        <div class="stat-icon" style="background:#dbeafe;">
                            <i class="fas fa-users" style="color:#1d4ed8;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="stat-card h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">Total Revenue</div>
                            <div class="stat-value" style="color:#15803d;">Nu {{ number_format($totalRevenue) }}</div>
                            <div class="stat-footer">All confirmed payments</div>
                        </div>
                        <div class="stat-icon" style="background:#dcfce7;">
                            <i class="fas fa-money-bill-wave" style="color:#15803d;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="stat-card h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">Pending / Overdue</div>
                            <div class="stat-value" style="color:{{ $overdueAmount > 0 ? '#b91c1c' : '#a16207' }};">
                                Nu {{ number_format($pendingAmount + $overdueAmount) }}
                            </div>
                            <div class="d-flex gap-2 mt-1">
                                @if($pendingAmount > 0)
                                <span class="chip chip-yellow">Nu {{ number_format($pendingAmount) }} pending</span>
                                @endif
                                @if($overdueAmount > 0)
                                <span class="chip chip-red">Nu {{ number_format($overdueAmount) }} overdue</span>
                                @endif
                                @if($pendingAmount == 0 && $overdueAmount == 0)
                                <span class="chip chip-green"><i class="fas fa-circle-check me-1"></i>All clear</span>
                                @endif
                            </div>
                        </div>
                        <div class="stat-icon" style="background:{{ $overdueAmount > 0 ? '#fee2e2' : '#fef9c3' }};">
                            <i class="fas fa-clock" style="color:{{ $overdueAmount > 0 ? '#b91c1c' : '#a16207' }};"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Revenue Chart ──────────────────────────────────────────────────── --}}
    <div class="col-12 col-xl-8">
        <div class="ob-card">
            <div class="ob-card-header">
                <h6><i class="fas fa-chart-bar me-2" style="color:var(--ob-accent);"></i>Revenue — Last 6 Months</h6>
            </div>
            <div class="p-3" style="position:relative;height:260px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Property Summary ───────────────────────────────────────────────── --}}
    <div class="col-12 col-xl-4">
        <div class="ob-card h-100">
            <div class="ob-card-header">
                <h6><i class="fas fa-building me-2" style="color:var(--ob-accent);"></i>Properties</h6>
                <a href="{{ route('owner.properties') }}" style="font-size:.75rem;color:var(--ob-accent);">See all →</a>
            </div>
            <div class="p-3">
                @forelse($properties as $prop)
                @php
                    $activeLease = $prop->rentals->first();
                    $statusColors = [
                        'available' => 'chip-green',
                        'rented'    => 'chip-blue',
                        'pending'   => 'chip-yellow',
                        'rejected'  => 'chip-red',
                    ];
                @endphp
                <div class="d-flex align-items-center gap-3 mb-3">
                    @if($prop->image)
                    <img src="{{ $prop->getImageUrlAttribute() }}" alt="" class="rounded-2"
                         style="width:48px;height:48px;object-fit:cover;flex-shrink:0;">
                    @else
                    <div class="rounded-2 d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px;background:#f1f5f9;flex-shrink:0;">
                        <i class="fas fa-home" style="color:#94a3b8;"></i>
                    </div>
                    @endif
                    <div class="flex-fill overflow-hidden">
                        <div class="fw-600 text-truncate" style="font-size:.83rem;">{{ $prop->title }}</div>
                        <div style="font-size:.7rem;color:#64748b;">Nu {{ number_format($prop->price) }}/mo</div>
                    </div>
                    <span class="chip {{ $statusColors[$prop->status] ?? 'chip-gray' }}">{{ ucfirst($prop->status) }}</span>
                </div>
                @empty
                <div class="text-center py-4 text-muted" style="font-size:.85rem;">
                    <i class="fas fa-building d-block mb-1" style="font-size:1.8rem;opacity:.3;"></i>
                    No properties yet.<br>
                    <a href="{{ route('houses.create') }}" style="color:var(--ob-accent);">Add your first listing →</a>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Recent Payments ────────────────────────────────────────────────── --}}
    <div class="col-12 col-xl-7">
        <div class="ob-card">
            <div class="ob-card-header">
                <h6><i class="fas fa-receipt me-2" style="color:var(--ob-accent);"></i>Recent Payments</h6>
                <a href="{{ route('owner.payments') }}" style="font-size:.75rem;color:var(--ob-accent);">View all →</a>
            </div>
            @if($recentPayments->isNotEmpty())
            <div class="table-responsive">
                <table class="table ob-table mb-0">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Property</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentPayments as $pay)
                        @php
                            $payChip = ['paid' => 'chip-green', 'pending' => 'chip-yellow', 'overdue' => 'chip-red'][$pay->status] ?? 'chip-gray';
                        @endphp
                        <tr>
                            <td>{{ $pay->tenant?->name ?? '—' }}</td>
                            <td class="text-truncate" style="max-width:130px;">{{ $pay->rental?->house?->title ?? '—' }}</td>
                            <td class="fw-600">Nu {{ number_format($pay->amount) }}</td>
                            <td style="color:#64748b;">{{ optional($pay->payment_date)->format('d M Y') ?? '—' }}</td>
                            <td><span class="chip {{ $payChip }}">{{ ucfirst($pay->status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5 text-muted" style="font-size:.85rem;">
                <i class="fas fa-receipt d-block mb-2" style="font-size:2rem;opacity:.25;"></i>
                No payments recorded yet.
            </div>
            @endif
        </div>
    </div>

    {{-- ── Recent Tenants ─────────────────────────────────────────────────── --}}
    <div class="col-12 col-xl-5">
        <div class="ob-card h-100">
            <div class="ob-card-header">
                <h6><i class="fas fa-users me-2" style="color:var(--ob-accent);"></i>Active Tenants</h6>
                <a href="{{ route('owner.tenants') }}" style="font-size:.75rem;color:var(--ob-accent);">View all →</a>
            </div>
            @if($recentTenants->isNotEmpty())
            <div class="p-2">
                @foreach($recentTenants as $rental)
                <div class="d-flex align-items-center gap-3 p-2 rounded-3 mb-1"
                     style="border:1px solid #f1f5f9;transition:background .15s;"
                     onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <div class="ob-avatar" style="flex-shrink:0;">
                        {{ strtoupper(substr($rental->tenant?->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-fill overflow-hidden">
                        <div class="fw-600 text-truncate" style="font-size:.83rem;">{{ $rental->tenant?->name ?? 'Unknown' }}</div>
                        <div class="text-truncate" style="font-size:.7rem;color:#64748b;">{{ $rental->house?->title ?? '—' }}</div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="fw-600" style="font-size:.8rem;color:var(--ob-accent);">Nu {{ number_format($rental->house?->price ?? 0) }}</div>
                        <div style="font-size:.65rem;color:#94a3b8;">/month</div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-5 text-muted" style="font-size:.85rem;">
                <i class="fas fa-users d-block mb-2" style="font-size:2rem;opacity:.25;"></i>
                No active tenants yet.
            </div>
            @endif
        </div>
    </div>

    {{-- ── New Rental Request Notifications ────────────────────────────── --}}
    <div class="col-12">
        <div class="ob-card">
            <div class="ob-card-header">
                <h6><i class="fas fa-bell me-2" style="color:var(--ob-accent);"></i>New Rental Requests</h6>
                <a href="{{ route('owner.tenants') }}?status=pending" style="font-size:.75rem;color:var(--ob-accent);">Manage requests →</a>
            </div>
            @if($latestRentalRequests->isNotEmpty())
            <div class="table-responsive">
                <table class="table ob-table mb-0">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Property</th>
                            <th>Requested On</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($latestRentalRequests as $request)
                        <tr>
                            <td>{{ $request->tenant?->name ?? '—' }}</td>
                            <td class="text-truncate" style="max-width:210px;">{{ $request->house?->title ?? '—' }}</td>
                            <td style="color:#64748b;">{{ $request->created_at->format('d M Y, h:i A') }}</td>
                            <td><span class="chip chip-yellow">Pending</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-4 text-muted" style="font-size:.85rem;">
                <i class="fas fa-bell-slash d-block mb-2" style="font-size:1.6rem;opacity:.3;"></i>
                No new rental requests right now.
            </div>
            @endif
        </div>
    </div>

    {{-- ── Tenant Move-Out Requests ─────────────────────────────────────── --}}
    <div class="col-12">
        <div class="ob-card">
            <div class="ob-card-header">
                <h6><i class="fas fa-door-open me-2" style="color:#dc2626;"></i>Tenant Move-Out Requests</h6>
                <a href="{{ route('owner.tenants') }}" style="font-size:.75rem;color:var(--ob-accent);">Manage tenants →</a>
            </div>
            @if($latestMoveOutRequests->isNotEmpty())
            <div class="table-responsive">
                <table class="table ob-table mb-0">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Property</th>
                            <th>Move-Out Date</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($latestMoveOutRequests as $moveOut)
                        @php
                            $statusChip = [
                                'requested' => 'chip-yellow',
                                'approved' => 'chip-blue',
                                'completed' => 'chip-green',
                                'rejected' => 'chip-red',
                            ][$moveOut->status] ?? 'chip-gray';
                        @endphp
                        <tr>
                            <td>{{ $moveOut->tenant?->name ?? '—' }}</td>
                            <td class="text-truncate" style="max-width:180px;">{{ $moveOut->house?->title ?? '—' }}</td>
                            <td style="color:#64748b;">{{ optional($moveOut->move_out_date)->format('d M Y') ?? '—' }}</td>
                            <td class="text-truncate" style="max-width:260px;">{{ $moveOut->reason }}</td>
                            <td><span class="chip {{ $statusChip }}">{{ ucfirst($moveOut->status) }}</span></td>
                            <td style="color:#b91c1c;font-weight:600;">Tenant wants to move out</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-4 text-muted" style="font-size:.85rem;">
                <i class="fas fa-door-closed d-block mb-2" style="font-size:1.6rem;opacity:.3;"></i>
                No tenant move-out messages right now.
            </div>
            @endif
        </div>
    </div>

    {{-- ── Moved-Out Tenant Records ─────────────────────────────────────── --}}
    <div class="col-12">
        <div class="ob-card">
            <div class="ob-card-header">
                <h6><i class="fas fa-clipboard-check me-2" style="color:#059669;"></i>Moved-Out Tenant Records</h6>
                <a href="{{ route('owner.tenants') }}" style="font-size:.75rem;color:var(--ob-accent);">See tenants →</a>
            </div>
            @if($movedOutTenantRecords->isNotEmpty())
            <div class="table-responsive">
                <table class="table ob-table mb-0">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Property</th>
                            <th>Requested Move-Out Date</th>
                            <th>Completed On</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movedOutTenantRecords as $movedOut)
                        <tr>
                            <td>{{ $movedOut->tenant?->name ?? '—' }}</td>
                            <td class="text-truncate" style="max-width:200px;">{{ $movedOut->house?->title ?? '—' }}</td>
                            <td style="color:#64748b;">{{ optional($movedOut->move_out_date)->format('d M Y') ?? '—' }}</td>
                            <td style="color:#047857;font-weight:600;">{{ optional($movedOut->completed_at)->format('d M Y, h:i A') ?? '—' }}</td>
                            <td class="text-truncate" style="max-width:280px;">{{ $movedOut->reason }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-4 text-muted" style="font-size:.85rem;">
                <i class="fas fa-clipboard d-block mb-2" style="font-size:1.6rem;opacity:.3;"></i>
                No completed move-out records yet.
            </div>
            @endif
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
const chartData = @json($chartData);

const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartData.map(d => d.label),
        datasets: [{
            label: 'Revenue (Nu)',
            data: chartData.map(d => d.revenue),
            backgroundColor: chartData.map((d, i) =>
                i === chartData.length - 1 ? '#10b981' : 'rgba(16,185,129,.25)'
            ),
            borderColor: '#10b981',
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => 'Nu ' + ctx.raw.toLocaleString()
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
            y: {
                beginAtZero: true,
                grid: { color: '#f1f5f9' },
                ticks: {
                    font: { size: 11 },
                    callback: val => 'Nu ' + (val >= 1000 ? (val/1000).toFixed(0) + 'K' : val)
                }
            }
        }
    }
});
</script>
@endpush
