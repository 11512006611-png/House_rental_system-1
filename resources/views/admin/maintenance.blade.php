@extends('layouts.admin')
@section('title','Maintenance Requests')
@section('breadcrumb')
<li class="breadcrumb-item active">Maintenance Requests</li>
@endsection

@section('content')

<div class="page-header">
    <h1><i class="fas fa-wrench me-2 text-primary"></i>Maintenance Complaints</h1>
    <p>Manage all maintenance complaints and arrange repairs. Admin handles all service arrangements.</p>
    @if(isset($statusCounts['pending']) && $statusCounts['pending'] > 0)
        <div class="mt-2">
            <span class="chip chip-orange">{{ $statusCounts['pending'] }} pending complaints to review</span>
        </div>
    @endif
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value text-warning">{{ $statusCounts['pending'] ?? 0 }}</div>
            <div class="stat-label"><span class="chip chip-yellow">Pending Review</span></div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value" style="color:#f59e0b;">{{ $statusCounts['approved_for_repair'] ?? 0 }}</div>
            <div class="stat-label"><span class="chip" style="background:#fef3c7;color:#92400e;">Approved</span></div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value" style="color:#3b82f6;">{{ $statusCounts['under_repair'] ?? 0 }}</div>
            <div class="stat-label"><span class="chip chip-blue">Under Repair</span></div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100"><div class="card-body p-3 text-center">
            <div class="stat-value text-success">{{ $statusCounts['resolved'] ?? 0 }}</div>
            <div class="stat-label"><span class="chip chip-green">Resolved</span></div>
        </div></div>
    </div>
</div>

{{-- Filters --}}
<div class="admin-card mb-4">
    <div class="p-3">
        <form method="GET" action="{{ route('admin.maintenance') }}" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Search tenant, property..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="pending"     @selected(request('status')==='pending')>Pending Review</option>
                    <option value="approved_for_repair" @selected(request('status')==='approved_for_repair')>Approved for Repair</option>
                    <option value="under_repair" @selected(request('status')==='under_repair')>Under Repair</option>
                    <option value="resolved"    @selected(request('status')==='resolved')>Resolved</option>
                    <option value="rejected"    @selected(request('status')==='rejected')>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select form-select-sm">
                    <option value="">All Priority</option>
                    <option value="low"     @selected(request('priority')==='low')>Low</option>
                    <option value="medium"  @selected(request('priority')==='medium')>Medium</option>
                    <option value="high"    @selected(request('priority')==='high')>High</option>
                    <option value="urgent"  @selected(request('priority')==='urgent')>Urgent</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Category</option>
                    <option value="water"     @selected(request('category')==='water')>Water</option>
                    <option value="electricity" @selected(request('category')==='electricity')>Electricity</option>
                    <option value="plumbing"  @selected(request('category')==='plumbing')>Plumbing</option>
                    <option value="security"  @selected(request('category')==='security')>Security</option>
                    <option value="cleaning"  @selected(request('category')==='cleaning')>Cleaning</option>
                    <option value="other"     @selected(request('category')==='other')>Other</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
            @if(request()->hasAny(['search','status','priority','category']))
            <div class="col-md-1">
                <a href="{{ route('admin.maintenance') }}" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="fas fa-times"></i>
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Status Tabs --}}
<div class="mb-3 d-flex gap-2 flex-wrap">
    <a href="{{ route('admin.maintenance') }}" class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">All</a>
    <a href="{{ route('admin.maintenance') }}?status=pending" class="btn btn-sm {{ request('status')==='pending' ? 'btn-warning' : 'btn-outline-warning' }}">Pending Review</a>
    <a href="{{ route('admin.maintenance') }}?status=approved_for_repair" class="btn btn-sm {{ request('status')==='approved_for_repair' ? 'btn-outline-warning' : 'btn-outline-secondary' }}" style="border-color:#f59e0b;{{ request('status')==='approved_for_repair' ? 'background:#f59e0b;color:white;' : '' }}">Approved</a>
    <a href="{{ route('admin.maintenance') }}?status=under_repair" class="btn btn-sm {{ request('status')==='under_repair' ? 'btn-info' : 'btn-outline-info' }}">Under Repair</a>
    <a href="{{ route('admin.maintenance') }}?status=resolved" class="btn btn-sm {{ request('status')==='resolved' ? 'btn-success' : 'btn-outline-success' }}">Resolved</a>
    <a href="{{ route('admin.maintenance') }}?status=rejected" class="btn btn-sm {{ request('status')==='rejected' ? 'btn-danger' : 'btn-outline-danger' }}">Rejected</a>
