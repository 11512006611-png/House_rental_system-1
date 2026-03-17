@extends('layouts.app')

@section('title', 'Inspection Requests')

@push('styles')
<style>
.inspections-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    color: white;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.inspections-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
}

.inspections-header p {
    margin: 0;
    opacity: 0.9;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-top: 1.5rem;
}

.stat-box {
    background: rgba(255, 255, 255, 0.15);
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
}

.stat-box .number {
    font-size: 1.5rem;
    font-weight: 700;
}

.stat-box .label {
    font-size: 0.85rem;
    opacity: 0.9;
    margin-top: 0.25rem;
}

.inspection-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    transition: box-shadow 0.2s;
}

.inspection-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.inspection-content {
    flex: 1;
}

.inspection-tenant {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.tenant-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.tenant-info h3 {
    margin: 0;
    color: #1e293b;
}

.tenant-info p {
    margin: 0.25rem 0 0 0;
    color: #64748b;
    font-size: 0.9rem;
}

.inspection-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.detail-item {
    font-size: 0.9rem;
}

.detail-label {
    color: #64748b;
    font-weight: 600;
    display: block;
    margin-bottom: 0.25rem;
}

.detail-value {
    color: #1e293b;
}

.status-badge {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-approved {
    background: #dcfce7;
    color: #166534;
}

.status-rejected {
    background: #fee2e2;
    color: #7f1d1d;
}

.status-completed {
    background: #d1fae5;
    color: #065f46;
}

.inspection-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-left: 1rem;
}

.btn-action {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn-approve {
    background: #dcfce7;
    color: #166534;
}

.btn-approve:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.btn-reject {
    background: #fee2e2;
    color: #7f1d1d;
}

.btn-reject:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
}

.btn-complete {
    background: #d1fae5;
    color: #065f46;
}

.btn-complete:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.modal-header {
    margin-bottom: 1.5rem;
}

.modal-header h2 {
    margin: 0;
    color: #1e293b;
}

.close-modal {
    color: #64748b;
    float: right;
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
}

.close-modal:hover {
    color: #334155;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #334155;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-family: inherit;
    font-size: 0.95rem;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-submit {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #64748b;
}

.empty-state-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}
</style>
@endpush

@section('content')
<div style="max-width: 1100px; margin: 0 auto; padding: 1rem;">
    <div class="inspections-header">
        <h1>🔍 Inspection Requests</h1>
        <p>Manage inspection requests from tenants</p>
        <div class="stats-row">
            <div class="stat-box">
                <div class="number">{{ $pendingCount }}</div>
                <div class="label">Pending</div>
            </div>
            <div class="stat-box">
                <div class="number">{{ $approvedCount }}</div>
                <div class="label">Approved</div>
            </div>
            <div class="stat-box">
                <div class="number">{{ $completedCount }}</div>
                <div class="label">Completed</div>
            </div>
        </div>
    </div>

    @if ($inspections->count() > 0)
        @foreach ($inspections as $inspection)
            <div class="inspection-card">
                <div class="inspection-content">
                    <div class="inspection-tenant">
                        <div class="tenant-avatar">{{ strtoupper(substr($inspection->tenant->name, 0, 1)) }}</div>
                        <div class="tenant-info">
                            <h3>{{ $inspection->tenant->name }}</h3>
                            <p>{{ $inspection->tenant->email }}</p>
                        </div>
                    </div>

                    <div class="inspection-details">
                        <div class="detail-item">
                            <span class="detail-label">Property</span>
                            <div class="detail-value">{{ $inspection->house->title }}</div>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Preferred Date</span>
                            <div class="detail-value">{{ $inspection->preferred_date->format('d M Y') }}</div>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Preferred Time</span>
                            <div class="detail-value">{{ $inspection->preferred_time }}</div>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status</span>
                            <div class="detail-value">
                                <span class="status-badge status-{{ $inspection->status }}">{{ ucfirst($inspection->status) }}</span>
                            </div>
                        </div>
                    </div>

                    @if ($inspection->message)
                        <div style="margin-top: 0.75rem; padding: 0.75rem; background: #f8fafc; border-radius: 6px; border-left: 3px solid #667eea;">
                            <strong style="color: #334155;">Tenant's Message:</strong>
                            <p style="margin: 0.5rem 0 0 0; color: #64748b;">{{ $inspection->message }}</p>
                        </div>
                    @endif

                    @if ($inspection->owner_notes)
                        <div style="margin-top: 0.75rem; padding: 0.75rem; background: #f0f9ff; border-radius: 6px; border-left: 3px solid #0284c7;">
                            <strong style="color: #0c4a6e;">Your Notes:</strong>
                            <p style="margin: 0.5rem 0 0 0; color: #0c4a6e;">{{ $inspection->owner_notes }}</p>
                        </div>
                    @endif
                </div>

                <div class="inspection-actions">
                    @if ($inspection->status === 'pending')
                        <button class="btn-action btn-approve" onclick="openApproveModal({{ $inspection->id }}, '{{ $inspection->house->title }}')">
                            ✓ Approve
                        </button>
                        <button class="btn-action btn-reject" onclick="openRejectModal({{ $inspection->id }})">
                            ✕ Reject
                        </button>
                    @elseif ($inspection->status === 'approved')
                        <button class="btn-action btn-complete" onclick="completeInspection({{ $inspection->id }})">
                            ✓ Mark Complete
                        </button>
                    @endif
                </div>
            </div>
        @endforeach

        <div style="display: flex; justify-content: center; margin-top: 2rem;">
            {{ $inspections->links() }}
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <h3>No inspection requests yet</h3>
            <p>Tenants will request inspections for your properties here</p>
        </div>
    @endif
</div>

<!-- Approve Modal -->
<div id="approveModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeApproveModal()">&times;</span>
        <div class="modal-header">
            <h2>✓ Approve Inspection</h2>
        </div>
        <form method="POST" id="approveForm">
            @csrf
            <div class="form-group">
                <label for="house_name">Property</label>
                <input type="text" id="house_name" readonly value="">
            </div>
            <div class="form-group">
                <label for="scheduled_at">Scheduled Date & Time *</label>
                <input type="datetime-local" id="scheduled_at" name="scheduled_at" required>
            </div>
            <div class="form-group">
                <label for="owner_notes">Your Notes (optional)</label>
                <textarea id="owner_notes" name="owner_notes" rows="3" placeholder="Any additional instructions for the tenant..."></textarea>
            </div>
            <button type="submit" class="btn-submit">✓ Approve & Notify Tenant</button>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeRejectModal()">&times;</span>
        <div class="modal-header">
            <h2>✕ Reject Inspection</h2>
        </div>
        <form method="POST" id="rejectForm">
            @csrf
            <div class="form-group">
                <label for="reject_notes">Reason for Rejection *</label>
                <textarea id="reject_notes" name="owner_notes" rows="3" placeholder="Explain why you're rejecting this inspection..." required></textarea>
            </div>
            <button type="submit" class="btn-submit">✕ Reject & Notify Tenant</button>
        </form>
    </div>
</div>

<script>
function openApproveModal(inspectionId, houseName) {
    document.getElementById('house_name').value = houseName;
    document.getElementById('approveForm').action = `/owner/inspections/${inspectionId}/approve`;
    document.getElementById('approveModal').style.display = 'block';
    
    // Set minimum date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('scheduled_at').min = tomorrow.toISOString().slice(0, 16);
}

function closeApproveModal() {
    document.getElementById('approveModal').style.display = 'none';
}

function openRejectModal(inspectionId) {
    document.getElementById('rejectForm').action = `/owner/inspections/${inspectionId}/reject`;
    document.getElementById('rejectModal').style.display = 'block';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

function completeInspection(inspectionId) {
    if (confirm('Mark this inspection as completed?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/owner/inspections/${inspectionId}/complete`;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const approveModal = document.getElementById('approveModal');
    const rejectModal = document.getElementById('rejectModal');
    if (event.target === approveModal) {
        closeApproveModal();
    }
    if (event.target === rejectModal) {
        closeRejectModal();
    }
};
</script>
    @if(request('status'))
        <a href="{{ route('owner.inspections') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
    @endif
</form>

{{-- List --}}
@if($inspections->isEmpty())
    <div class="text-center py-5">
        <i class="fas fa-clipboard-check fs-1 text-muted mb-3"></i>
        <p class="text-muted">No inspection requests found.</p>
    </div>
@else
    <div class="d-flex flex-column gap-3">
        @foreach($inspections as $insp)
            @php
                $iColor = match($insp->status) {
                    'pending'   => ['bg'=>'#fff7ed','color'=>'#d97706','icon'=>'fa-hourglass-half'],
                    'approved'  => ['bg'=>'#f0fdf4','color'=>'#059669','icon'=>'fa-check-circle'],
                    'rejected'  => ['bg'=>'#fef2f2','color'=>'#dc2626','icon'=>'fa-times-circle'],
                    'completed' => ['bg'=>'#eff6ff','color'=>'#2563eb','icon'=>'fa-flag-checkered'],
                    default     => ['bg'=>'#f1f5f9','color'=>'#64748b','icon'=>'fa-circle'],
                };
            @endphp
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-start gap-3">
                        <div style="width:48px;height:48px;border-radius:12px;background:{{ $iColor['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas {{ $iColor['icon'] }}" style="color:{{ $iColor['color'] }};font-size:1.1rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                <div>
                                    <h6 class="fw-bold mb-0">{{ $insp->house->title ?? 'Property' }}</h6>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-user me-1"></i>{{ $insp->tenant->name ?? 'Tenant' }}
                                        &nbsp;·&nbsp;
                                        <i class="fas fa-envelope me-1"></i>{{ $insp->tenant->email ?? '' }}
                                    </p>
                                </div>
                                <span class="badge rounded-pill px-3 py-2"
                                      style="background:{{ $iColor['bg'] }};color:{{ $iColor['color'] }};">
                                    {{ $insp->statusLabel() }}
                                </span>
                            </div>

                            <div class="d-flex flex-wrap gap-3 small text-muted my-2">
                                <span><i class="fas fa-calendar me-1"></i>{{ $insp->preferred_date->format('d M Y') }}</span>
                                <span><i class="fas fa-clock me-1"></i>{{ $insp->preferred_time }}</span>
                                @if($insp->scheduled_at)
                                    <span class="text-success fw-semibold">
                                        <i class="fas fa-calendar-check me-1"></i>Confirmed: {{ $insp->scheduled_at->format('d M Y, g:i A') }}
                                    </span>
                                @endif
                                <span><i class="fas fa-clock me-1"></i>Submitted {{ $insp->created_at->diffForHumans() }}</span>
                            </div>

                            @if($insp->message)
                                <div class="p-2 rounded-2 small mb-2" style="background:#f8fafc;border-left:3px solid #8b5cf6;">
                                    <span class="fw-semibold">Tenant message:</span> {{ $insp->message }}
                                </div>
                            @endif

                            {{-- Actions for pending --}}
                            @if($insp->status === 'pending')
                                <form class="d-flex flex-wrap align-items-end gap-2 mt-2"
                                      action="{{ route('owner.inspections.approve', $insp) }}" method="POST">
                                    @csrf
                                    <div>
                                        <label class="form-label small fw-semibold mb-1">Confirm Date/Time (optional)</label>
                                        <input type="datetime-local" name="scheduled_at" class="form-control form-control-sm" style="width:220px;">
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label small fw-semibold mb-1">Note to tenant (optional)</label>
                                        <input type="text" name="owner_notes" class="form-control form-control-sm" placeholder="e.g. I'll meet you at the gate." maxlength="500">
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-check me-1"></i>Approve
                                    </button>
                                </form>
                                <form action="{{ route('owner.inspections.reject', $insp) }}" method="POST" class="d-flex gap-2 mt-2">
                                    @csrf
                                    <input type="text" name="owner_notes" class="form-control form-control-sm" placeholder="Reason for rejection (optional)" maxlength="500">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Decline this inspection request?')">
                                        <i class="fas fa-times me-1"></i>Reject
                                    </button>
                                </form>
                            @endif

                            {{-- Mark completed for approved --}}
                            @if($insp->status === 'approved')
                                <form action="{{ route('owner.inspections.complete', $insp) }}" method="POST" class="mt-2">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-flag-checkered me-1"></i>Mark as Completed
                                    </button>
                                </form>
                            @endif

                            @if($insp->owner_notes && $insp->status !== 'pending')
                                <div class="mt-2 p-2 rounded-2 small" style="background:#f1f5f9;border-left:3px solid #8b5cf6;">
                                    <span class="fw-semibold">Your note:</span> {{ $insp->owner_notes }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">{{ $inspections->links() }}</div>
@endif

@endsection
