@extends('layouts.app')

@section('title', 'Post a House')

@section('content')

<div class="page-header">
    <div class="container">
        <h1 class="page-title">Post a House for Rent</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Post House</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-card">
                <div class="form-card-header">
                    <i class="fas fa-home me-2"></i> House Listing Details
                </div>
                <div class="form-card-body">
                    <form id="houseCreateForm" action="{{ route('houses.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Basic Info -->
                        <div class="form-section-title">Basic Information</div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Listing Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   placeholder="e.g. Cozy 2BHK near Tashichho Dzong"
                                   value="{{ old('title') }}" required>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Dzongkhag <span class="text-danger">*</span></label>
                                <select name="location_id" class="form-select @error('location_id') is-invalid @enderror" required>
                                    <option value="">Select Dzongkhag</option>
                                    @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->dzongkhag_name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('location_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Area / Locality <span class="text-danger">*</span></label>
                                <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                                       placeholder="e.g. Changlimithang, Upper Motithang"
                                       value="{{ old('location') }}" required>
                                @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label class="form-label fw-semibold">Full Address</label>
                            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                                   placeholder="House number, street, locality..."
                                   value="{{ old('address') }}">
                        </div>

                        <!-- House Details -->
                        <div class="form-section-title mt-4">Property Details</div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">House Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    @foreach($houseTypes as $type)
                                    <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Bedrooms <span class="text-danger">*</span></label>
                                <select name="bedrooms" class="form-select @error('bedrooms') is-invalid @enderror" required>
                                    @for($i = 1; $i <= 6; $i++)
                                    <option value="{{ $i }}" {{ old('bedrooms', 1) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Bathrooms <span class="text-danger">*</span></label>
                                <select name="bathrooms" class="form-select @error('bathrooms') is-invalid @enderror" required>
                                    @for($i = 1; $i <= 4; $i++)
                                    <option value="{{ $i }}" {{ old('bathrooms', 1) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Monthly Rent (Nu.) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Nu.</span>
                                    <input type="number" name="price"
                                           class="form-control @error('price') is-invalid @enderror"
                                           placeholder="e.g. 8000"
                                           value="{{ old('price') }}" min="0" step="50" required>
                                    @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Floor Area</label>
                                <input type="text" name="area"
                                       class="form-control @error('area') is-invalid @enderror"
                                       placeholder="e.g. 850 sq.ft"
                                       value="{{ old('area') }}">
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="4"
                                      placeholder="Describe the property, amenities, nearby facilities...">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Upload -->
                        <div class="form-section-title mt-4">Property Photos</div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Upload Photos <span class="text-danger">*</span></label>
                            <input type="file" name="images[]" id="imageInput"
                                   class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror"
                                   accept="image/jpeg,image/png,image/jpg,image/webp"
                                   multiple required onchange="previewImages(this)">
                            <div class="form-text">Select at least 3 photos. Max 2MB each. JPEG, PNG, or WebP. You can select again to add more photos.</div>
                            <div id="imageCountError" class="invalid-feedback d-none">Please upload at least 3 photos of the property.</div>
                            <div id="imageCountInfo" class="form-text d-none"></div>
                            @error('images')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('images.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <button type="button" id="clearImagesBtn" class="btn btn-sm btn-outline-secondary mt-2 d-none" onclick="clearSelectedImages()">
                                Clear selected photos
                            </button>
                            <div id="imagePreview" class="mt-3 d-none">
                                <div id="previewGrid" class="d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 justify-content-end">
                            <a href="{{ route('houses.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                            <button type="button" class="btn btn-hrs-primary px-5" onclick="showPostConfirm()">
                                <i class="fas fa-paper-plane me-2"></i> Post Listing
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

{{-- ── Confirmation Modal ──────────────────────────────────────────────── --}}
<div class="modal fade" id="postConfirmModal" tabindex="-1" aria-labelledby="postConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">

            {{-- Header --}}
            <div class="modal-header border-0 pb-0" style="background:#f0fdf4;padding:1.5rem 1.5rem .75rem;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:46px;height:46px;background:#dcfce7;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-paper-plane" style="color:#15803d;font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="postConfirmLabel" style="color:#0f172a;">Ready to post?</h5>
                        <p class="mb-0" style="font-size:.8rem;color:#64748b;">Please review your listing before publishing.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            {{-- Summary --}}
            <div class="modal-body px-4 py-3">
                <div id="confirmSummary" style="background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;padding:1rem;">
                    <div class="row g-2" style="font-size:.83rem;">
                        <div class="col-5" style="color:#64748b;font-weight:600;">Title</div>
                        <div class="col-7" id="cs-title" style="color:#0f172a;font-weight:700;">—</div>

                        <div class="col-5" style="color:#64748b;font-weight:600;">Type</div>
                        <div class="col-7" id="cs-type" style="color:#0f172a;">—</div>

                        <div class="col-5" style="color:#64748b;font-weight:600;">Location</div>
                        <div class="col-7" id="cs-location" style="color:#0f172a;">—</div>

                        <div class="col-5" style="color:#64748b;font-weight:600;">Monthly Rent</div>
                        <div class="col-7" id="cs-price" style="color:#15803d;font-weight:700;">—</div>

                        <div class="col-5" style="color:#64748b;font-weight:600;">Bedrooms / Baths</div>
                        <div class="col-7" id="cs-rooms" style="color:#0f172a;">—</div>
                    </div>
                </div>

                <div class="mt-3 p-3 d-flex align-items-start gap-2"
                     style="background:#fef9c3;border-radius:10px;border:1px solid #fde68a;font-size:.8rem;color:#92400e;">
                    <i class="fas fa-circle-info mt-1" style="flex-shrink:0;"></i>
                    <span>Once submitted, your listing will be reviewed by the admin before it goes live. You can still edit it afterwards.</span>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                        style="border-radius:10px;font-weight:600;font-size:.85rem;">
                    <i class="fas fa-arrow-left me-1"></i> Go Back &amp; Edit
                </button>
                <button type="button" class="btn px-5" id="confirmPostBtn"
                        style="background:#15803d;color:#fff;border-radius:10px;font-weight:700;font-size:.85rem;"
                        onclick="document.getElementById('houseCreateForm').submit();">
                    <i class="fas fa-paper-plane me-2"></i> Yes, Post It!
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedImages = [];

function fileKey(file) {
    return [file.name, file.size, file.lastModified].join('::');
}

function syncImageInputFiles(input) {
    const dataTransfer = new DataTransfer();
    selectedImages.forEach(file => dataTransfer.items.add(file));
    input.files = dataTransfer.files;
}

function renderImagePreviewState(input) {
    const preview = document.getElementById('imagePreview');
    const previewGrid = document.getElementById('previewGrid');
    const countError = document.getElementById('imageCountError');
    const countInfo = document.getElementById('imageCountInfo');
    const clearBtn = document.getElementById('clearImagesBtn');

    previewGrid.innerHTML = '';

    if (selectedImages.length === 0) {
        preview.classList.add('d-none');
        countInfo.classList.add('d-none');
        countInfo.textContent = '';
        clearBtn.classList.add('d-none');
        input.classList.remove('is-invalid');
        countError.classList.add('d-none');
        return;
    }

    selectedImages.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = e => {
            const item = document.createElement('div');
            item.className = 'position-relative';

            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = 'Preview';
            img.className = 'img-thumbnail';
            img.style.height = '110px';
            img.style.width = '180px';
            img.style.objectFit = 'cover';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 m-1 py-0 px-2';
            removeBtn.textContent = 'x';
            removeBtn.title = 'Remove this photo';
            removeBtn.onclick = () => removeSelectedImage(index);

            item.appendChild(img);
            item.appendChild(removeBtn);
            previewGrid.appendChild(item);
        };
        reader.readAsDataURL(file);
    });

    preview.classList.remove('d-none');
    countInfo.classList.remove('d-none');
    countInfo.textContent = 'Selected ' + selectedImages.length + ' photo' + (selectedImages.length === 1 ? '' : 's') + '. Minimum required: 3.';
    clearBtn.classList.remove('d-none');

    if (selectedImages.length < 3) {
        input.classList.add('is-invalid');
        countError.classList.remove('d-none');
    } else {
        input.classList.remove('is-invalid');
        countError.classList.add('d-none');
    }
}

function previewImages(input) {
    const incomingFiles = Array.from(input.files || []);
    if (incomingFiles.length > 0) {
        const existingKeys = new Set(selectedImages.map(file => fileKey(file)));
        incomingFiles.forEach(file => {
            const key = fileKey(file);
            if (!existingKeys.has(key)) {
                selectedImages.push(file);
                existingKeys.add(key);
            }
        });
    }

    syncImageInputFiles(input);
    renderImagePreviewState(input);
}

function clearSelectedImages() {
    const input = document.getElementById('imageInput');
    selectedImages = [];
    syncImageInputFiles(input);
    renderImagePreviewState(input);
}

function removeSelectedImage(index) {
    const input = document.getElementById('imageInput');
    selectedImages = selectedImages.filter((_, i) => i !== index);
    syncImageInputFiles(input);
    renderImagePreviewState(input);
}

function showPostConfirm() {
    // Read form values
    const title    = document.querySelector('[name=title]').value.trim();
    const type     = document.querySelector('[name=type]').value;
    const location = document.querySelector('[name=location]').value.trim();
    const price    = document.querySelector('[name=price]').value;
    const beds     = document.querySelector('[name=bedrooms]').value;
    const baths    = document.querySelector('[name=bathrooms]').value;
    const imageInput = document.getElementById('imageInput');
    const countError = document.getElementById('imageCountError');

    // Basic validation — let browser handle required fields first
    const form = document.getElementById('houseCreateForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    if (!imageInput.files || imageInput.files.length < 3) {
        imageInput.classList.add('is-invalid');
        countError.classList.remove('d-none');
        return;
    }

    // Populate summary
    document.getElementById('cs-title').textContent    = title    || '(not provided)';
    document.getElementById('cs-type').textContent     = type     || '(not selected)';
    document.getElementById('cs-location').textContent = location || '(not provided)';
    document.getElementById('cs-price').textContent    = price ? 'Nu. ' + Number(price).toLocaleString() + ' / month' : '—';
    document.getElementById('cs-rooms').textContent    = beds + ' bed   ·   ' + baths + ' bath';

    new bootstrap.Modal(document.getElementById('postConfirmModal')).show();
}
</script>
@endpush
