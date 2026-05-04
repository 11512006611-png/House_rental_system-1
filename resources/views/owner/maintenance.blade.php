@extends('layouts.owner')

@section('title', 'Maintenance Requests')

@section('breadcrumb')
    <li class="breadcrumb-item active">Maintenance Requests</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h1 class="mb-0" style="font-size:1.35rem;font-weight:800;color:#0f172a;">Maintenance Requests</h1>
        <p class="text-muted mb-0" style="font-size:.82rem;">Track and respond to tenant maintenance issues.</p>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('owner.maintenance') }}" class="chip {{ !request('status') ? 'chip-teal' : 'chip-gray' }} text-decoration-none" style="padding:.32rem .9rem;font-size:.75rem;">All</a>
    <a href="{{ route('owner.maintenance', ['status' => 'pending']) }}" class="chip {{ request('status') === 'pending' ? 'chip-yellow' : 'chip-gray' }} text-decoration-none" style="padding:.32rem .9rem;font-size:.75rem;">Pending <strong class="ms-1">{{ $statusCounts['pending'] }}</strong></a>
    <a href="{{ route('owner.maintenance', ['status' => 'in_progress']) }}" class="chip {{ request('status') === 'in_progress' ? 'chip-teal' : 'chip-gray' }} text-decoration-none" style="padding:.32rem .9rem;font-size:.75rem;">In Progress <strong class="ms-1">{{ $statusCounts['in_progress'] }}</strong></a>
    <a href="{{ route('owner.maintenance', ['status' => 'resolved']) }}" class="chip {{ request('status') === 'resolved' ? 'chip-green' : 'chip-gray' }} text-decoration-none" style="padding:.32rem .9rem;font-size:.75rem;">Resolved <strong class="ms-1">{{ $statusCounts['resolved'] }}</strong></a>
</div>

<form method="GET" action="{{ route('owner.maintenance') }}" class="ob-card p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Tenant, email, property, issue...">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Category</label>
            <select name="category" class="form-select form-select-sm">
                <option value="">All Categories</option>
                @foreach(['water','electricity','plumbing','security','cleaning','other'] as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ ucfirst($category) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-sm" style="background:var(--ob-accent);color:#fff;border-radius:8px;font-size:.82rem;">
                <i class="fas fa-filter me-1"></i>Filter
            </button>
        </div>
        @if(request()->hasAny(['search','status','category']))
            <div class="col-md-2">
                <a href="{{ route('owner.maintenance') }}" class="btn btn-sm btn-light" style="border-radius:8px;font-size:.82rem;">
                    <i class="fas fa-xmark"></i> Clear
                </a>
            </div>
        @endif
    </div>
</form>

<div class="ob-card">
    <div class="table-responsive">
        <table class="table ob-table mb-0">
            <thead>
                <tr>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Issue</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Preferred Date</th>
                    <th>Owner Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $requestItem)
                    <tr>
                        <td>
                            <div class="fw-600" style="font-size:.84rem;">{{ $requestItem->tenant->name ?? '—' }}</div>
                            <div style="font-size:.7rem;color:#94a3b8;">{{ $requestItem->tenant->email ?? '' }}</div>
                        </td>
                        <td>{{ $requestItem->house->title ?? ('Property #' . $requestItem->house_id) }}</td>
                        <td>
                            <div class="fw-600" style="font-size:.8rem;">{{ ucfirst($requestItem->category) }}</div>
                            <div style="font-size:.72rem;color:#64748b;">{{ \Illuminate\Support\Str::limit($requestItem->description, 90) }}</div>
                        </td>
                        <td><span class="chip chip-gray">{{ ucfirst($requestItem->priority) }}</span></td>
                        <td>
                            @php
                                $statusChip = match($requestItem->status) {
                                    'pending' => 'chip-yellow',
                                    'in_progress' => 'chip-teal',
                                    'resolved' => 'chip-green',
                                    'rejected' => 'chip-red',
                                    default => 'chip-gray'
                                };
                            @endphp
                            <span class="chip {{ $statusChip }}">{{ ucfirst(str_replace('_', ' ', $requestItem->status)) }}</span>
                        </td>
                        <td>{{ $requestItem->preferred_visit_date?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <form action="{{ route('owner.maintenance.update', $requestItem) }}" method="POST" class="d-flex flex-column gap-1">
                                @csrf
                                <div class="d-flex gap-1">
                                    <select name="status" class="form-select form-select-sm" required>
                                        <option value="in_progress" @selected($requestItem->status === 'in_progress')>In Progress</option>
                                        <option value="resolved" @selected($requestItem->status === 'resolved')>Resolved</option>
                                        <option value="rejected" @selected($requestItem->status === 'rejected')>Rejected</option>
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary">Update</button>
                                </div>
                                <textarea name="owner_response" rows="2" class="form-control form-control-sm" placeholder="Response for tenant (optional)">{{ $requestItem->owner_response }}</textarea>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No maintenance requests found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($requests->hasPages())
        <div class="p-3 border-top">{{ $requests->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
