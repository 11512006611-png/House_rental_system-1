@extends('layouts.admin')
@section('title','Inspections')
@section('breadcrumb')
<li class="breadcrumb-item active">Inspections</li>
@endsection

@section('content')

<div class="page-header">
    <h1><i class="fas fa-search me-2 text-purple"></i>Inspection Requests</h1>
    <p>Review all tenant inspection requests submitted to admin.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value" style="color:#f59e0b;">{{ $pendingCount }}</div>
            <div class="stat-label"><span class="chip chip-yellow">Pending</span></div>
        </div></div>
    </div>
    <div class="col-6 col-md">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value text-primary">{{ $confirmedCount }}</div>
            <div class="stat-label"><span class="chip chip-blue">Confirmed</span></div>
        </div></div>
    </div>
    <div class="col-6 col-md">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value text-success">{{ $completedCount ?? 0 }}</div>
            <div class="stat-label"><span class="chip chip-green">Completed</span></div>
        </div></div>
    </div>
    <div class="col-6 col-md">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value text-info">{{ $rescheduledCount }}</div>
            <div class="stat-label"><span class="chip chip-blue">Rescheduled</span></div>
        </div></div>
    </div>
    <div class="col-6 col-md">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value text-secondary">{{ $cancelledCount }}</div>
            <div class="stat-label"><span class="chip chip-gray">Cancelled</span></div>
        </div></div>
    </div>
    <div class="col-6 col-md">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value text-danger">{{ $rejectedCount }}</div>
            <div class="stat-label"><span class="chip chip-red">Rejected</span></div>
        </div></div>
    </div>
</div>

<div class="admin-card mb-4">
    <div class="p-3">
        <form method="GET" action="{{ route('admin.inspections') }}" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Search tenant or property..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="pending" @selected(request('status')==='pending')>Pending</option>
                    <option value="confirmed" @selected(request('status')==='confirmed')>Confirmed</option>
                    <option value="completed" @selected(request('status')==='completed')>Completed</option>
                    <option value="rescheduled" @selected(request('status')==='rescheduled')>Rescheduled</option>
                    <option value="cancelled" @selected(request('status')==='cancelled')>Cancelled</option>
                    <option value="rejected" @selected(request('status')==='rejected')>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
            @if(request()->hasAny(['search','status']))
            <div class="col-md-2">
                <a href="{{ route('admin.inspections') }}" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="fas fa-times me-1"></i>Clear
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h6><i class="fas fa-list me-2"></i>Inspection Requests ({{ $inspections->total() }})</h6>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th class="text-center">Preferred Date</th>
                    <th class="text-center">Preferred Time</th>
                    <th>Message</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Actions</th>
                    <th class="text-center">Requested At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inspections as $inspection)
                <tr>
                    <td class="text-muted small">{{ ($inspections->currentPage() - 1) * $inspections->perPage() + $loop->iteration }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="u-avatar" style="background:#fff7ed;color:#ea580c;">{{ strtoupper(substr($inspection->tenant->name ?? '?', 0, 1)) }}</div>
                            <span style="font-size:.84rem;">{{ $inspection->tenant->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="text-muted small">{{ $inspection->house->title ?? '—' }}</td>
                    <td class="text-center text-muted small">{{ optional($inspection->preferred_date)->format('d M Y') ?? '—' }}</td>
                    <td class="text-center">
                        <span class="chip chip-blue">{{ $inspection->preferred_time ?? '—' }}</span>
                    </td>
                    <td class="text-muted small">{{ \Illuminate\Support\Str::limit($inspection->message ?? '—', 80) }}</td>
                    <td class="text-center">
                        <span class="chip chip-{{ $inspection->statusColor() }}">{{ $inspection->statusLabel() }}</span>
                    </td>
                    <td class="text-center">
                        <div class="d-inline-flex flex-wrap justify-content-center gap-1">
                            @if(in_array($inspection->status, ['pending', 'rescheduled'], true))
                                <form action="{{ route('admin.inspections.confirm', $inspection) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-check me-1"></i>Confirm
                                    </button>
                                </form>

                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#rescheduleModal-{{ $inspection->id }}">
                                    <i class="fas fa-calendar-alt me-1"></i>Reschedule
                                </button>

                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $inspection->id }}">
                                    <i class="fas fa-times me-1"></i>Reject
                                </button>
                            @endif

                            @if(in_array($inspection->status, ['pending', 'confirmed', 'rescheduled'], true))
                                <form action="{{ route('admin.inspections.cancel', $inspection) }}" method="POST" class="m-0" onsubmit="return confirm('Cancel this inspection request?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-ban me-1"></i>Cancel
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('admin.inspections.delete', $inspection) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this inspection request permanently?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash-alt me-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </td>
                    <td class="text-center text-muted small">{{ optional($inspection->created_at)->format('d M Y, h:i A') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="fas fa-search fa-2x mb-2 d-block opacity-25"></i>
                        No inspection requests found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($inspections->hasPages())
    <div class="px-3 py-2 border-top">
        {{ $inspections->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@foreach($inspections as $inspection)
<div class="modal fade" id="rescheduleModal-{{ $inspection->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.inspections.reschedule', $inspection) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reschedule Inspection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" required value="{{ optional($inspection->scheduled_at)->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="mb-3 mb-0">
                        <label class="form-label">Message to Tenant</label>
                        <textarea name="admin_message" class="form-control" rows="4" required placeholder="Explain why the inspection was rescheduled and any instructions..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Reschedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal-{{ $inspection->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.inspections.reject', $inspection) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Inspection Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-0">
                        <label class="form-label">Reason</label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="Provide a reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection
