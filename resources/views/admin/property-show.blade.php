@extends('layouts.admin')
@section('title','Property Details')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.properties') }}">Properties</a></li>
<li class="breadcrumb-item active">Property Details</li>
@endsection

@section('content')
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
    <div class="page-header mb-0">
        <h1><i class="fas fa-building me-2 text-primary"></i>{{ $house->title }}</h1>
        <p>Full property information and image gallery for admin review.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.properties') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
        <a href="{{ route('houses.edit', ['house' => $house, 'from' => 'admin-property-show']) }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-pen me-1"></i>Edit As Admin
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="admin-card mb-3">
            <div class="admin-card-header">
                <h6><i class="fas fa-image me-2"></i>Main Image</h6>
            </div>
            <div class="p-3">
                <img src="{{ $house->image_url }}" alt="{{ $house->title }}"
                     class="img-fluid rounded border" style="width:100%;max-height:430px;object-fit:cover;">

                <form action="{{ route('admin.properties.image.update', $house->id) }}" method="POST" enctype="multipart/form-data" class="mt-3">
                    @csrf
                    <label class="form-label fw-semibold">Change Main Photo</label>
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*" required>
                    <div class="form-text">Accepted: JPG, JPEG, PNG, WEBP. Max 5MB.</div>
                    @error('image')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <button type="submit" class="btn btn-sm btn-primary mt-2">
                        <i class="fas fa-upload me-1"></i>Update Photo
                    </button>
                </form>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="fas fa-images me-2"></i>Gallery Images ({{ $house->houseImages->count() }})</h6>
            </div>
            <div class="p-3">
                @if($house->houseImages->isNotEmpty())
                <div class="row g-2">
                    @foreach($house->houseImages as $img)
                    <div class="col-6 col-md-4 col-xl-3">
                        <a href="{{ $img->url }}" target="_blank" class="text-decoration-none">
                            <img src="{{ $img->url }}" alt="Property image {{ $loop->iteration }}"
                                 class="img-fluid rounded border" style="width:100%;height:120px;object-fit:cover;">
                        </a>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-muted">No gallery images uploaded for this property.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="admin-card mb-3">
            <div class="admin-card-header">
                <h6><i class="fas fa-circle-info me-2"></i>Property Info</h6>
            </div>
            <div class="p-3 small">
                <div class="mb-2"><strong>Status:</strong>
                    @if($house->status === 'available')
                        <span class="chip chip-green">Available</span>
                    @elseif($house->status === 'rented')
                        <span class="chip chip-blue">Rented</span>
                    @elseif($house->status === 'pending')
                        <span class="chip chip-yellow">Pending</span>
                    @elseif($house->status === 'rejected')
                        <span class="chip chip-red">Rejected</span>
                    @else
                        <span class="chip chip-gray">{{ $house->status }}</span>
                    @endif
                </div>
                <div class="mb-2"><strong>Admin Commission:</strong> {{ $house->admin_commission_rate !== null ? number_format((float) $house->admin_commission_rate, 2) . '%' : 'Not set yet' }}</div>
                <div class="mb-2"><strong>Inspection Scheduled:</strong> {{ $house->inspection_scheduled_at ? $house->inspection_scheduled_at->format('d M Y, h:i A') : 'Not scheduled yet' }}</div>
                <div class="mb-2"><strong>Last Inspected:</strong> {{ $house->inspected_at ? \Carbon\Carbon::parse($house->inspected_at)->format('d M Y, h:i A') : 'Not inspected yet' }}</div>
                <div class="mb-2"><strong>Inspected By:</strong> {{ $house->inspectedByAdmin->name ?? 'N/A' }}</div>
                <div class="mb-2"><strong>Price:</strong> Nu. {{ number_format($house->price, 0) }} / month</div>
                <div class="mb-2"><strong>Type:</strong> {{ ucfirst($house->type ?? 'N/A') }}</div>
                <div class="mb-2"><strong>Bedrooms:</strong> {{ $house->bedrooms ?? 'N/A' }}</div>
                <div class="mb-2"><strong>Bathrooms:</strong> {{ $house->bathrooms ?? 'N/A' }}</div>
                <div class="mb-2"><strong>Area:</strong> {{ $house->area ?? 'N/A' }}</div>
                <div class="mb-2"><strong>Location:</strong> {{ $house->locationModel->name ?? $house->location ?? 'N/A' }}</div>
                <div class="mb-0"><strong>Address:</strong> {{ $house->address ?? 'N/A' }}</div>
            </div>
        </div>

        @if(in_array($house->status, ['pending', 'rejected'], true))
        <div class="admin-card mb-3">
            <div class="admin-card-header">
                <h6><i class="fas fa-clipboard-check me-2"></i>Admin Inspection Decision</h6>
            </div>
            <div class="p-3 small">
                @if($errors->any())
                <div class="alert alert-danger py-2 small mb-3">
                    <div class="fw-semibold mb-1">Please fix the following before submitting:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('admin.properties.schedule-inspection', $house->id) }}" method="POST" class="mb-4">
                    @csrf
                    <label class="form-label fw-semibold">Set Inspection Date &amp; Time (Required Before Decision)</label>
                    <input type="datetime-local" name="inspection_scheduled_at" class="form-control mb-2" value="{{ old('inspection_scheduled_at', $house->inspection_scheduled_at ? $house->inspection_scheduled_at->format('Y-m-d\\TH:i') : '') }}" required>
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-calendar-check me-1"></i>Save Inspection Schedule
                    </button>
                </form>

                <form action="{{ route('admin.properties.approve', $house->id) }}" method="POST" class="mb-3">
                    @csrf
                    <label class="form-label fw-semibold">Admin Commission Rate (%)</label>
                    <input type="number" name="admin_commission_rate" min="0" max="100" step="0.01" class="form-control mb-2" value="{{ old('admin_commission_rate', $house->admin_commission_rate ?? 10) }}" required>
                    <label class="form-label fw-semibold">Inspection Notes (Required)</label>
                    <textarea name="admin_inspection_notes" rows="4" class="form-control mb-2 @error('admin_inspection_notes') is-invalid @enderror" placeholder="Write what you verified on-site and why this listing is accurate..." required>{{ old('admin_inspection_notes', $house->admin_inspection_notes) }}</textarea>
                    <div class="form-text mb-2">Minimum 3 characters.</div>
                    @error('admin_inspection_notes')
                    <div class="invalid-feedback d-block mb-2">{{ $message }}</div>
                    @enderror

                    <div class="form-check mb-2">
                        <input class="form-check-input @error('inspection_confirmed') is-invalid @enderror" type="checkbox" name="inspection_confirmed" id="inspection_confirmed" value="1" required {{ old('inspection_confirmed') ? 'checked' : '' }}>
                        <label class="form-check-label" for="inspection_confirmed">
                            I inspected this property and confirm the posted details are true.
                        </label>
                    </div>
                    @error('inspection_confirmed')
                    <div class="invalid-feedback d-block mb-2">{{ $message }}</div>
                    @enderror

                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Publish this property after inspection?')">
                        <i class="fas fa-check me-1"></i>Approve And Publish
                    </button>
                </form>

                <form action="{{ route('admin.properties.reject', $house->id) }}" method="POST" class="m-0">
                    @csrf
                    <label class="form-label fw-semibold">Rejection Reason (Required)</label>
                    <textarea name="admin_inspection_notes" rows="3" class="form-control mb-2 @error('admin_inspection_notes') is-invalid @enderror" placeholder="Explain why this listing failed inspection..." required>{{ old('admin_inspection_notes') }}</textarea>
                    <div class="form-text mb-2">Minimum 3 characters.</div>
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject this property after inspection?')">
                        <i class="fas fa-times me-1"></i>Reject After Inspection
                    </button>
                </form>
            </div>
        </div>
        @endif

        @if($house->status === 'available')
        <div class="admin-card mb-3">
            <div class="admin-card-header">
                <h6><i class="fas fa-upload me-2"></i>Update Published Listing</h6>
            </div>
            <div class="p-3 small">
                <p class="mb-2 text-muted">Need to change details or upload new images after inspection?</p>
                <a href="{{ route('houses.edit', ['house' => $house, 'from' => 'admin-property-show']) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-pen me-1"></i>Edit &amp; Upload (Keep Published)
                </a>
            </div>
        </div>
        @endif

        <div class="admin-card mb-3">
            <div class="admin-card-header">
                <h6><i class="fas fa-user-tie me-2"></i>Owner</h6>
            </div>
            <div class="p-3 small">
                <div class="mb-2"><strong>Name:</strong> {{ $house->owner->name ?? 'N/A' }}</div>
                <div class="mb-2"><strong>Email:</strong> {{ $house->owner->email ?? 'N/A' }}</div>
                <div class="mb-0"><strong>Phone:</strong> {{ $house->owner->phone ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h6><i class="fas fa-align-left me-2"></i>Description</h6>
            </div>
            <div class="p-3 small" style="white-space:pre-line;">
                {{ $house->description ?: 'No description provided.' }}
            </div>
        </div>

        <div class="admin-card mt-3">
            <div class="admin-card-header d-flex align-items-center justify-content-between gap-2">
                <h6><i class="fas fa-file-contract me-2"></i>Lease Agreement</h6>
                <span class="chip chip-blue">Admin Upload</span>
            </div>
            <div class="p-3 small">
                @php
                    $activeLeaseRental = $house->rentals->firstWhere('status', 'active');
                    $leaseRental = $activeLeaseRental ?: $house->rentals->first();
                    $canUploadLease = $leaseRental
                        && $leaseRental->status === 'active'
                        && ! $leaseRental->leaseAgreement
                        && (
                            $leaseRental->lease_status === 'requested'
                            || ($leaseRental->stay_decision === 'yes' && in_array($leaseRental->lease_status, [null, '', 'not_requested'], true))
                        );
                @endphp

                @if($leaseRental)
                    <div class="mb-3">
                        <div class="mb-2"><strong>Tenant:</strong> {{ $leaseRental->tenant->name ?? 'N/A' }}</div>
                        <div class="mb-2"><strong>Stay Decision:</strong> {{ ucfirst((string) ($leaseRental->stay_decision ?? 'pending')) }}</div>
                        <div class="mb-2"><strong>Lease Status:</strong> {{ ucfirst((string) ($leaseRental->lease_status ?? 'not_requested')) }}</div>
                    </div>

                    @if($leaseRental->leaseAgreement)
                        <a href="{{ route('rentals.lease.download', $leaseRental->leaseAgreement) }}" class="btn btn-sm btn-outline-primary mb-2">
                            <i class="fas fa-download me-1"></i>Open Lease Agreement
                        </a>
                        <div class="text-muted small">The lease agreement has already been uploaded for this tenant.</div>
                    @elseif($canUploadLease)
                        <button type="button" id="leaseUploadTrigger-{{ $leaseRental->id }}" class="btn btn-sm btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#leaseUploadModal-property-{{ $leaseRental->id }}">
                            <i class="fas fa-upload me-1"></i>Lease Agreement
                        </button>
                        <div class="text-muted small">Click the button to upload the PDF lease agreement for this tenant.</div>
                    @else
                        <div class="text-muted">No lease upload is available yet for this property.</div>
                    @endif
                @else
                    <div class="text-muted">No rental record is available for this property yet.</div>
                @endif
            </div>
        </div>

        <div class="admin-card mt-3">
            <div class="admin-card-header">
                <h6><i class="fas fa-file-signature me-2"></i>Latest Inspection Notes</h6>
            </div>
            <div class="p-3 small" style="white-space:pre-line;">
                {{ $house->admin_inspection_notes ?: 'No inspection notes recorded yet.' }}
            </div>
        </div>

        @if($canUploadLease)
        <div class="modal fade" id="leaseUploadModal-property-{{ $leaseRental->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold">Lease Agreement Upload</h5>
                            <div class="text-muted small">{{ $leaseRental->tenant->name ?? 'Tenant' }} · {{ $house->title }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('admin.rentals.lease.upload', $leaseRental) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body pt-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Lease Agreement PDF</label>
                                    <input type="file" name="lease_file" class="form-control" accept="application/pdf,.pdf" required>
                                    <div class="form-text">PDF only.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Rent Amount</label>
                                    <input type="number" step="0.01" min="1" name="monthly_rent" class="form-control" value="{{ old('monthly_rent', (float) ($leaseRental->monthly_rent ?? $house->price ?? 0)) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Security Deposit</label>
                                    <input type="number" step="0.01" min="0" name="security_deposit_amount" class="form-control" value="{{ old('security_deposit_amount', (float) ($house->security_deposit_amount ?? 0)) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Lease Duration (months)</label>
                                    <input type="number" min="1" max="60" name="duration_months" class="form-control" value="{{ old('duration_months', 12) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Lease Start Date</label>
                                    <input type="date" name="lease_start_date" class="form-control" value="{{ old('lease_start_date', optional($leaseRental->rental_date)->format('Y-m-d') ?? now()->toDateString()) }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i>Upload Lease
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@if(request()->boolean('lease_upload') && $canUploadLease)
<script>
document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('leaseUploadTrigger-{{ $leaseRental->id }}');
    if (trigger) {
        trigger.click();
    }
});
</script>
@endif
@endsection
