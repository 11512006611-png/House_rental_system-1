@extends('layouts.owner')

@section('title', 'My Properties')

@section('breadcrumb')
    <li class="breadcrumb-item active">My Properties</li>
@endsection

@section('content')

{{-- ── Header ────────────────────────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h1 class="mb-0" style="font-size:1.35rem;font-weight:800;color:#0f172a;">My Properties</h1>
        <p class="text-muted mb-0" style="font-size:.82rem;">Manage and track all your rental listings</p>
    </div>
    <a href="{{ route('houses.create') }}" class="btn btn-sm d-flex align-items-center gap-2"
       style="background:var(--ob-accent);color:#fff;border-radius:10px;font-weight:600;font-size:.82rem;padding:.5rem 1rem;">
        <i class="fas fa-plus"></i> Add Property
    </a>
</div>

{{-- ── Stat Chips ────────────────────────────────────────────────────────── --}}
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('owner.properties') }}"
       class="chip {{ !request('status') ? 'chip-teal' : 'chip-gray' }} text-decoration-none" style="padding:.32rem .9rem;font-size:.75rem;">
        All <strong class="ms-1">{{ $totalProperties }}</strong>
    </a>
    <a href="{{ route('owner.properties') }}?status=available"
       class="chip {{ request('status') === 'available' ? 'chip-green' : 'chip-gray' }} text-decoration-none" style="padding:.32rem .9rem;font-size:.75rem;">
        Available <strong class="ms-1">{{ $availableCount }}</strong>
    </a>
    <a href="{{ route('owner.properties') }}?status=rented"
       class="chip {{ request('status') === 'rented' ? 'chip-blue' : 'chip-gray' }} text-decoration-none" style="padding:.32rem .9rem;font-size:.75rem;">
        Rented <strong class="ms-1">{{ $rentedCount }}</strong>
    </a>
    <a href="{{ route('owner.properties') }}?status=pending"
       class="chip {{ request('status') === 'pending' ? 'chip-yellow' : 'chip-gray' }} text-decoration-none" style="padding:.32rem .9rem;font-size:.75rem;">
        Pending <strong class="ms-1">{{ $pendingCount }}</strong>
    </a>
</div>

