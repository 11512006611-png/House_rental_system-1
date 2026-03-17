@extends('layouts.admin')
@section('title','Rental Activity')
@section('breadcrumb')
<li class="breadcrumb-item active">Rental Activity</li>
@endsection

@section('content')

<div class="page-header">
    <h1><i class="fas fa-file-contract me-2 text-primary"></i>Rental Activity</h1>
    <p>Monitor all rental agreements and their current statuses.</p>
    @if(isset($openMoveOutRequests) && $openMoveOutRequests > 0)
        <div class="mt-2">
            <span class="chip chip-orange">{{ $openMoveOutRequests }} open move-out requests</span>
        </div>
    @endif
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value text-success">{{ $totalActive }}</div>
            <div class="stat-label"><span class="chip chip-green">Active</span></div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value" style="color:#f59e0b;">{{ $totalPending }}</div>
            <div class="stat-label"><span class="chip chip-yellow">Pending</span></div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value text-muted">{{ $totalExpired }}</div>
            <div class="stat-label"><span class="chip chip-gray">Expired</span></div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value text-danger">{{ $totalCancelled }}</div>
            <div class="stat-label"><span class="chip chip-red">Cancelled</span></div>
        </div></div>
    </div>
</div>

{{-- Filters --}}
<div class="admin-card mb-4">
    <div class="p-3">
        <form method="GET" action="{{ route('admin.rentals') }}" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Search tenant, property..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active"    @selected(request('status')==='active')>Active</option>
                    <option value="pending"   @selected(request('status')==='pending')>Pending</option>
                    <option value="expired"   @selected(request('status')==='expired')>Expired</option>
                    <option value="cancelled" @selected(request('status')==='cancelled')>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
            @if(request()->hasAny(['search','status']))
            <div class="col-md-2">
                <a href="{{ route('admin.rentals') }}" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="fas fa-times me-1"></i>Clear
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Status Tabs --}}
<div class="mb-3 d-flex gap-2 flex-wrap">
    <a href="{{ route('admin.rentals') }}" class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">All</a>
    <a href="{{ route('admin.rentals') }}?status=active" class="btn btn-sm {{ request('status')==='active' ? 'btn-success' : 'btn-outline-success' }}">Active</a>
    <a href="{{ route('admin.rentals') }}?status=pending" class="btn btn-sm {{ request('status')==='pending' ? 'btn-warning' : 'btn-outline-warning' }}">Pending</a>
    <a href="{{ route('admin.rentals') }}?status=expired" class="btn btn-sm {{ request('status')==='expired' ? 'btn-secondary' : 'btn-outline-secondary' }}">Expired</a>
    <a href="{{ route('admin.rentals') }}?status=cancelled" class="btn btn-sm {{ request('status')==='cancelled' ? 'btn-danger' : 'btn-outline-danger' }}">Cancelled</a>
</div>

{{-- Table --}}
<div class="admin-card">
    <div class="admin-card-header">
        <h6><i class="fas fa-list me-2"></i>Rentals ({{ $rentals->total() }})</h6>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Owner</th>
                    <th class="text-end">Monthly Rent</th>
                    <th class="text-center">Rental Date</th>
                    <th class="text-center">End Date</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Move-Out</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rentals as $r)
                @php
                    $latestMoveOut = $r->moveOutRequests->sortByDesc('created_at')->first();
                @endphp
                <tr>
                    <td class="text-muted small">{{ ($rentals->currentPage() - 1) * $rentals->perPage() + $loop->iteration }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="u-avatar" style="background:#fff7ed;color:#ea580c;">{{ strtoupper(substr($r->tenant->name ?? '?', 0, 1)) }}</div>
                            <span style="font-size:.84rem;">{{ $r->tenant->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="text-muted small">{{ Str::limit($r->house->title ?? '—', 28) }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="u-avatar" style="background:#dbeafe;color:#2563eb;">{{ strtoupper(substr($r->house->owner->name ?? '?', 0, 1)) }}</div>
                            <span style="font-size:.84rem;">{{ $r->house->owner->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="text-end fw-600" style="font-size:.85rem;">Nu. {{ number_format($r->monthly_rent ?? ($r->house->price ?? 0), 0) }}</td>
                    <td class="text-center text-muted small">
                        {{ $r->rental_date ? \Carbon\Carbon::parse($r->rental_date)->format('d M Y') : '—' }}
                    </td>
                    <td class="text-center text-muted small">
                        @if($r->end_date)
                            {{ \Carbon\Carbon::parse($r->end_date)->format('d M Y') }}
                            @if($r->status === 'active' && \Carbon\Carbon::parse($r->end_date)->isPast())
                                <span class="chip chip-red ms-1" style="font-size:.65rem;">Overdue</span>
                            @endif
                        @else —
                        @endif
                    </td>
                    <td class="text-center">
                        @php $s = $r->status; @endphp
                        @if($s === 'active')    <span class="chip chip-green">Active</span>
                        @elseif($s === 'pending')<span class="chip chip-yellow">Pending</span>
                        @elseif($s === 'expired')<span class="chip chip-gray">Expired</span>
                        @else                   <span class="chip chip-red">Cancelled</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($latestMoveOut)
                            @if($latestMoveOut->status === 'requested')
                                <span class="chip chip-yellow">Requested</span>
                            @elseif($latestMoveOut->status === 'approved')
                                <span class="chip chip-blue">Approved</span>
                            @elseif($latestMoveOut->status === 'completed')
                                <span class="chip chip-green">Completed</span>
                            @else
                                <span class="chip chip-red">Rejected</span>
                            @endif
                        @else
                            <span class="chip chip-gray">None</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="fas fa-file-contract fa-2x mb-2 d-block opacity-25"></i>
                        No rental records found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rentals->hasPages())
    <div class="px-3 py-2 border-top">
        {{ $rentals->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection
