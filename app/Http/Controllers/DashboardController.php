<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Inspection;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function tenant(Request $request)
    {
        $admin = User::where('role', 'admin')->first();
        $tenantId = Auth::id();
        $selectedRequestHouseId = (int) $request->query('selected_house', 0);
        $selectedInspectionHouseId = (int) $request->query('selected_inspection_house', 0);

        $rentals = Rental::with(['house', 'house.locationModel', 'house.owner', 'payments', 'leaseAgreement', 'moveOutRequests'])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->get();

        $activeRentals = $rentals->where('status', 'active')->count();

        $pendingRequests = $rentals->where('status', 'pending')->count();

        $acceptedRequests = $rentals->where('status', 'active')->count();

        $rejectedRequests = $rentals->where('status', 'cancelled')->count();

        $totalPaid = Payment::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->sum('amount');

        $pendingLeaseApprovals = $rentals->where('lease_status', 'requested')->count();

        $completedRentals = $rentals->where('lease_status', 'approved')->count();

        $notifications = collect();
        foreach ($rentals as $rental) {
            $securityDepositDue = (float) ($rental->leaseAgreement?->security_deposit_amount ?? $rental->house?->security_deposit_amount ?? 0);
            $advancePayments = $rental->payments->whereIn('payment_type', ['first_month_rent', 'security_deposit']);
            $verifiedTypes = $advancePayments->where('verification_status', 'verified')->pluck('payment_type')->unique();
            $requiredTypes = ['first_month_rent'];
            if ($securityDepositDue > 0) {
                $requiredTypes[] = 'security_deposit';
            }
            $advanceFullyVerified = collect($requiredTypes)->every(fn ($type) => $verifiedTypes->contains($type));

            if ($rental->status === 'active') {
                $notifications->push([
                    'type' => 'success',
                    'message' => 'Your request for "' . ($rental->house->title ?? 'selected property') . '" was accepted by the owner.',
                ]);
            }

            if ($rental->lease_status === 'requested') {
                $notifications->push([
                    'type' => 'info',
                    'message' => 'Lease workflow is in progress for "' . ($rental->house->title ?? 'selected property') . '". Complete digital signatures once payment is verified.',
                ]);
            }

            if ($rental->lease_status === 'approved') {
                $notifications->push([
                    'type' => 'success',
                    'message' => 'Your lease agreement has been approved. The rental process is complete.',
                ]);
            }

            if ($advanceFullyVerified) {
                $notifications->push([
                    'type' => 'success',
                    'message' => 'Your advance payment is completed for "' . ($rental->house->title ?? 'selected property') . '". You can now shift to this place.',
                ]);
            }

            if ($rental->leaseAgreement) {
                $notifications->push([
                    'type' => 'info',
                    'message' => 'Digital agreement is available for "' . ($rental->house->title ?? 'selected property') . '". You can download and sign it.',
                ]);
            }

            if ($rental->lease_status === 'rejected') {
                $notifications->push([
                    'type' => 'danger',
                    'message' => 'Lease agreement was rejected by owner for "' . ($rental->house->title ?? 'selected property') . '".',
                ]);
            }
        }

        // Inspections
        $inspections = Inspection::with('house')
            ->where('tenant_id', $tenantId)
            ->latest()
            ->get();

        $completedInspectionHouseIds = $inspections
            ->whereIn('status', ['confirmed', 'completed'])
            ->pluck('house_id')
            ->unique()
            ->values();

        $pendingInspections = $inspections->where('status', 'pending')->count();
        $confirmedInspections = $inspections->where('status', 'confirmed')->count();
        $rescheduledInspections = $inspections->where('status', 'rescheduled')->count();
        $cancelledInspections = $inspections->where('status', 'cancelled')->count();
        $rejectedInspections = $inspections->where('status', 'rejected')->count();

        // Notifications for inspections
        foreach ($inspections as $insp) {
            if ($insp->status === 'confirmed') {
                $notifications->push([
                    'type'    => 'success',
                    'message' => 'Your inspection request for "' . ($insp->house->title ?? 'a property') . '" has been confirmed'
                                 . ($insp->scheduled_at ? ' — scheduled for ' . $insp->scheduled_at->format('d M Y, g:i A') : '') . '.',
                ]);
            }
            if ($insp->status === 'rescheduled') {
                $notifications->push([
                    'type'    => 'info',
                    'message' => 'Your inspection request for "' . ($insp->house->title ?? 'a property') . '" has been rescheduled'
                                 . ($insp->scheduled_at ? ' to ' . $insp->scheduled_at->format('d M Y, g:i A') : '')
                                 . ($insp->admin_message ? '. Message: ' . $insp->admin_message : '.')
                                 ,
                ]);
            }
            if ($insp->status === 'rejected') {
                $notifications->push([
                    'type'    => 'danger',
                    'message' => 'Your inspection request for "' . ($insp->house->title ?? 'a property') . '" was rejected'
                                 . ($insp->rejection_reason ? '. Reason: ' . $insp->rejection_reason : '.'),
                ]);
            }
        }

        foreach ($rentals as $rental) {
            if (
                $rental->status === 'active' &&
                in_array($rental->lease_status, [null, '', 'not_requested'], true) &&
                $completedInspectionHouseIds->contains($rental->house_id)
            ) {
                $notifications->push([
                    'type'    => 'warning',
                    'message' => 'The inspection for "' . ($rental->house->title ?? 'selected property') . '" is completed. Please confirm whether you want to stay.',
                ]);
            }

            $latestMoveOut = $rental->moveOutRequests->sortByDesc('created_at')->first();
            if ($latestMoveOut) {
                $moveOutText = match ($latestMoveOut->status) {
                    'requested' => 'Move-out request is waiting owner review',
                    'approved' => 'Move-out request is approved by owner',
                    'completed' => 'Move-out request has been completed',
                    'rejected' => 'Move-out request was rejected by owner',
                    default => 'Move-out request status updated',
                };

                $notifications->push([
                    'type' => $latestMoveOut->status === 'rejected' ? 'danger' : 'info',
                    'message' => $moveOutText . ' for "' . ($rental->house->title ?? 'selected property') . '".',
                ]);
            }
        }

        // Houses eligible for inspection requests:
        // - all currently available listings
        // - houses the tenant has already requested/approved, so action buttons can prefill correctly
        $tenantRequestedHouseIds = $rentals->pluck('house_id')->filter()->values();
        $availableHouses = House::where(function ($query) use ($tenantRequestedHouseIds) {
            $query->where('status', 'available');

            if ($tenantRequestedHouseIds->isNotEmpty()) {
                $query->orWhereIn('id', $tenantRequestedHouseIds);
            }
        })->orderBy('title')->get();

        // Houses tenant can request directly from dashboard:
        // - currently available
        // - no existing pending/active request by this tenant
        $blockedRequestHouseIds = $rentals
            ->whereIn('status', ['pending', 'active'])
            ->pluck('house_id')
            ->filter()
            ->values();

        $requestableHouses = House::where('status', 'available')
            ->whereNotIn('id', $rentals->pluck('house_id'))
            ->orderBy('title')
            ->get();

        $activeRentalsByHouse = $rentals
            ->where('status', 'active')
            ->keyBy('house_id');

        $focusMonthlyPayment = $request->query('focus') === 'monthly-payment';
        $autoMonthlyPaymentRentalId = null;

        if ($focusMonthlyPayment) {
            foreach ($rentals as $rental) {
                if ($rental->status !== 'active') {
                    continue;
                }

                $leaseAccepted = $rental->lease_status === 'approved'
                    && (($rental->leaseAgreement->tenant_review_status ?? 'pending') === 'accepted');

                if (! $leaseAccepted) {
                    continue;
                }

                $securityDepositDue = (float) ($rental->leaseAgreement?->security_deposit_amount ?? $rental->house?->security_deposit_amount ?? 0);
                $advancePayments = $rental->payments->whereIn('payment_type', ['first_month_rent', 'security_deposit']);
                $verifiedTypes = $advancePayments->where('verification_status', 'verified')->pluck('payment_type')->unique();
                $requiredTypes = ['first_month_rent'];
                if ($securityDepositDue > 0) {
                    $requiredTypes[] = 'security_deposit';
                }

                $advanceFullyVerified = collect($requiredTypes)->every(fn ($type) => $verifiedTypes->contains($type));
                if (! $advanceFullyVerified) {
                    continue;
                }

                $monthlyRentPaidThisMonth = $rental->payments
                    ->where('payment_type', 'monthly_rent')
                    ->filter(fn ($payment) => ($payment->billing_month && $payment->billing_month->isSameMonth(now()))
                        || (! $payment->billing_month && $payment->payment_date && $payment->payment_date->isSameMonth(now())))
                    ->whereIn('verification_status', ['pending', 'verified'])
                    ->isNotEmpty();

                if (! $monthlyRentPaidThisMonth) {
                    $autoMonthlyPaymentRentalId = $rental->id;
                    break;
                }
            }
        }

        return view('dashboard.tenant', compact(
            'rentals',
            'activeRentals',
            'pendingRequests',
            'acceptedRequests',
            'rejectedRequests',
            'totalPaid',
            'pendingLeaseApprovals',
            'completedRentals',
            'notifications',
            'selectedRequestHouseId',
            'inspections',
            'completedInspectionHouseIds',
            'pendingInspections',
            'confirmedInspections',
            'rescheduledInspections',
            'cancelledInspections',
            'rejectedInspections',
            'admin',
            'availableHouses',
            'requestableHouses',
            'selectedInspectionHouseId',
            'focusMonthlyPayment',
            'autoMonthlyPaymentRentalId'
        ));
    }

    public function user()
    {
        $ownerId = Auth::id();

        $myListings = House::where('owner_id', $ownerId)->count();

        $occupiedListings = House::where('owner_id', $ownerId)
            ->where('status', 'rented')
            ->count();

        $pendingListings = House::where('owner_id', $ownerId)
            ->where('status', 'pending')
            ->count();

        return view('dashboard.user', compact('myListings', 'occupiedListings', 'pendingListings'));
    }

    public function tenantPayments(Request $request)
    {
        $tenantId = Auth::id();

        // Get all payments for this tenant
        $payments = Payment::with(['rental.house', 'rental.house.owner'])
            ->where('tenant_id', $tenantId)
            ->where('payment_type', 'monthly_rent')
            ->orderBy('billing_month', 'desc')
            ->orderBy('payment_date', 'desc')
            ->paginate(20);

        // Get payment statistics
        $totalPaid = Payment::where('tenant_id', $tenantId)
            ->where('payment_type', 'monthly_rent')
            ->where('verification_status', 'verified')
            ->sum('amount');

        $pendingPayments = Payment::where('tenant_id', $tenantId)
            ->where('payment_type', 'monthly_rent')
            ->where('verification_status', 'pending')
            ->count();

        $verifiedPayments = Payment::where('tenant_id', $tenantId)
            ->where('payment_type', 'monthly_rent')
            ->where('verification_status', 'verified')
            ->count();

        return view('tenant.payments', compact(
            'payments',
            'totalPaid',
            'pendingPayments',
            'verifiedPayments'
        ));
    }

    public function makePaymentForm(Request $request)
    {
        $tenantId = Auth::id();
        $selectedRentalId = (int) $request->query('rental_id', 0);
        $selectedMonth = $request->query('month', now()->format('Y-m'));

        // Get active rentals for the tenant
        $activeRentals = Rental::with(['house', 'house.owner', 'payments'])
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('lease_status', 'approved')
            ->latest()
            ->get();

        $paymentQuery = Payment::with(['rental.house'])
            ->where('tenant_id', $tenantId)
            ->where('payment_type', 'monthly_rent')
            ->orderBy('billing_month', 'desc')
            ->orderBy('payment_date', 'desc');

        if ($selectedRentalId > 0) {
            $paymentQuery->where('rental_id', $selectedRentalId);
        }

        // Get last 5 months of payments
        $recentPayments = (clone $paymentQuery)->limit(12)->get();
        $lastPayments = (clone $paymentQuery)->limit(5)->get();

        $selectedPayment = null;
        if ($selectedRentalId > 0) {
            $selectedPayment = Payment::with(['rental.house'])
                ->where('tenant_id', $tenantId)
                ->where('payment_type', 'monthly_rent')
                ->where('rental_id', $selectedRentalId)
                ->whereRaw('DATE_FORMAT(billing_month, "%Y-%m") = ?', [$selectedMonth])
                ->orderByDesc('payment_date')
                ->first();
        }

        // Get payment statistics
        $paymentStats = [
            'verified' => Payment::where('tenant_id', $tenantId)
                ->where('payment_type', 'monthly_rent')
                ->where('verification_status', 'verified')
                ->count(),
            'pending' => Payment::where('tenant_id', $tenantId)
                ->where('payment_type', 'monthly_rent')
                ->where('verification_status', 'pending')
                ->count(),
            'rejected' => Payment::where('tenant_id', $tenantId)
                ->where('payment_type', 'monthly_rent')
                ->where('verification_status', 'rejected')
                ->count(),
            'total_paid' => Payment::where('tenant_id', $tenantId)
                ->where('payment_type', 'monthly_rent')
                ->where('verification_status', 'verified')
                ->sum('amount'),
        ];

        return view('tenant.make-payment', compact(
            'activeRentals',
            'recentPayments',
            'lastPayments',
            'selectedRentalId',
            'selectedMonth',
            'selectedPayment',
            'paymentStats'
        ));
    }

    public function storeMonthlyPayment(Request $request)
    {
        $tenantId = Auth::id();

        // Validate the rental belongs to the tenant
        $rental = Rental::findOrFail($request->rental_id);
        if ((int) $rental->tenant_id !== (int) $tenantId) {
            abort(403, 'Unauthorized action.');
        }

        // Validate form data
        $validated = $request->validate([
            'rental_id' => 'required|exists:rentals,id',
            'payment_month' => 'required|date_format:Y-m',
            'payment_method' => 'required|in:mbob,mpay,bdbl,cash',
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'transaction_id' => 'nullable|string|max:120',
            'notes' => 'nullable|string|max:500',
            'confirm_payment' => 'accepted',
        ]);

        // Check if payment already exists for this month
        $existingPayment = Payment::where('tenant_id', $tenantId)
            ->where('rental_id', $rental->id)
            ->where('payment_type', 'monthly_rent')
            ->whereYear('billing_month', substr($validated['payment_month'], 0, 4))
            ->whereMonth('billing_month', substr($validated['payment_month'], 5, 2))
            ->whereIn('verification_status', ['pending', 'verified'])
            ->exists();

        if ($existingPayment) {
            return back()->with('error', 'A payment for this month already exists and is awaiting/has completed verification.');
        }

        // Store payment proof
        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payments/proofs', 'public');
        }

        // Get commission rate
        $commissionRate = $rental->house?->admin_commission_rate !== null
            ? (float) $rental->house->admin_commission_rate
            : 5; // Default 5% commission

        $monthlyRent = (float) $rental->monthly_rent;
        $serviceFeeAmount = round($monthlyRent * ($commissionRate / 100), 2);
        $totalAmount = round($monthlyRent + $serviceFeeAmount, 2);
        $ownerShareAmount = round($monthlyRent - $serviceFeeAmount, 2);

        // Create the payment record
        $payment = Payment::create([
            'tenant_id' => $tenantId,
            'rental_id' => $rental->id,
            'amount' => $totalAmount,
            'rent_amount' => $monthlyRent,
            'service_fee_rate' => $commissionRate,
            'service_fee_amount' => $serviceFeeAmount,
            'owner_share_amount' => $ownerShareAmount,
            'payment_date' => now(),
            'billing_month' => $validated['payment_month'] . '-01',
            'payment_method' => $validated['payment_method'],
            'payment_type' => 'monthly_rent',
            'transaction_id' => $validated['transaction_id'] ?? null,
            'payment_proof_path' => $paymentProofPath,
            'status' => 'paid',
            'verification_status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('tenant.payments')
            ->with('success', 'Payment submitted successfully! Your payment is under verification. You will receive a notification once it is verified.');
    }

    public function clearTenantNotifications()
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'tenant') {
            abort(403, 'Unauthorized action.');
        }

        $user->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications cleared.');
    }
}