{{-- ── Filter Bar ────────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('owner.properties') }}" class="ob-card p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-auto" style="display:none;">
            @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
        </div>
        <div class="col-12 col-sm">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Search by title…" value="{{ request('search') }}"
                   style="border-radius:8px;font-size:.83rem;">
        </div>
        <div class="col-auto">
            <select name="status" class="form-select form-select-sm" style="border-radius:8px;font-size:.83rem;">
                <option value="">All statuses</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                <option value="rented"    {{ request('status') === 'rented'    ? 'selected' : '' }}>Rented</option>
                <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                <option value="rejected"  {{ request('status') === 'rejected'  ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
        <div class="col-auto d-flex gap-2">
            <button type="submit" class="btn btn-sm"
                    style="background:var(--ob-accent);color:#fff;border-radius:8px;font-size:.82rem;">
                <i class="fas fa-magnifying-glass me-1"></i>Search
            </button>
            @if(request()->hasAny(['search','status']))
            <a href="{{ route('owner.properties') }}" class="btn btn-sm btn-light" style="border-radius:8px;font-size:.82rem;">
                <i class="fas fa-xmark"></i>
            </a>
            @endif
        </div>
    </div>
</form>

{{-- ── Table ─────────────────────────────────────────────────────────────── --}}
<div class="ob-card">
    @if($houses->isNotEmpty())
    <div class="table-responsive">
        <table class="table ob-table mb-0">
            <thead>
                <tr>
                    <th>Property</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Price / mo</th>
                    <th>Current Tenant</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($houses as $house)
                @php
                    $lease  = $house->rentals->first();
                    $tenant = $lease?->tenant;
                    $displayStatus = in_array($house->status, ['pending', 'rejected'], true)
                        ? $house->status
                        : ($lease ? 'rented' : 'available');
                    $statusColors = [
                        'available' => 'chip-green',
                        'rented'    => 'chip-blue',
                        'pending'   => 'chip-yellow',
                        'rejected'  => 'chip-red',
                    ];
                @endphp
                <tr>
                    {{-- Property --}}
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            @if($house->image)
                            <img src="{{ $house->getImageUrlAttribute() }}" alt=""
                                 class="rounded-2" style="width:44px;height:44px;object-fit:cover;flex-shrink:0;">
                            @else
                            <div class="rounded-2 d-flex align-items-center justify-content-center"
                                 style="width:44px;height:44px;background:#f1f5f9;flex-shrink:0;">
                                <i class="fas fa-home" style="color:#94a3b8;"></i>
                            </div>
                            @endif
                            <div>
                                <a href="{{ route('houses.show', $house) }}"
                                   class="fw-600 text-decoration-none" style="font-size:.84rem;color:#0f172a;">
                                   {{ $house->title }}
                                </a>
                                <div style="font-size:.7rem;color:#94a3b8;">
                                    {{ $house->bedrooms }}bd · {{ $house->bathrooms }}ba
                                </div>
                                @if(in_array($house->status, ['pending', 'rejected'], true))
                                <div style="font-size:.7rem;color:#475569;" class="mt-1">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Inspection:
                                    {{ $house->inspection_scheduled_at ? $house->inspection_scheduled_at->format('d M Y, h:i A') : 'Waiting for admin schedule' }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    {{-- Location --}}
                    <td style="font-size:.82rem;color:#475569;">
                        {{ $house->locationModel?->name ?? $house->location ?? '—' }}
                    </td>
                    {{-- Type --}}
                    <td style="font-size:.82rem;">{{ ucfirst($house->type ?? '—') }}</td>
                    {{-- Price --}}
                    <td class="fw-600" style="font-size:.84rem;">Nu {{ number_format($house->price) }}</td>
                    {{-- Current Tenant --}}
                    <td>
                        @if($tenant)
                        <div class="d-flex align-items-center gap-2">
                            <div class="ob-avatar" style="width:26px;height:26px;font-size:.65rem;">
                                {{ strtoupper(substr($tenant->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-size:.8rem;font-weight:600;">{{ $tenant->name }}</div>
                                <div style="font-size:.68rem;color:#94a3b8;">
                                    Until {{ optional($lease->end_date)->format('d M Y') ?? '—' }}
                                </div>
                            </div>
                        </div>
                        @else
                        <span style="font-size:.8rem;color:#94a3b8;">—</span>
                        @endif
                    </td>
                    {{-- Status --}}
                    <td>
                        <span class="chip {{ $statusColors[$displayStatus] ?? 'chip-gray' }}">
                            {{ ucfirst($displayStatus) }}
                        </span>
                        @if(!in_array($displayStatus, ['pending', 'rejected'], true))
                        <div style="font-size:.68rem;color:#64748b;" class="mt-1">Admin managed</div>
                        @endif
                    </td>
                    {{-- Actions --}}
                    <td>
                        @php
                            $hasActiveRental = $house->rentals->isNotEmpty();
                            $canDelete = $displayStatus !== 'rented' && ! $hasActiveRental;
                            $canEdit = ! $hasActiveRental;
                        @endphp
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('houses.show', $house) }}"
                               class="btn btn-sm btn-light" style="border-radius:7px;font-size:.75rem;"
                               title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($canEdit)
                            <a href="{{ route('houses.edit', $house) }}"
                               class="btn btn-sm" style="border-radius:7px;font-size:.75rem;background:#dbeafe;color:#1d4ed8;border:none;"
                               title="Edit / Adjust">
                                <i class="fas fa-pen"></i>
                            </a>
                            @endif
                            @if($canDelete)
                            <form action="{{ route('houses.destroy', $house) }}" method="POST"
                                  onsubmit="return confirm('Delete this property? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm" title="Delete"
                                        style="border-radius:7px;font-size:.75rem;background:#fee2e2;color:#b91c1c;border:none;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @else
                            <button type="button" class="btn btn-sm" disabled
                                    title="Delete is unavailable while this property has an active tenant."
                                    style="border-radius:7px;font-size:.75rem;background:#f1f5f9;color:#94a3b8;border:none;cursor:not-allowed;"
                                    aria-disabled="true">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($houses->hasPages())
    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top"
         style="font-size:.78rem;color:#64748b;background:#f8fafc;border-radius:0 0 14px 14px;">
        <span>Showing {{ $houses->firstItem() }}–{{ $houses->lastItem() }} of {{ $houses->total() }}</span>
        {{ $houses->links('pagination::bootstrap-5') }}
    </div>
    @endif

    @else
    <div class="text-center py-5" style="color:#94a3b8;">
        <i class="fas fa-building d-block mb-3" style="font-size:3rem;opacity:.2;"></i>
        <p class="mb-1" style="font-size:.9rem;font-weight:600;">No properties found</p>
        <p style="font-size:.8rem;margin:0 0 1rem;">
            @if(request()->hasAny(['search','status']))
                Try adjusting your filters.
            @else
                You haven't added any properties yet.
            @endif
        </p>
        <a href="{{ route('houses.create') }}" class="btn btn-sm"
           style="background:var(--ob-accent);color:#fff;border-radius:10px;font-size:.82rem;">
            <i class="fas fa-plus me-1"></i> Add Your First Property
        </a>
    </div>
    @endif
</div>

@endsection
