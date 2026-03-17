@extends('layouts.admin')

@section('title', 'Owners')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Owners</li>
@endsection

@section('content')

    <div class="page-header">
        <h1><i class="fas fa-user-tie me-2 text-primary"></i>Owners / Landlords</h1>
        <p>All registered property owners, their listings, and revenue earned.</p>
    </div>

    @forelse($owners as $o)
    <div class="admin-card mb-4">
        {{-- Owner header --}}
        <div class="admin-card-header">
            <div class="d-flex align-items-center gap-3">
                <div class="u-avatar" style="background:#dbeafe;color:#2563eb;width:44px;height:44px;font-size:.9rem;">
                    {{ strtoupper(substr($o->name, 0, 1)) }}
                </div>
                <div>
                    <div class="fw-bold" style="font-size:1rem;color:#0f172a;">{{ $o->name }}</div>
                    <div class="text-muted small">
                        <a href="mailto:{{ $o->email }}" class="text-decoration-none text-muted">{{ $o->email }}</a>
                        @if($o->phone) &nbsp;&bull;&nbsp; {{ $o->phone }} @endif
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="text-center">
                    <div class="fw-700" style="font-size:1.1rem;color:#9333ea;">{{ $o->total_properties }}</div>
                    <div style="font-size:.67rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Properties</div>
                </div>
                <div class="text-center">
                    <div class="fw-700" style="font-size:1.1rem;color:#16a34a;">Nu.&nbsp;{{ number_format($o->total_revenue, 0) }}</div>
                    <div style="font-size:.67rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Revenue</div>
                </div>
                <div class="text-center">
                    <div class="fw-700" style="font-size:1.1rem;color:#9333ea;">Nu.&nbsp;{{ number_format($o->total_commission ?? 0, 0) }}</div>
                    <div style="font-size:.67rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Commission</div>
                </div>
                @if($o->status === 'approved')
                    <span class="badge" style="background:#f0fdf4;color:#16a34a;">Approved</span>
                @elseif($o->status === 'pending')
                    <span class="badge" style="background:#fef9c3;color:#a16207;">Pending</span>
                @else
                    <span class="badge" style="background:#fef2f2;color:#dc2626;">Rejected</span>
                @endif
            </div>
        </div>

        {{-- Properties --}}
        @if($o->properties->count() > 0)
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Property Title</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Beds / Baths</th>
                        <th class="text-end">Rent / Month</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($o->properties as $house)
                    <tr>
                        <td>
                            <a href="{{ route('houses.show', $house->id) }}"
                               class="text-decoration-none fw-semibold text-primary" target="_blank">
                                {{ $house->title }}
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border" style="font-size:.72rem;">{{ $house->type }}</span>
                        </td>
                        <td class="text-muted small">
                            <i class="fas fa-location-dot me-1 text-danger"></i>
                            {{ $house->location }}
                        </td>
                        <td class="text-muted small">
                            <i class="fas fa-bed me-1"></i>{{ $house->bedrooms }}
                            &nbsp;&bull;&nbsp;
                            <i class="fas fa-bath me-1"></i>{{ $house->bathrooms }}
                        </td>
                        <td class="text-end fw-semibold" style="color:#16a34a;">
                            Nu. {{ number_format($house->price, 0) }}
                        </td>
                        <td class="text-center">
                            @if($house->status === 'available')
                                <span class="badge" style="background:#f0fdf4;color:#16a34a;font-size:.68rem;">Available</span>
                            @elseif($house->status === 'rented')
                                <span class="badge" style="background:#eff6ff;color:#2563eb;font-size:.68rem;">Rented</span>
                            @else
                                <span class="badge" style="background:#fef9c3;color:#a16207;font-size:.68rem;">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-3 text-muted small">
            <i class="fas fa-house-circle-xmark me-1"></i>No properties listed yet.
        </div>
        @endif
    </div>
    @empty
    <div class="admin-card">
        <div class="text-center py-5 text-muted">
            <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
            <h5 class="fw-semibold">No owners registered yet.</h5>
        </div>
    </div>
    @endforelse

@endsection
