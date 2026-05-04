@extends('layouts.app')

@section('title', 'Create Booking')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="mb-4">
                        <h1 class="h3 fw-bold mb-1">Book {{ $house->title }}</h1>
                        <p class="text-muted mb-0">First month rent and security deposit are recorded separately.</p>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="text-muted small text-uppercase">Monthly Rent</div>
                                <div class="fs-5 fw-bold">Nu. {{ number_format((float) $house->price, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="text-muted small text-uppercase">Security Deposit</div>
                                <div class="fs-5 fw-bold">Nu. {{ number_format((float) ($house->security_deposit_amount ?? $house->price), 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="text-muted small text-uppercase">Held By Admin</div>
                                <div class="fs-5 fw-bold">Deposit is not transferred to the owner</div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('bookings.store', $house) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Booking Date</label>
                                <input type="date" name="booking_date" value="{{ old('booking_date', now()->toDateString()) }}" class="form-control @error('booking_date') is-invalid @enderror">
                                @error('booking_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Security Deposit Amount</label>
                                <input type="number" step="0.01" min="0" name="security_deposit_amount" value="{{ old('security_deposit_amount', $house->security_deposit_amount ?? $house->price) }}" class="form-control @error('security_deposit_amount') is-invalid @enderror">
                                @error('security_deposit_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" rows="4" class="form-control @error('notes') is-invalid @enderror" placeholder="Any special booking notes">{{ old('notes') }}</textarea>
                                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary px-4">Confirm Booking</button>
                            <a href="{{ route('houses.show', $house) }}" class="btn btn-outline-secondary">Back to House</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