</div>

{{-- Table --}}
<div class="admin-card">
    <div class="admin-card-header">
        <h6><i class="fas fa-list me-2"></i>Maintenance Complaints ({{ $maintenanceRequests->total() }})</h6>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th class="text-center">Issue</th>
                    <th class="text-center">Priority</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Responsibility</th>
                    <th class="text-center">Submitted</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($maintenanceRequests as $mr)
                <tr>
                    <td class="text-muted small">{{ ($maintenanceRequests->currentPage() - 1) * $maintenanceRequests->perPage() + $loop->iteration }}</td>
                    
                    {{-- Tenant --}}
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="u-avatar" style="background:#fff7ed;color:#ea580c;">{{ strtoupper(substr($mr->tenant->name ?? '?', 0, 1)) }}</div>
                            <div style="font-size:.84rem;">
                                <div class="fw-500">{{ $mr->tenant->name ?? '—' }}</div>
                                <div class="text-muted">{{ substr($mr->tenant->email ?? '', 0, 25) }}</div>
                            </div>
                        </div>
                    </td>
                    
                    {{-- Property --}}
                    <td class="text-muted small">
                        {{ Str::limit($mr->house->title ?? '—', 25) }}<br>
                        <span class="text-muted" style="font-size:.75rem;">ID: {{ $mr->house->id ?? '—' }}</span>
                    </td>
                    
                    {{-- Category --}}
                    <td class="text-center">
                        @php $cat = $mr->category; @endphp
                        @if($cat === 'water')
                            <span class="badge bg-info">💧 Water</span>
                        @elseif($cat === 'electricity')
                            <span class="badge bg-warning">⚡ Electricity</span>
                        @elseif($cat === 'plumbing')
                            <span class="badge bg-primary">🔧 Plumbing</span>
                        @elseif($cat === 'security')
                            <span class="badge bg-danger">🔒 Security</span>
                        @elseif($cat === 'cleaning')
                            <span class="badge bg-success">🧹 Cleaning</span>
                        @else
                            <span class="badge bg-secondary">📋 Other</span>
                        @endif
                    </td>
                    
                    {{-- Priority --}}
                    <td class="text-center">
                        @php $pri = $mr->priority; @endphp
                        @if($pri === 'urgent')
                            <span class="chip chip-red">🔴 Urgent</span>
                        @elseif($pri === 'high')
                            <span class="chip chip-orange">🟠 High</span>
                        @elseif($pri === 'medium')
                            <span class="chip chip-yellow">🟡 Medium</span>
                        @else
                            <span class="chip chip-green">🟢 Low</span>
                        @endif
                    </td>
                    
                    {{-- Status --}}
                    <td class="text-center">
                        @php 
                        $s = $mr->status;
                        $statusText = match($s) {
                            'pending' => 'Pending Review',
                            'approved_for_repair' => 'Approved',
                            'under_repair' => 'Under Repair',
                            'resolved' => 'Resolved',
                            'rejected' => 'Rejected',
                            default => ucfirst(str_replace('_', ' ', $s))
                        };
                        @endphp
                        @if($s === 'pending')
                            <span class="chip chip-yellow">{{ $statusText }}</span>
                        @elseif($s === 'approved_for_repair')
                            <span class="chip" style="background:#fef3c7;color:#92400e;border:1px solid #f59e0b;">{{ $statusText }}</span>
                        @elseif($s === 'under_repair')
                            <span class="chip chip-blue">{{ $statusText }}</span>
                        @elseif($s === 'resolved')
                            <span class="chip chip-green">{{ $statusText }}</span>
                        @else
                            <span class="chip chip-red">{{ $statusText }}</span>
                        @endif
                    </td>

                    {{-- Payment Responsibility --}}
                    <td class="text-center">
                        @if($mr->payment_responsibility)
                            <span class="badge {{ $mr->payment_responsibility === 'tenant' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}" style="font-size:0.8rem;">
                                {{ ucfirst($mr->payment_responsibility) }} Pays
                            </span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    
                    {{-- Submitted --}}
                    <td class="text-center text-muted small">
                        {{ $mr->created_at ? $mr->created_at->format('d M Y') : '—' }}<br>
                        <span style="font-size:.75rem;">{{ $mr->created_at ? $mr->created_at->format('H:i') : '' }}</span>
                    </td>

                    {{-- Actions --}}
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#complaintModal{{ $mr->id }}" title="View details & manage">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>

                {{-- Complaint Management Modal --}}
                <div class="modal fade" id="complaintModal{{ $mr->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                            <div class="modal-header border-0" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);flex-shrink:0;">
                                <div class="text-white">
                                    <h5 class="modal-title fw-bold mb-1">
                                        <i class="fas fa-clipboard-list me-2"></i>Maintenance Complaint
                                    </h5>
                                    <p class="mb-0 small opacity-75">{{ $mr->house->title ?? ('Property #' . $mr->house_id) }}</p>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" style="padding:1.5rem;overflow-y:auto;max-height:70vh;">
                                {{-- Complaint Summary --}}
                                <div class="p-3 rounded-3 mb-3" style="background:#f0f9ff;border:1px solid #bfdbfe;">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="small text-muted fw-semibold mb-1">Issue</div>
                                            <div class="fw-bold" style="color:#0284c7;">{{ ucfirst($mr->category) }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small text-muted fw-semibold mb-1">Priority</div>
                                            <span class="badge text-bg-dark" style="font-size:0.85rem;">{{ ucfirst($mr->priority) }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small text-muted fw-semibold mb-1">Status</div>
                                            <span class="badge text-bg-info" style="font-size:0.85rem;">{{ $statusText }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tenant & Property Info --}}
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">Tenant</label>
                                        <div class="p-2 rounded-2" style="background:#f8fafc;">
                                            <div class="fw-500">{{ $mr->tenant->name ?? '—' }}</div>
                                            <div class="small text-muted">{{ $mr->tenant->email ?? '—' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">Property Owner</label>
                                        <div class="p-2 rounded-2" style="background:#f8fafc;">
                                            <div class="fw-500">{{ $mr->owner->name ?? '—' }}</div>
                                            <div class="small text-muted">{{ $mr->owner->email ?? '—' }}</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Issue Description --}}
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Issue Description</label>
                                    <div class="p-3 rounded-2" style="background:#f8fafc;border-left:4px solid #0284c7;">
                                        <p class="mb-0 small" style="line-height:1.6;">{{ $mr->description }}</p>
                                    </div>
                                </div>

                                {{-- Admin Notes (if exists) --}}
                                @if($mr->admin_notes)
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Admin Notes</label>
                                    <div class="p-3 rounded-2" style="background:#faf5ff;border-left:4px solid #8b5cf6;">
                                        <p class="mb-0 small" style="line-height:1.6;color:#5b21b6;">{{ $mr->admin_notes }}</p>
                                    </div>
                                </div>
                                @endif

                                {{-- Inspection Notes (if exists) --}}
                                @if($mr->inspection_notes)
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Inspection Findings</label>
                                    <div class="p-3 rounded-2" style="background:#fffbeb;border-left:4px solid #f59e0b;">
                                        <p class="mb-0 small" style="line-height:1.6;color:#92400e;">{{ $mr->inspection_notes }}</p>
                                    </div>
                                </div>
                                @endif

                                {{-- Admin Actions --}}
                                <div class="border-top mt-4 pt-3">
                                    <label class="form-label small fw-semibold text-muted mb-3">
                                        <i class="fas fa-cogs me-1"></i>Admin Actions
                                    </label>

                                    @if($mr->status === 'pending')
                                        <div class="row g-2 mb-3">
                                            <div class="col-12">
                                                <p class="small text-muted mb-2">For obvious issues (broken socket, major leak, etc.), approve for immediate repair.</p>
                                                <button class="btn btn-sm btn-success w-100" data-bs-toggle="modal" data-bs-target="#approveModal{{ $mr->id }}">
                                                    <i class="fas fa-check-circle me-1"></i>Approve for Repair (No Inspection)
                                                </button>
                                            </div>
                                            <div class="col-12">
                                                <p class="small text-muted mb-2">If cause is unclear or responsibility needs verification, request inspection first.</p>
                                                <button class="btn btn-sm btn-warning w-100" data-bs-toggle="modal" data-bs-target="#inspectionModal{{ $mr->id }}">
                                                    <i class="fas fa-clipboard-check me-1"></i>Request Inspection First
                                                </button>
                                            </div>
                                        </div>
                                    @elseif($mr->status === 'approved_for_repair')
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <button class="btn btn-sm btn-info w-100" data-bs-toggle="modal" data-bs-target="#startRepairModal{{ $mr->id }}">
                                                    <i class="fas fa-wrench me-1"></i>Start Repair
                                                </button>
                                            </div>
                                        </div>
                                    @elseif($mr->status === 'under_repair')
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <button class="btn btn-sm btn-success w-100" data-bs-toggle="modal" data-bs-target="#completeRepairModal{{ $mr->id }}">
                                                    <i class="fas fa-check me-1"></i>Mark Resolved
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Payment Responsibility Display --}}
                                    @if($mr->payment_responsibility)
                                    <div class="alert alert-info mb-0">
                                        <strong>Payment Responsibility:</strong> {{ ucfirst($mr->payment_responsibility) }} pays for this repair.
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="modal-footer border-0" style="background:#f8fafc;">
                                <button type="button" class="btn btn-light fw-semibold" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Approve for Repair Modal --}}
                <div class="modal fade" id="approveModal{{ $mr->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <form action="{{ route('maintenance.admin.approve', $mr->id) }}" method="POST">
                                @csrf
                                <div class="modal-header border-0" style="background:#10b981;">
                                    <h5 class="modal-title text-white fw-bold">
                                        <i class="fas fa-check-circle me-2"></i>Approve for Repair
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Who pays for the repair?</label>
                                        <select name="payment_responsibility" class="form-select" required>
                                            <option value="">Select...</option>
                                            <option value="owner">Owner (Property fault, normal wear, old damage)</option>
                                            <option value="tenant">Tenant (Tenant-caused damage)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Admin Notes</label>
                                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Why this issue requires immediate repair..." required></textarea>
                                        <small class="text-muted">Visible issue, urgent, etc.</small>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-1"></i>Approve & Arrange Service
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Request Inspection Modal --}}
                <div class="modal fade" id="inspectionModal{{ $mr->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <form action="{{ route('maintenance.admin.request-inspection', $mr->id) }}" method="POST">
                                @csrf
                                <div class="modal-header border-0" style="background:#f59e0b;">
                                    <h5 class="modal-title text-white fw-bold">
                                        <i class="fas fa-clipboard-check me-2"></i>Request Inspection
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Why is inspection needed?</label>
                                        <select name="inspection_reason" class="form-select" required>
                                            <option value="">Select...</option>
                                            <option value="unclear_cause">Cause of damage is unclear</option>
                                            <option value="verify_responsibility">Payment responsibility needs confirmation</option>
                                            <option value="condition_assessment">Property condition assessment required</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Notes for Inspection Team</label>
                                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Details for inspection..." required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-search me-1"></i>Request Inspection
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Start Repair Modal --}}
                <div class="modal fade" id="startRepairModal{{ $mr->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <form action="{{ route('maintenance.admin.update-repair-status', $mr->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="under_repair">
                                <div class="modal-header border-0" style="background:#3b82f6;">
                                    <h5 class="modal-title text-white fw-bold">
                                        <i class="fas fa-wrench me-2"></i>Start Repair Work
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="text-muted">Mark this complaint as 'Under Repair' to indicate that repair work has started.</p>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-play me-1"></i>Start Repair
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Complete Repair Modal --}}
                <div class="modal fade" id="completeRepairModal{{ $mr->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <form action="{{ route('maintenance.admin.update-repair-status', $mr->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="resolved">
                                <div class="modal-header border-0" style="background:#10b981;">
                                    <h5 class="modal-title text-white fw-bold">
                                        <i class="fas fa-check-circle me-2"></i>Mark as Resolved
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="text-muted">Mark this complaint as resolved once repair work is completed.</p>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-1"></i>Mark Resolved
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="fas fa-wrench fa-2x mb-2 d-block opacity-25"></i>
                        No maintenance complaints found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($maintenanceRequests->hasPages())
    <div class="px-3 py-2 border-top">
        {{ $maintenanceRequests->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection
