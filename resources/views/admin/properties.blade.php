@extends('layouts.admin')
@section('title','Manage Properties')
@section('breadcrumb')
<li class="breadcrumb-item active">Properties</li>
@endsection

@section('content')

<div class="page-header">
    <h1><i class="fas fa-building me-2 text-primary"></i>Property Management</h1>
    <p>Review, approve, or reject property listings submitted by owners.</p>
</div>

{{-- Stats row --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body p-3 text-center">
                <div class="stat-value">{{ $totalPending }}</div>
                <div class="stat-label"><span class="chip chip-yellow">Pending</span></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body p-3 text-center">
                <div class="stat-value text-success">{{ $totalAvailable }}</div>
                <div class="stat-label"><span class="chip chip-green">Available</span></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body p-3 text-center">
                <div class="stat-value text-primary">{{ $totalRented }}</div>
                <div class="stat-label"><span class="chip chip-blue">Rented</span></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body p-3 text-center">
                <div class="stat-value text-danger">{{ $totalRejected }}</div>
                <div class="stat-label"><span class="chip chip-red">Rejected</span></div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="admin-card mb-4">
    <div class="p-3">
        <form method="GET" action="{{ route('admin.properties') }}" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Search title, owner..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="pending"   @selected(request('status')==='pending')>Pending</option>
                    <option value="available" @selected(request('status')==='available')>Available</option>
                    <option value="rented"    @selected(request('status')==='rented')>Rented</option>
                    <option value="rejected"  @selected(request('status')==='rejected')>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
            @if(request()->hasAny(['search','status']))
            <div class="col-md-2">
                <a href="{{ route('admin.properties') }}" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="fas fa-times me-1"></i>Clear
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Tab pills --}}
<div class="mb-3 d-flex gap-2 flex-wrap">
    <a href="{{ route('admin.properties') }}" class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">All</a>
    <a href="{{ route('admin.properties') }}?status=pending" class="btn btn-sm {{ request('status')==='pending' ? 'btn-warning' : 'btn-outline-warning' }}">
        Pending @if($totalPending > 0)<span class="badge bg-danger ms-1">{{ $totalPending }}</span>@endif
    </a>
    <a href="{{ route('admin.properties') }}?status=available" class="btn btn-sm {{ request('status')==='available' ? 'btn-success' : 'btn-outline-success' }}">Available</a>
    <a href="{{ route('admin.properties') }}?status=rented" class="btn btn-sm {{ request('status')==='rented' ? 'btn-primary' : 'btn-outline-primary' }}">Rented</a>
    <a href="{{ route('admin.properties') }}?status=rejected" class="btn btn-sm {{ request('status')==='rejected' ? 'btn-danger' : 'btn-outline-danger' }}">Rejected</a>
</div>

{{-- Table --}}
<div class="admin-card">
    <div class="admin-card-header">
        <h6><i class="fas fa-list me-2"></i>Properties ({{ $properties->total() }})</h6>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Property</th>
                    <th>Owner</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th class="text-end">Price/mo</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($properties as $h)
                <tr>
                    <td class="text-muted small">{{ $loop->iteration }}</td>
                    <td>
                        <div class="fw-600" style="font-size:.85rem;">{{ Str::limit($h->title, 32) }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $h->bedrooms }} bed · {{ $h->bathrooms }} bath</div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="u-avatar" style="background:#dbeafe;color:#2563eb;">{{ strtoupper(substr($h->owner->name ?? '?', 0, 1)) }}</div>
                            <span style="font-size:.82rem;">{{ $h->owner->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="text-muted small">{{ $h->locationModel->name ?? $h->location ?? '—' }}</td>
                    <td class="text-muted small">{{ ucfirst($h->property_type ?? '—') }}</td>
                    <td class="text-end fw-600" style="font-size:.85rem;">Nu. {{ number_format($h->price, 0) }}</td>
                    <td class="text-center">
                        @php $s = $h->status; @endphp
                        @if($s === 'available')   <span class="chip chip-green">Available</span>
                        @elseif($s === 'rented')  <span class="chip chip-blue">Rented</span>
                        @elseif($s === 'pending') <span class="chip chip-yellow">Pending</span>
                        @elseif($s === 'rejected')<span class="chip chip-red">Rejected</span>
                        @else                     <span class="chip chip-gray">{{ $s }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            @if($s === 'pending')
                            <form action="{{ route('admin.properties.approve', $h->id) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-success"
                                    style="font-size:.72rem;padding:.2rem .5rem;"
                                    onclick="return confirm('Approve this property?')"
                                    title="Approve">
                                    <i class="fas fa-check me-1"></i>Approve
                                </button>
                            </form>
                            <form action="{{ route('admin.properties.reject', $h->id) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-danger"
                                    style="font-size:.72rem;padding:.2rem .5rem;"
                                    onclick="return confirm('Reject this property?')"
                                    title="Reject">
                                    <i class="fas fa-times me-1"></i>Reject
                                </button>
                            </form>
                            @elseif($s === 'available')
                            <form action="{{ route('admin.properties.reject', $h->id) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-outline-danger"
                                    style="font-size:.72rem;padding:.2rem .5rem;"
                                    onclick="return confirm('Reject this property? It will be removed from listings.')"
                                    title="Reject">
                                    <i class="fas fa-ban me-1"></i>Reject
                                </button>
                            </form>
                            @elseif($s === 'rejected')
                            <form action="{{ route('admin.properties.approve', $h->id) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-outline-success"
                                    style="font-size:.72rem;padding:.2rem .5rem;"
                                    onclick="return confirm('Re-approve this property?')"
                                    title="Re-approve">
                                    <i class="fas fa-rotate-right me-1"></i>Re-approve
                                </button>
                            </form>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="fas fa-building fa-2x mb-2 d-block opacity-25"></i>
                        No properties found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($properties->hasPages())
    <div class="px-3 py-2 border-top">
        {{ $properties->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection
