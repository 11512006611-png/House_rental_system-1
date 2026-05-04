@extends('layouts.app')

@section('title', 'Maintenance Requests')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h3 class="mb-1"><i class="fas fa-screwdriver-wrench text-primary me-2"></i>Maintenance Requests</h3>
            <p class="text-muted mb-0">Report issues like water, electricity, and plumbing to your owner.</p>
        </div>
        <a href="{{ route('tenant.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body text-center">
                <div class="h4 mb-0">{{ $statusCounts['pending'] }}</div>
                <small class="text-muted">Pending</small>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body text-center">
                <div class="h4 mb-0" style="color:#f59e0b;">{{ $statusCounts['approved_for_repair'] ?? 0 }}</div>
                <small class="text-muted">Approved for Repair</small>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body text-center">
                <div class="h4 mb-0" style="color:#3b82f6;">{{ $statusCounts['under_repair'] ?? 0 }}</div>
                <small class="text-muted">Under Repair</small>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body text-center">
                <div class="h4 mb-0 text-success">{{ $statusCounts['resolved'] }}</div>
                <small class="text-muted">Resolved</small>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-plus-circle text-primary me-2"></i>Create New Request</h6>
                </div>
                <div class="card-body">
                    @if($activeRentals->isEmpty())
                        <div class="alert alert-info mb-0">You currently have no active rentals eligible for maintenance requests.</div>
                    @else
                        <form action="{{ route('tenant.maintenance.store') }}" method="POST" class="row g-3">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label">Property</label>
                                <select name="rental_id" class="form-select" required>
                                    <option value="">Select Property</option>
                                    @foreach($activeRentals as $rental)
                                        <option value="{{ $rental->id }}">{{ $rental->house->title ?? ('Property #' . $rental->house_id) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select</option>
                                    <option value="water">Water</option>
                                    <option value="electricity">Electricity</option>
                                    <option value="plumbing">Plumbing</option>
                                    <option value="security">Security</option>
                                    <option value="cleaning">Cleaning</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select" required>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Preferred Visit Date</label>
                                <input type="date" name="preferred_visit_date" class="form-control" min="{{ now()->toDateString() }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Issue Description</label>
                                <textarea name="description" class="form-control" rows="4" maxlength="1500" placeholder="Example: Water leakage from kitchen tap..." required></textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i>Submit Request
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-life-ring me-2 text-primary"></i>Emergency Contact</h5>
                    <p class="card-text">For urgent issues like water leakage, electrical faults, or security concerns, please contact the admin directly. The admin will arrange qualified technicians to attend and complete repairs.</p>
                    @if($admin && $admin->phone)
                        <a href="tel:{{ $admin->phone }}" class="btn btn-primary mb-3 d-inline-flex align-items-center">
                            <i class="fas fa-phone me-2"></i>Call Admin: {{ $admin->phone }}
                        </a>
                    @else
                        <p class="text-muted">Admin contact not available.</p>
                    @endif

                    <div class="mt-3 p-3 rounded-2" style="background:#fff7f0;border-left:4px solid #f59e0b;">
                        <h6 class="mb-2" style="font-size:0.95rem;">Emergency Maintenance Policy</h6>
                        <ul class="mb-0 small" style="line-height:1.6;">
                            <li>If electrical sockets or wiring are damaged due to tenant negligence or misuse, the tenant will be responsible for repair and replacement costs.</li>
                            <li>For water leaks and plumbing issues, the admin will coordinate repairs; any charges assessed will be communicated by the admin and billed accordingly.</li>
                            <li>If you need immediate assistance while occupying the property, contact the admin and they will dispatch qualified repair personnel.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0"><i class="fas fa-list-check text-primary me-2"></i>My Requests</h6>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Property</th>
                        <th>Issue</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Responsibility</th>
                        <th>Created</th>
                        <th style="width: 60px;">View</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $requestItem)
                        @php
                            $statusClass = match($requestItem->status) {
                                'pending' => 'warning',
                                'approved_for_repair' => 'warning',
                                'under_repair' => 'info',
                                'resolved' => 'success',
                                'rejected' => 'danger',
                                default => 'secondary'
                            };
                            $statusText = match($requestItem->status) {
                                'pending' => 'Pending Review',
                                'approved_for_repair' => 'Approved for Repair',
                                'under_repair' => 'Under Repair',
                                'resolved' => 'Resolved',
                                'rejected' => 'Rejected',
                                default => ucfirst(str_replace('_', ' ', $requestItem->status))
                            };
                        @endphp
                        <tr>
                            <td>{{ $requestItem->house->title ?? ('Property #' . $requestItem->house_id) }}</td>
                            <td>
                                <div class="fw-semibold">{{ ucfirst($requestItem->category) }}</div>
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($requestItem->description, 70) }}</small>
                            </td>
                            <td><span class="badge bg-dark-subtle text-dark">{{ ucfirst($requestItem->priority) }}</span></td>
                            <td>
                                <span class="badge text-bg-{{ $statusClass }}">{{ $statusText }}</span>
                                @if($requestItem->needs_inspection)
                                    <div><span class="badge bg-info-subtle text-info mt-1">Inspection Required</span></div>
                                @endif
                            </td>
                            <td>
                                @if($requestItem->payment_responsibility)
                                    <span class="badge {{ $requestItem->payment_responsibility === 'tenant' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                                        {{ ucfirst($requestItem->payment_responsibility) }} Pays
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>{{ $requestItem->created_at->format('d M Y') }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#maintenanceDetailModal{{ $requestItem->id }}">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </td>
                        </tr>

                        {{-- Maintenance Detail Modal --}}
                        <div class="modal fade" id="maintenanceDetailModal{{ $requestItem->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                                    <div class="modal-header border-0" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);flex-shrink:0;">
                                        <div class="text-white">
                                            <h5 class="modal-title fw-bold mb-1">
                                                <i class="fas fa-screwdriver-wrench me-2"></i>Maintenance Request Details
                                            </h5>
                                            <p class="mb-0 small opacity-75">{{ $requestItem->house->title ?? ('Property #' . $requestItem->house_id) }}</p>
                                        </div>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body" style="padding:1.5rem;overflow-y:auto;max-height:70vh;">
                                        {{-- Request Summary --}}
                                        <div class="p-3 rounded-3 mb-3" style="background:#f0f9ff;border:1px solid #bfdbfe;">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="small text-muted fw-semibold mb-1">Category</div>
                                                    <div class="fw-bold" style="color:#0284c7;">{{ ucfirst($requestItem->category) }}</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted fw-semibold mb-1">Priority</div>
                                                    <span class="badge text-bg-dark" style="font-size:0.85rem;">{{ ucfirst($requestItem->priority) }}</span>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted fw-semibold mb-1">Current Status</div>
                                                    <span class="badge text-bg-{{ $statusClass }}" style="font-size:0.85rem;">{{ $statusText }}</span>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted fw-semibold mb-1">Payment Responsibility</div>
                                                    @if($requestItem->payment_responsibility)
                                                        <span class="badge {{ $requestItem->payment_responsibility === 'tenant' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}" style="font-size:0.85rem;">
                                                            {{ ucfirst($requestItem->payment_responsibility) }} Pays
                                                        </span>
                                                    @else
                                                        <span class="text-muted" style="font-size:0.85rem;">Pending</span>
                                                    @endif
                                                </div>
                                                @if($requestItem->needs_inspection)
                                                    <div class="col-12">
                                                        <div class="small text-muted fw-semibold mb-1">Inspection Status</div>
                                                        <span class="badge bg-info" style="font-size:0.85rem;">Inspection Required</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Issue Description --}}
                                        <div class="mb-3">
                                            <div class="small text-uppercase fw-semibold mb-2" style="letter-spacing:.05em;color:#64748b;">
                                                <i class="fas fa-file-text me-1" style="color:#0284c7;"></i>Issue Description
                                            </div>
                                            <div class="p-3 rounded-2" style="background:#f8fafc;border-left:4px solid #0284c7;">
                                                <p class="mb-0 small" style="line-height:1.6;">{{ $requestItem->description }}</p>
                                            </div>
                                        </div>

                                        {{-- Admin Notes --}}
                                        @if($requestItem->admin_notes)
                                        <div class="mb-3">
                                            <div class="small text-uppercase fw-semibold mb-2" style="letter-spacing:.05em;color:#64748b;">
                                                <i class="fas fa-note-sticky me-1" style="color:#8b5cf6;"></i>Admin Notes
                                            </div>
                                            <div class="p-3 rounded-2" style="background:#faf5ff;border-left:4px solid #8b5cf6;">
                                                <p class="mb-0 small" style="line-height:1.6;color:#5b21b6;">{{ $requestItem->admin_notes }}</p>
                                            </div>
                                        </div>
                                        @endif

                                        {{-- Inspection Notes --}}
                                        @if($requestItem->inspection_notes)
                                        <div class="mb-3">
                                            <div class="small text-uppercase fw-semibold mb-2" style="letter-spacing:.05em;color:#64748b;">
                                                <i class="fas fa-magnifying-glass me-1" style="color:#f59e0b;"></i>Inspection Findings
                                            </div>
                                            <div class="p-3 rounded-2" style="background:#fffbeb;border-left:4px solid #f59e0b;">
                                                <p class="mb-0 small" style="line-height:1.6;color:#92400e;">{{ $requestItem->inspection_notes }}</p>
                                            </div>
                                        </div>
                                        @endif

                                        {{-- Timeline Section --}}
                                        <div class="border-top my-4 pt-3">
                                            <div class="small text-uppercase fw-semibold mb-3" style="letter-spacing:.05em;color:#64748b;">
                                                <i class="fas fa-timeline me-1" style="color:#0284c7;"></i>Timeline
                                            </div>
                                            <div style="position:relative;padding-left:30px;">
                                                {{-- Request Submitted --}}
                                                <div style="position:relative;margin-bottom:2rem;">
                                                    <div style="position:absolute;left:-28px;top:4px;width:20px;height:20px;border-radius:50%;background:#0284c7;border:3px solid white;box-shadow:0 0 0 2px #0284c7;"></div>
                                                    <div class="small fw-semibold" style="color:#0284c7;">Complaint Submitted</div>
                                                    <div class="text-muted small mt-1">
                                                        <i class="fas fa-calendar me-1"></i>{{ $requestItem->created_at->format('d M Y') }}
                                                        <span class="ms-2"><i class="fas fa-clock me-1"></i>{{ $requestItem->created_at->format('h:i A') }}</span>
                                                    </div>
                                                </div>

                                                {{-- Approved for Repair (if approved) --}}
                                                @if($requestItem->status === 'approved_for_repair' && $requestItem->approved_for_repair_at)
                                                    <div style="position:relative;margin-bottom:2rem;">
                                                        <div style="position:absolute;left:-28px;top:4px;width:20px;height:20px;border-radius:50%;background:#f59e0b;border:3px solid white;box-shadow:0 0 0 2px #f59e0b;"></div>
                                                        <div class="small fw-semibold" style="color:#f59e0b;">Approved for Repair</div>
                                                        <div class="text-muted small mt-1">
                                                            <i class="fas fa-calendar me-1"></i>{{ $requestItem->approved_for_repair_at->format('d M Y') }}
                                                            <span class="ms-2"><i class="fas fa-clock me-1"></i>{{ $requestItem->approved_for_repair_at->format('h:i A') }}</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Under Repair (if under repair or resolved) --}}
                                                @if(in_array($requestItem->status, ['under_repair', 'resolved']) && $requestItem->under_repair_at)
                                                    <div style="position:relative;margin-bottom:2rem;">
                                                        <div style="position:absolute;left:-28px;top:4px;width:20px;height:20px;border-radius:50%;background:#3b82f6;border:3px solid white;box-shadow:0 0 0 2px #3b82f6;"></div>
                                                        <div class="small fw-semibold" style="color:#3b82f6;">Repair Work Started</div>
                                                        <div class="text-muted small mt-1">
                                                            <i class="fas fa-calendar me-1"></i>{{ $requestItem->under_repair_at->format('d M Y') }}
                                                            <span class="ms-2"><i class="fas fa-clock me-1"></i>{{ $requestItem->under_repair_at->format('h:i A') }}</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Resolved (if exists) --}}
                                                @if($requestItem->status === 'resolved' && $requestItem->resolved_at)
                                                    <div style="position:relative;">
                                                        <div style="position:absolute;left:-28px;top:4px;width:20px;height:20px;border-radius:50%;background:#10b981;border:3px solid white;box-shadow:0 0 0 2px #10b981;"></div>
                                                        <div class="small fw-semibold text-success">Resolved</div>
                                                        <div class="text-muted small mt-1">
                                                            <i class="fas fa-calendar me-1"></i>{{ $requestItem->resolved_at->format('d M Y') }}
                                                            <span class="ms-2"><i class="fas fa-clock me-1"></i>{{ $requestItem->resolved_at->format('h:i A') }}</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Rejected (if rejected) --}}
                                                @if($requestItem->status === 'rejected')
                                                    <div style="position:relative;">
                                                        <div style="position:absolute;left:-28px;top:4px;width:20px;height:20px;border-radius:50%;background:#ef4444;border:3px solid white;box-shadow:0 0 0 2px #ef4444;"></div>
                                                        <div class="small fw-semibold" style="color:#ef4444;">Request Rejected</div>
                                                        <div class="text-muted small mt-1">
                                                            <i class="fas fa-calendar me-1"></i>{{ $requestItem->updated_at->format('d M Y') }}
                                                            <span class="ms-2"><i class="fas fa-clock me-1"></i>{{ $requestItem->updated_at->format('h:i A') }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Status Summary --}}
                                        <div class="alert alert-info d-flex gap-2 mt-3" style="border-radius:12px;background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;">
                                            <i class="fas fa-info-circle mt-1 flex-shrink-0"></i>
                                            <div class="small">
                                                <strong>Last Updated:</strong> {{ $requestItem->updated_at->format('d M Y \a\t h:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0" style="background:#f8fafc;">
                                        <button type="button" class="btn btn-light fw-semibold" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No maintenance requests submitted yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="card-footer bg-white">{{ $requests->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</div>
@endsection
