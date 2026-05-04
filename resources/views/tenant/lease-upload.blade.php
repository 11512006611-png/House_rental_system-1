@extends('layouts.app')

@section('title', 'Upload Lease Agreement')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="mb-4">
                <h2 class="fw-bold mb-2"><i class="fas fa-file-contract me-2 text-primary"></i>Upload Lease Agreement</h2>
                <p class="text-muted">Upload the signed lease agreement for your rental to complete the booking process.</p>
            </div>

            <!-- Alert Messages -->
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Upload Failed</strong>
                    <ul class="mb-0 ps-3 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Rental Details Card -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-light border-0">
                    <h5 class="mb-0"><i class="fas fa-home me-2"></i>Rental Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="small text-muted mb-1">Property</div>
                            <div class="fw-bold">{{ $rental->house->title ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="small text-muted mb-1">Location</div>
                            <div class="fw-bold">{{ $rental->house->location ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="small text-muted mb-1">Monthly Rent</div>
                            <div class="fw-bold text-success">Nu. {{ number_format($rental->monthly_rent ?? $rental->house->price ?? 0, 0) }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="small text-muted mb-1">Owner</div>
                            <div class="fw-bold">{{ $rental->house->owner->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-0">
                    <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Upload Lease Agreement</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('tenant.lease.upload', $rental) }}" method="POST" enctype="multipart/form-data" id="leaseUploadForm">
                        @csrf

                        <!-- File Input -->
                        <div class="mb-4">
                            <label for="leaseFile" class="form-label fw-semibold">
                                Lease Agreement File <span class="text-danger">*</span>
                            </label>
                            <div class="border-2 border-dashed rounded-3 p-4 text-center" id="dragDropZone" style="cursor: pointer; transition: all 0.3s; border-color: #dee2e6;">
                                <input 
                                    type="file" 
                                    id="leaseFile" 
                                    name="file" 
                                    class="form-control d-none" 
                                    accept=".pdf"
                                    required
                                >
                                <div class="py-3">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3 d-block"></i>
                                    <div class="fw-semibold mb-1">Drag and drop your PDF here</div>
                                    <div class="small text-muted mb-2">or click to browse</div>
                                    <div class="small text-muted">PDF only • Maximum 10MB</div>
                                </div>
                                <div class="mt-3" id="fileName"></div>
                            </div>
                            @error('file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Information Box -->
                        <div class="alert alert-info d-flex gap-3 mb-4" style="border-radius: 12px;">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle mt-1" style="color: #0c63e4;"></i>
                            </div>
                            <div class="small mb-0">
                                <strong>About the lease agreement:</strong><br>
                                Please upload the signed lease agreement between you and the property owner. This document must be in PDF format and clearly show both signatures. The owner will review and sign the document.
                            </div>
                        </div>

                        <!-- Requirements -->
                        <div class="mb-4">
                            <h6 class="fw-semibold mb-2"><i class="fas fa-checklist me-2"></i>Document Requirements</h6>
                            <ul class="small mb-0 ps-3">
                                <li>File must be in PDF format</li>
                                <li>Maximum file size: 10MB</li>
                                <li>Document should be clearly readable</li>
                                <li>Include all terms and conditions agreed upon</li>
                                <li>Both parties' signatures recommended</li>
                            </ul>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-check me-2"></i>Upload Agreement
                            </button>
                            <a href="{{ route('rentals.my-rentals') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Timeline Info -->
            <div class="mt-4">
                <h6 class="fw-semibold mb-3"><i class="fas fa-timeline me-2"></i>What Happens Next</h6>
                <div class="timeline">
                    <div class="timeline-item mb-3">
                        <div class="timeline-marker bg-primary text-white" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                            <i class="fas fa-upload fa-sm"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Upload Agreement</div>
                            <div class="small text-muted">You upload the signed lease agreement</div>
                        </div>
                    </div>
                    <div class="timeline-item mb-3">
                        <div class="timeline-marker bg-secondary text-white" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                            <i class="fas fa-user fa-sm"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Owner Reviews</div>
                            <div class="small text-muted">Property owner reviews and signs the agreement</div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success text-white" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                            <i class="fas fa-check fa-sm"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Agreement Finalized</div>
                            <div class="small text-muted">Both signatures collected and rental confirmed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .timeline-item {
        display: flex;
        align-items: flex-start;
    }

    #dragDropZone {
        transition: all 0.3s ease;
    }

    #dragDropZone.dragover {
        border-color: #0d6efd !important;
        background-color: #f0f7ff;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dragDropZone = document.getElementById('dragDropZone');
        const fileInput = document.getElementById('leaseFile');
        const fileNameDiv = document.getElementById('fileName');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('leaseUploadForm');

        // Click to select file
        dragDropZone.addEventListener('click', () => fileInput.click());

        // Drag and drop
        dragDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dragDropZone.classList.add('dragover');
        });

        dragDropZone.addEventListener('dragleave', () => {
            dragDropZone.classList.remove('dragover');
        });

        dragDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dragDropZone.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                updateFileName();
            }
        });

        // File input change
        fileInput.addEventListener('change', updateFileName);

        function updateFileName() {
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                
                // Check file type
                if (file.type !== 'application/pdf') {
                    fileNameDiv.innerHTML = '<div class="text-danger small"><i class="fas fa-exclamation-circle me-1"></i>Only PDF files are allowed</div>';
                    fileInput.value = '';
                    return;
                }

                // Check file size (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    fileNameDiv.innerHTML = '<div class="text-danger small"><i class="fas fa-exclamation-circle me-1"></i>File size must be less than 10MB</div>';
                    fileInput.value = '';
                    return;
                }

                fileNameDiv.innerHTML = '<div class="text-success small"><i class="fas fa-check-circle me-1"></i>File selected: ' + file.name + '</div>';
            } else {
                fileNameDiv.innerHTML = '';
            }
        }

        // Form submit
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Uploading...';
        });
    });
</script>
@endpush
@endsection
