<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\AdminCommissionTransaction;
use App\Models\Inspection;
use App\Models\LeaseAgreement;
use App\Models\MoveOutRequest;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\User;
use App\Notifications\WorkflowStatusNotification;
use App\Services\LeaseAgreementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RentalController extends Controller
{
    private function commissionRate(): float
    {
        $db = DB::table('settings')->where('key', 'commission_rate')->value('value');
        return $db !== null ? (float) $db : 10.0;
    }

    public function store(Request $request, House $house)
    {
        $validated = $request->validate([
            'rental_date' => 'nullable|date|after_or_equal:today',
            'notes'       => 'nullable|string|max:500',
        ]);

        $existing = Rental::where('house_id', $house->id)
            ->where('tenant_id', Auth::id())
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($existing) {
            return back()->with('error', 'You already have an active rental request for this house.');
        }

        $rental = Rental::create([
            'house_id'     => $house->id,
            'tenant_id'    => Auth::id(),
            'rental_date'  => $validated['rental_date'] ?? now()->toDateString(),
            'monthly_rent' => $house->price,
            'status'       => 'pending',
            'notes'        => $validated['notes'] ?? null,
        ]);

        $rental->loadMissing(['tenant', 'house.owner']);

        // Notify owner for direct action.
        if ($rental->house?->owner) {
            $rental->house->owner->notify(new WorkflowStatusNotification(
                'rental_requested',
                'New Rental Request',
                'Tenant ' . ($rental->tenant->name ?? 'Unknown Tenant') . ' requested to rent ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '. Please review the request.'
            ));
        }

        // Notify admins for platform-level monitoring.
        User::where('role', 'admin')->get()->each(function ($admin) use ($rental) {
            $admin->notify(new WorkflowStatusNotification(
                'rental_requested_admin',
                'New Tenant House Request',
                'A tenant submitted a rental request for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '. Please review in the admin dashboard.'
            ));
        });

        return back()->with('success', 'Rental request sent successfully. The owner has been notified and will review your request.');
    }

    public function myRentals()
    {
        $rentals = Rental::with(['house', 'house.locationModel'])
            ->where('tenant_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('rentals.my-rentals', compact('rentals'));
    }

    public function stayDecision(Request $request, Rental $rental)
    {
        if ((int) $rental->tenant_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($rental->status !== 'active') {
            return back()->with('error', 'Stay decision is only available for accepted rentals.');
        }

        $validated = $request->validate([
            'decision' => 'required|in:yes,no',
            'message' => 'required_if:decision,no|nullable|string|max:1500',
            'move_out_date' => 'required_if:decision,no|nullable|date|after_or_equal:today',
            'lease_extension' => 'nullable|in:6_months,1_year',
        ]);

        $inspectionCompleted = Inspection::where('tenant_id', Auth::id())
            ->where('house_id', $rental->house_id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->exists();

        if (! $inspectionCompleted) {
            return back()->with('error', 'Inspection must be completed before you can make this decision.');
        }

        $decisionPending = ! in_array($rental->stay_decision, ['yes', 'no'], true);

        if (! $decisionPending) {
            return back()->with('error', 'Stay decision was already submitted for this rental.');
        }

        if ($validated['decision'] === 'yes') {
            $note = 'Tenant confirmed stay after inspection on ' . now()->format('Y-m-d H:i');
            if (!empty($validated['message'])) {
                $note .= ' | Message: ' . trim($validated['message']);
            }

            $endDate = $rental->end_date;
            if (! empty($validated['lease_extension'])) {
                $baseDate = $rental->end_date && $rental->end_date->isFuture()
                    ? $rental->end_date->copy()
                    : now();

                $endDate = $validated['lease_extension'] === '1_year'
                    ? $baseDate->addYearNoOverflow()->toDateString()
                    : $baseDate->addMonthsNoOverflow(6)->toDateString();

                $note .= ' | Lease extended: ' . ($validated['lease_extension'] === '1_year' ? '1 year' : '6 months');
            }

            $rental->update([
                'status' => 'active',
                'lease_status' => 'requested',
                'lease_requested_at' => now(),
                'stay_decision' => 'yes',
                'stay_decision_message' => ! empty($validated['message']) ? trim((string) $validated['message']) : null,
                'stay_decision_at' => now(),
                'end_date' => $endDate,
                'notes' => trim(($rental->notes ? $rental->notes . PHP_EOL : '') . $note),
            ]);

            if ($rental->house && ! in_array($rental->house->status, ['pending', 'rejected'], true)) {
                $rental->house->update(['status' => 'rented']);
            }

            if ($rental->house && $rental->house->owner) {
                $rental->house->owner->notify(new WorkflowStatusNotification(
                    'tenant_confirmed_stay',
                    'Tenant Confirmed Stay',
                    'Tenant confirmed stay after inspection for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '. Admin will upload the lease agreement and notify the tenant for review.'
                ));
            }

            User::where('role', 'admin')->get()->each(function ($admin) use ($rental, $validated) {
                $message = 'Tenant confirmed they want to stay after inspection for '
                    . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '.';

                if (! empty($validated['message'])) {
                    $message .= ' Tenant message: ' . trim((string) $validated['message']);
                }

                $admin->notify(new WorkflowStatusNotification(
                    'tenant_stay_decision_yes',
                    'Tenant Stay Decision: Wants To Stay',
                    $message . ' Please upload the lease agreement from Admin > Rentals so tenant can review and proceed.'
                ));
            });

            return back()->with('success', 'You are currently staying in this property.');
        }

        $moveOutDate = $validated['move_out_date'];
        $reason = trim((string) ($validated['message'] ?? 'Tenant selected Move Out after inspection.'));

        $existingOpenRequest = MoveOutRequest::where('rental_id', $rental->id)
            ->whereIn('status', ['requested', 'approved'])
            ->exists();

        $moveOutRequest = null;
        if (! $existingOpenRequest) {
            $moveOutRequest = MoveOutRequest::create([
                'rental_id' => $rental->id,
                'tenant_id' => Auth::id(),
                'owner_id' => $rental->house->owner_id,
                'house_id' => $rental->house_id,
                'booking_id' => $rental->booking?->id,
                'reason' => $reason,
                'move_out_date' => $moveOutDate,
                'status' => 'requested',
            ]);
        }

        $note = 'Tenant selected move-out after inspection on ' . now()->format('Y-m-d H:i')
            . ' | Move-out date: ' . $moveOutDate
            . ($reason ? ' | Reason: ' . $reason : '');

        $rental->update([
            'status' => 'active',
            'lease_status' => 'rejected',
            'lease_reviewed_at' => now(),
            'stay_decision' => 'no',
            'stay_decision_message' => $reason,
            'stay_decision_at' => now(),
            'notes' => trim(($rental->notes ? $rental->notes . PHP_EOL : '') . $note),
        ]);

        if ($moveOutRequest && $rental->house && $rental->house->owner) {
            $rental->house->owner->notify(new WorkflowStatusNotification(
                'move_out_requested',
                'Move-Out Requested',
                'Tenant selected move-out after inspection for ' . ($rental->house->title ?? ('Property #' . $rental->house_id))
                . '. Planned move-out date: ' . $moveOutDate
                . '. Reason: ' . $reason
            ));
        }

        User::where('role', 'admin')->get()->each(function ($admin) use ($rental, $validated) {
            $message = 'Tenant selected Move Out after inspection for '
                . ($rental->house->title ?? ('Property #' . $rental->house_id))
                . '. Move-out date: ' . ($validated['move_out_date'] ?? 'N/A')
                . '. Reason: ' . trim((string) ($validated['message'] ?? 'Not provided'))
                . '. Proceed with inspection closeout and security deposit refund.';

            $admin->notify(new WorkflowStatusNotification(
                'tenant_stay_decision_no',
                'Tenant Decision: Move Out',
                $message
            ));
        });

        if ($existingOpenRequest) {
            return back()->with('success', 'Your move-out decision is recorded. An existing move-out request is already in progress.');
        }

        return back()->with('success', 'Your move-out request has been submitted. Admin can now process security deposit refund after move-out review.');
    }

    public function downloadLease(LeaseAgreement $leaseAgreement)
    {
        $rental = $leaseAgreement->rental;

        if (! $rental) {
            abort(404, 'Lease not found.');
        }

        $user = Auth::user();
        $isTenant = (int) $rental->tenant_id === (int) $user->id;
        $isOwner = (int) ($rental->house->owner_id ?? 0) === (int) $user->id;
        $isAdmin = $user->role === 'admin';

        if (! $isTenant && ! $isOwner && ! $isAdmin) {
            abort(403, 'Unauthorized action.');
        }

        if (! Storage::disk('public')->exists($leaseAgreement->file_path)) {
            return back()->with('error', 'Lease file is not available right now.');
        }

        return response()->download(
            storage_path('app/public/' . $leaseAgreement->file_path),
            $leaseAgreement->original_name
        );
    }

    public function makePayment(Request $request, Rental $rental)
    {
        if ((int) $rental->tenant_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $leaseNotRequested = in_array($rental->lease_status, [null, '', 'not_requested'], true);

        if ($leaseNotRequested) {
            return back()->with('error', 'Please confirm Yes/No after inspection before proceeding with payment.');
        }

        if ($rental->lease_status !== 'approved') {
            return back()->with('error', 'Please accept the lease agreement before payment submission.');
        }

        $leaseAgreement = $rental->leaseAgreement;

        if (! $leaseAgreement) {
            return back()->with('error', 'Owner must upload the lease agreement first. You can pay advance after lease upload.');
        }

        if (($leaseAgreement->tenant_review_status ?? 'pending') !== 'accepted') {
            return back()->with('error', 'Please review and accept the lease agreement before payment submission.');
        }

        $inspectionCompleted = Inspection::where('tenant_id', Auth::id())
            ->where('house_id', $rental->house_id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->exists();

        if (! $inspectionCompleted && ($leaseAgreement->tenant_review_status ?? 'pending') !== 'accepted') {
            return back()->with('error', 'Inspection must be completed before payment submission.');
        }

        $validated = $request->validate([
            'payment_type' => 'required|in:first_month_rent,monthly_rent',
            'payment_method' => 'required|in:mbob,mpay,bdbl,cash',
            'transaction_id' => 'nullable|string|max:120',
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'confirm_payment' => 'accepted',
            'notes' => 'nullable|string|max:500',
            'billing_month' => 'nullable|date_format:Y-m',
            'payment_date_selected' => 'nullable|date',
            'payment_time_selected' => 'nullable|date_format:H:i',
        ]);

        $rental->loadMissing('payments');

        $monthlyRent = (float) $rental->monthly_rent;
        $commissionRate = $rental->house?->admin_commission_rate !== null
            ? (float) $rental->house->admin_commission_rate
            : $this->commissionRate();

        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payments/proofs', 'public');
        }

        if ($validated['payment_type'] === 'first_month_rent') {
            $alreadyPaid = $rental->payments
                ->whereIn('verification_status', ['pending', 'verified'])
                ->isNotEmpty();

            if ($alreadyPaid) {
                return back()->with('error', 'A payment already exists for this rental and is awaiting/has completed verification.');
            }

            $securityDeposit = (float) ($rental->house->security_deposit_amount ?? $monthlyRent);
            // Advance now requires two months (rent + service fee per month) plus security deposit
            $breakdown = Payment::calculateAdvanceBreakdown($monthlyRent * 2, $securityDeposit, $commissionRate);

            $firstMonthPayment = Payment::create([
                'tenant_id' => Auth::id(),
                'rental_id' => $rental->id,
                'booking_id' => $rental->booking_id,
                'amount' => $breakdown['rent_payable_amount'],
                'rent_amount' => $breakdown['rent_amount'],
                'service_fee_rate' => $breakdown['service_fee_rate'],
                'service_fee_amount' => $breakdown['service_fee_amount'],
                'commission_rate' => $commissionRate,
                'commission_amount' => $breakdown['service_fee_amount'],
                'owner_share_amount' => $breakdown['owner_share_amount'],
                'payment_date' => now()->toDateString(),
                'payment_method' => $validated['payment_method'],
                'payment_type' => 'first_month_rent',
                'transaction_id' => $validated['transaction_id'] ?? null,
                'payment_proof_path' => $paymentProofPath,
                'status' => 'pending',
                'verification_status' => 'pending',
                'notes' => $validated['notes'] ?? ('Tenant submitted first month rent payment via ' . strtoupper($validated['payment_method']) . ' from dashboard.'),
            ]);

            $securityDepositPayment = Payment::create([
                'tenant_id' => Auth::id(),
                'rental_id' => $rental->id,
                'booking_id' => $rental->booking_id,
                'amount' => $breakdown['security_deposit_amount'],
                'security_deposit_amount' => $breakdown['security_deposit_amount'],
                'commission_rate' => 0,
                'commission_amount' => 0,
                'owner_share_amount' => 0,
                'held_by_admin' => true,
                'payment_date' => now()->toDateString(),
                'payment_method' => $validated['payment_method'],
                'payment_type' => 'security_deposit',
                'transaction_id' => $validated['transaction_id'] ?? null,
                'payment_proof_path' => $paymentProofPath,
                'status' => 'pending',
                'verification_status' => 'pending',
                'notes' => $validated['notes'] ?? ('Tenant submitted security deposit payment via ' . strtoupper($validated['payment_method']) . ' from dashboard.'),
            ]);

            if ($rental->house && $rental->house->owner_id) {
                AdminCommissionTransaction::updateOrCreate(
                    ['payment_id' => $firstMonthPayment->id],
                    [
                        'tenant_id' => Auth::id(),
                        'owner_id' => $rental->house->owner_id,
                        'property_id' => $rental->house_id,
                        'payment_amount' => $breakdown['rent_payable_amount'],
                        'admin_commission' => $breakdown['service_fee_amount'],
                        'owner_share' => $breakdown['owner_share_amount'],
                        'transaction_date' => now()->toDateString(),
                        'status' => 'pending',
                        'notes' => 'First month rent payment awaiting verification.',
                    ]
                );

                AdminCommissionTransaction::updateOrCreate(
                    ['payment_id' => $securityDepositPayment->id],
                    [
                        'tenant_id' => Auth::id(),
                        'owner_id' => $rental->house->owner_id,
                        'property_id' => $rental->house_id,
                        'payment_amount' => $breakdown['security_deposit_amount'],
                        'admin_commission' => 0,
                        'owner_share' => 0,
                        'transaction_date' => now()->toDateString(),
                        'status' => 'pending',
                        'notes' => 'Security deposit held by admin awaiting verification.',
                    ]
                );
            }

            if ($rental->house && $rental->house->owner) {
                $rental->house->owner->notify(new WorkflowStatusNotification(
                    'payment_submitted',
                    'Advance Payment Submitted',
                    'Tenant submitted advance payment (first month rent + security deposit) via ' . strtoupper($validated['payment_method']) . ' for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '. Waiting for admin verification.'
                ));
            }

            User::where('role', 'admin')->get()->each(function ($admin) use ($rental) {
                $admin->notify(new WorkflowStatusNotification(
                    'payment_pending_verification',
                    'Advance Payment Verification Needed',
                    'Advance payment proofs (first month rent + security deposit) are waiting verification for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '.'
                ));
            });

            return redirect()->route('tenant.dashboard')->with('success', 'Advance payment submitted successfully. Waiting for admin verification.');
        } elseif ($validated['payment_type'] === 'monthly_rent') {
            $securityDepositDue = (float) ($rental->leaseAgreement?->security_deposit_amount ?? $rental->house?->security_deposit_amount ?? 0);
            $advancePayments = $rental->payments->whereIn('payment_type', ['first_month_rent', 'security_deposit']);
            $verifiedTypes = $advancePayments->where('verification_status', 'verified')->pluck('payment_type')->unique();
            $requiredTypes = ['first_month_rent'];
            if ($securityDepositDue > 0) {
                $requiredTypes[] = 'security_deposit';
            }

            $advanceFullyVerified = collect($requiredTypes)->every(fn ($type) => $verifiedTypes->contains($type));

            if (! $advanceFullyVerified) {
                return back()->with('error', 'Advance payment must be fully verified before submitting monthly rent.');
            }

            $monthlyRentPaidThisMonth = $rental->payments
                ->where('payment_type', 'monthly_rent')
                ->filter(function ($payment) {
                    $selectedMonth = $validated['billing_month'] ? \Carbon\Carbon::createFromFormat('Y-m', $validated['billing_month'])->startOfMonth() : now()->startOfMonth();
                    return ($payment->billing_month && $payment->billing_month->isSameMonth($selectedMonth))
                        || (! $payment->billing_month && $payment->payment_date && $payment->payment_date->isSameMonth($selectedMonth));
                })
                ->whereIn('verification_status', ['pending', 'verified'])
                ->isNotEmpty();

            if ($monthlyRentPaidThisMonth) {
                $monthText = $validated['billing_month'] ? \Carbon\Carbon::createFromFormat('Y-m', $validated['billing_month'])->format('F Y') : now()->format('F Y');
                return back()->with('error', 'You already have a monthly rent payment submitted for ' . $monthText . '.');
            }

            $breakdown = Payment::calculateAdvanceBreakdown($monthlyRent, 0, $commissionRate);

            $monthlyPayment = Payment::create([
                'tenant_id' => Auth::id(),
                'rental_id' => $rental->id,
                'booking_id' => $rental->booking_id,
                'amount' => $breakdown['rent_payable_amount'],
                'rent_amount' => $breakdown['rent_amount'],
                'service_fee_rate' => $breakdown['service_fee_rate'],
                'service_fee_amount' => $breakdown['service_fee_amount'],
                'commission_rate' => $commissionRate,
                'commission_amount' => $breakdown['service_fee_amount'],
                'owner_share_amount' => $breakdown['owner_share_amount'],
                'payment_date' => $validated['payment_date_selected'] ?? now()->toDateString(),
                'billing_month' => $validated['billing_month'] ? $validated['billing_month'] . '-01' : now()->startOfMonth()->toDateString(),
                'payment_method' => $validated['payment_method'],
                'payment_type' => 'monthly_rent',
                'transaction_id' => $validated['transaction_id'] ?? null,
                'payment_proof_path' => $paymentProofPath,
                'status' => 'pending',
                'verification_status' => 'pending',
                'notes' => $validated['notes'] ?? ('Tenant submitted monthly rent payment via ' . strtoupper($validated['payment_method']) . ' from dashboard.'),
            ]);

            if ($rental->house && $rental->house->owner_id) {
                AdminCommissionTransaction::updateOrCreate(
                    ['payment_id' => $monthlyPayment->id],
                    [
                        'tenant_id' => Auth::id(),
                        'owner_id' => $rental->house->owner_id,
                        'property_id' => $rental->house_id,
                        'payment_amount' => $breakdown['rent_payable_amount'],
                        'admin_commission' => $breakdown['service_fee_amount'],
                        'owner_share' => $breakdown['owner_share_amount'],
                        'transaction_date' => now()->toDateString(),
                        'status' => 'pending',
                        'notes' => 'Monthly rent payment awaiting verification for ' . now()->format('F Y') . '.',
                    ]
                );
            }

            if ($rental->house && $rental->house->owner) {
                $rental->house->owner->notify(new WorkflowStatusNotification(
                    'monthly_payment_submitted',
                    'Monthly Rent Submitted',
                    'Tenant submitted monthly rent payment for ' . ($validated['billing_month'] ? \Carbon\Carbon::createFromFormat('Y-m', $validated['billing_month'])->format('F Y') : now()->format('F Y')) . ' via ' . strtoupper($validated['payment_method']) . ' for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '. Waiting for admin verification.'
                ));
            }

            User::where('role', 'admin')->get()->each(function ($admin) use ($rental) {
                $admin->notify(new WorkflowStatusNotification(
                    'monthly_payment_verification_needed',
                    'Monthly Rent Verification Needed',
                    'A monthly rent payment proof for ' . now()->format('F Y') . ' is waiting verification for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '.'
                ));
            });

            return redirect()->route('tenant.dashboard')->with('success', 'Monthly rent submitted successfully. Waiting for admin verification.');
        }
    }

    public function acceptAgreement(Rental $rental)
    {
        if ((int) $rental->tenant_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($rental->lease_status !== 'requested') {
            return back()->with('error', 'Agreement acceptance is not available at this stage.');
        }

        $leaseAgreement = $rental->leaseAgreement;
        if (! $leaseAgreement) {
            return back()->with('error', 'Agreement has not been generated yet.');
        }

        if ($leaseAgreement->tenant_signed_at) {
            return back()->with('error', 'You already accepted this agreement.');
        }

        $leaseAgreement->update([
            'tenant_signature_name' => Auth::user()->name,
            'tenant_signed_at' => now(),
            'tenant_review_status' => 'accepted',
            'tenant_reviewed_at' => now(),
            'tenant_review_note' => null,
        ]);

        $rental->update([
            'lease_status' => 'approved',
            'lease_reviewed_at' => now(),
        ]);

        LeaseAgreementService::regeneratePdf($leaseAgreement->fresh());

        if ($rental->house && $rental->house->owner) {
            $rental->house->owner->notify(new WorkflowStatusNotification(
                'agreement_accepted_by_tenant',
                'Agreement Accepted',
                'Tenant accepted the agreement for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '. The tenant can now submit advance payment.'
            ));
        }

        User::where('role', 'admin')->get()->each(function ($admin) use ($rental) {
            $admin->notify(new WorkflowStatusNotification(
                'agreement_accepted_by_tenant',
                'Tenant Accepted Lease Agreement',
                'Tenant accepted the lease agreement for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '. Awaiting advance payment submission.'
            ));
        });

        return redirect()->route('houses.show', $rental->house)->with('success', 'Agreement accepted! Please proceed with the advance payment.')->with('openPaymentModal', true);
    }

    public function rejectAgreement(Request $request, Rental $rental)
    {
        if ((int) $rental->tenant_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($rental->lease_status !== 'requested') {
            return back()->with('error', 'Agreement rejection is not available at this stage.');
        }

        $leaseAgreement = $rental->leaseAgreement;
        if (! $leaseAgreement) {
            return back()->with('error', 'Agreement has not been generated yet.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:3|max:1500',
        ]);

        $reason = trim((string) $validated['rejection_reason']);

        $leaseAgreement->update([
            'tenant_review_status' => 'rejected',
            'tenant_reviewed_at' => now(),
            'tenant_review_note' => $reason,
            'tenant_signature_name' => null,
            'tenant_signed_at' => null,
        ]);

        $rental->update([
            'lease_status' => 'rejected',
            'lease_reviewed_at' => now(),
            'notes' => trim((string) ($rental->notes
                ? $rental->notes . PHP_EOL
                : '') . 'Tenant rejected lease agreement: ' . $reason),
        ]);

        if ($rental->house && $rental->house->owner) {
            $rental->house->owner->notify(new WorkflowStatusNotification(
                'agreement_rejected_by_tenant',
                'Agreement Rejected',
                'Tenant rejected the lease agreement for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '. Reason: ' . $reason
            ));
        }

        User::where('role', 'admin')->get()->each(function ($admin) use ($rental, $reason) {
            $admin->notify(new WorkflowStatusNotification(
                'agreement_rejected_by_tenant',
                'Tenant Rejected Lease Agreement',
                'Tenant rejected the lease agreement for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '. Reason: ' . $reason
            ));
        });

        return back()->with('success', 'Agreement rejected and admin has been notified.');
    }

    public function requestMoveOut(Request $request, Rental $rental)
    {
        if ((int) $rental->tenant_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($rental->status !== 'active') {
            return back()->with('error', 'Move-out request is only available for active rentals.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1500',
            'move_out_date' => 'required|date|after_or_equal:today',
        ]);

        $existingOpenRequest = MoveOutRequest::where('rental_id', $rental->id)
            ->whereIn('status', ['requested', 'approved'])
            ->exists();

        if ($existingOpenRequest) {
            return back()->with('error', 'You already have an open move-out request for this rental.');
        }

        $moveOutRequest = MoveOutRequest::create([
            'rental_id' => $rental->id,
            'tenant_id' => Auth::id(),
            'owner_id' => $rental->house->owner_id,
            'house_id' => $rental->house_id,
            'booking_id' => $rental->booking?->id,
            'reason' => $validated['reason'],
            'move_out_date' => $validated['move_out_date'],
            'status' => 'requested',
        ]);

        if ($rental->house && $rental->house->owner) {
            $rental->house->owner->notify(new WorkflowStatusNotification(
                'move_out_requested',
                'Move-Out Requested',
                'Tenant requested move-out for ' . ($rental->house->title ?? ('Property #' . $rental->house_id))
                . '. Planned move-out date: ' . $validated['move_out_date']
                . '. Reason: ' . trim($validated['reason'])
            ));
        }

        User::where('role', 'admin')->get()->each(function ($admin) use ($rental, $validated) {
            $admin->notify(new WorkflowStatusNotification(
                'move_out_requested',
                'Move-Out Requested',
                'A tenant requested move-out for ' . ($rental->house->title ?? ('Property #' . $rental->house_id))
                . '. Planned move-out date: ' . $validated['move_out_date']
                . '. Reason: ' . trim($validated['reason'])
            ));
        });

        $rental->update([
            'notes' => trim(($rental->notes ? $rental->notes . PHP_EOL : '')
                . 'Move-out requested on ' . now()->format('Y-m-d H:i') . ' (Request #' . $moveOutRequest->id . ').'),
        ]);

        return back()->with('success', 'Move-out request submitted successfully.');
    }

    /**
     * Show the lease upload form for tenants
     */
    public function showLeaseUploadForm(Rental $rental)
    {
        // Verify tenant owns this rental
        if ((int) $rental->tenant_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if rental is in correct status
        if ($rental->status !== 'active' || $rental->stay_decision !== 'yes') {
            return back()->with('error', 'Lease upload is only available after confirming stay.');
        }

        $leaseAgreement = $rental->leaseAgreement;

        return view('tenant.lease-upload', compact('rental', 'leaseAgreement'));
    }

    /**
     * Upload lease agreement for tenants
     */
    public function uploadLeaseAgreement(Request $request, Rental $rental)
    {
        // Verify tenant owns this rental
        if ((int) $rental->tenant_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if rental is in correct status
        if ($rental->status !== 'active' || $rental->stay_decision !== 'yes') {
            return back()->with('error', 'Lease upload is only available after confirming stay.');
        }

        // Check if lease agreement already exists
        if ($rental->leaseAgreement) {
            return back()->with('error', 'Lease agreement already uploaded for this rental.');
        }

        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);

        try {
            $file = $validated['file'];
            $originalName = $file->getClientOriginalName();
            
            // Store file in storage/app/private/lease-agreements
            $path = $file->storeAs(
                'lease-agreements',
                'rental_' . $rental->id . '_' . time() . '.pdf',
                'private'
            );

            // Create lease agreement record
            LeaseAgreement::create([
                'rental_id'     => $rental->id,
                'owner_id'      => $rental->house->owner_id,
                'file_path'     => $path,
                'original_name' => $originalName,
                'uploaded_at'   => now(),
            ]);

            // Update lease status
            $rental->update(['lease_status' => 'requested']);

            // Notify owner about the lease agreement upload
            $rental->house->owner->notify(new WorkflowStatusNotification(
                'Lease Agreement Uploaded',
                'Tenant ' . Auth::user()->name . ' uploaded a lease agreement for ' . ($rental->house->title ?? ('Property #' . $rental->house_id))
            ));

            return back()->with('success', 'Lease agreement uploaded successfully. Owner will review and sign.');
        } catch (\Exception $e) {
            \Log::error('Lease agreement upload error: ' . $e->getMessage());
            return back()->with('error', 'Failed to upload lease agreement. Please try again.');
        }
    }
}

