@extends('layouts.app')

@section('title', 'Edit Listing')

@section('content')

<div class="page-header">
    <div class="container">
        <h1 class="page-title">Edit House Listing</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('houses.show', $house) }}">{{ $house->title }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-card">
                <div class="form-card-header">
                    <i class="fas fa-edit me-2"></i> Update Listing
                </div>
                <div class="form-card-body">
                    <form action="{{ route('houses.update', $house) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')

                        <div class="form-section-title">Basic Information</div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Listing Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $house->title) }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Dzongkhag <span class="text-danger">*</span></label>
                                <select name="location_id" class="form-select @error('location_id') is-invalid @enderror" required>
                                    @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('location_id', $house->location_id) == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->dzongkhag_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Area / Locality <span class="text-danger">*</span></label>
                                <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                                       value="{{ old('location', $house->location) }}" required>
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label class="form-label fw-semibold">Full Address</label>
                            <input type="text" name="address" class="form-control"
                                   value="{{ old('address', $house->address) }}">
                        </div>

                        <div class="form-section-title mt-4">Property Details</div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">House Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    @foreach($houseTypes as $type)
                                    <option value="{{ $type }}" {{ old('type', $house->type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Bedrooms <span class="text-danger">*</span></label>
                                <select name="bedrooms" class="form-select" required>
                                    @for($i = 1; $i <= 6; $i++)
                                    <option value="{{ $i }}" {{ old('bedrooms', $house->bedrooms) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Bathrooms <span class="text-danger">*</span></label>
                                <select name="bathrooms" class="form-select" required>
                                    @for($i = 1; $i <= 4; $i++)
                                    <option value="{{ $i }}" {{ old('bathrooms', $house->bathrooms) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Monthly Rent (Nu.) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Nu.</span>
                                    <input type="number" name="price" class="form-control"
                                           value="{{ old('price', $house->price) }}" min="0" step="50" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Floor Area</label>
                                <input type="text" name="area" class="form-control"
                                       value="{{ old('area', $house->area) }}" placeholder="e.g. 850 sq.ft">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="available" {{ old('status', $house->status) == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="rented" {{ old('status', $house->status) == 'rented' ? 'selected' : '' }}>Rented</option>
                                    <option value="pending" {{ old('status', $house->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="4">{{ old('description', $house->description) }}</textarea>
                        </div>

                        <div class="form-section-title mt-4">Property Image</div>

                        @if($house->image)
                        <div class="mb-3">
                            <div class="small text-muted mb-2">Current image:</div>
                            <img src="{{ $house->image_url }}" alt="Current" class="img-thumbnail" style="max-height:180px;">
                        </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Upload New Image (optional)</label>
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror"
                                   accept="image/jpeg,image/png,image/jpg,image/webp">
                            <div class="form-text">Leave blank to keep current image. Max 2MB.</div>
                            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex gap-3 justify-content-end">
                            <a href="{{ route('houses.show', $house) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-hrs-primary px-5">
                                <i class="fas fa-save me-2"></i> Update Listing
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
