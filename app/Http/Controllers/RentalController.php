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
            'rental_date' => 'required|date|after_or_equal:today',
            'notes'       => 'nullable|string|max:500',
        ]);

        $existing = Rental::where('house_id', $house->id)
            ->where('tenant_id', Auth::id())
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($existing) {
            return back()->with('error', 'You already have an active rental request for this house.');
        }

        Rental::create([
            'house_id'     => $house->id,
            'tenant_id'    => Auth::id(),
            'rental_date'  => $validated['rental_date'],
            'monthly_rent' => $house->price,
            'status'       => 'pending',
            'notes'        => $validated['notes'] ?? null,
        ]);

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
            'message' => 'nullable|string|max:500',
        ]);

        $inspectionCompleted = Inspection::where('tenant_id', Auth::id())
            ->where('house_id', $rental->house_id)
            ->where('status', 'completed')
            ->exists();

        if (! $inspectionCompleted) {
            return back()->with('error', 'Inspection must be completed before you can make this decision.');
        }

        if ($rental->lease_status !== 'not_requested') {
            return back()->with('error', 'Stay decision was already submitted for this rental.');
        }

        if ($validated['decision'] === 'yes') {
            $note = 'Tenant confirmed stay after inspection on ' . now()->format('Y-m-d H:i');
            if (!empty($validated['message'])) {
                $note .= ' | Message: ' . trim($validated['message']);
            }

            $rental->update([
                'lease_status' => 'requested',
                'lease_requested_at' => now(),
                'notes' => trim(($rental->notes ? $rental->notes . PHP_EOL : '') . $note),
            ]);

            if ($rental->house && $rental->house->owner) {
                $rental->house->owner->notify(new WorkflowStatusNotification(
                    'tenant_confirmed_stay',
                    'Tenant Confirmed Stay',
                    'Tenant confirmed stay after inspection for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '.'
                ));
            }

            return back()->with('success', 'Thanks. Your confirmation was sent and the owner has been notified to proceed.');
        }

        $note = 'Tenant declined stay after inspection on ' . now()->format('Y-m-d H:i');
        if (!empty($validated['message'])) {
            $note .= ' | Message: ' . trim($validated['message']);
        }

        $rental->update([
            'status' => 'cancelled',
            'lease_status' => 'rejected',
            'lease_reviewed_at' => now(),
            'notes' => trim(($rental->notes ? $rental->notes . PHP_EOL : '') . $note),
        ]);

        return back()->with('success', 'Your decision has been recorded. This rental request is now closed.');
    }

    public function downloadLease(LeaseAgreement $leaseAgreement)
    {
        $rental = $leaseAgreement->rental;

        if (! $rental || (int) $rental->tenant_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $inspectionCompleted = Inspection::where('tenant_id', Auth::id())
            ->where('house_id', $rental->house_id)
            ->where('status', 'completed')
            ->exists();

        if (! $inspectionCompleted || ! in_array($rental->lease_status, ['requested', 'approved'], true)) {
            return back()->with('error', 'Lease is available only after inspection completion and tenant confirmation.');
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

        if ($rental->status !== 'active') {
            return back()->with('error', 'Payment is only available after the owner accepts your request.');
        }

        $inspectionCompleted = Inspection::where('tenant_id', Auth::id())
            ->where('house_id', $rental->house_id)
            ->where('status', 'completed')
            ->exists();

        if (! $inspectionCompleted) {
            return back()->with('error', 'Inspection must be completed before payment submission.');
        }

        if ($rental->lease_status === 'not_requested') {
            return back()->with('error', 'Please confirm Yes/No after inspection before proceeding with payment.');
        }

        if ($rental->lease_status !== 'requested') {
            return back()->with('error', 'Payment submission is not available at this stage.');
        }

        if (! $rental->leaseAgreement) {
            return back()->with('error', 'Owner must upload the lease agreement before payment.');
        }

        $validated = $request->validate([
            'transaction_id' => 'nullable|string|max:120|required_without:payment_proof',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120|required_without:transaction_id',
            'notes' => 'nullable|string|max:500',
        ]);

        $alreadyPaid = Payment::where('rental_id', $rental->id)
            ->whereIn('verification_status', ['pending', 'verified'])
            ->exists();

        if ($alreadyPaid) {
            return back()->with('error', 'A payment already exists for this rental and is awaiting/has completed verification.');
        }

        $amount = (float) $rental->monthly_rent;
        $commissionRate = $this->commissionRate();
        $commissionAmount = round($amount * ($commissionRate / 100), 2);
        $ownerShare = round($amount - $commissionAmount, 2);

        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payments/proofs', 'public');
        }

        $payment = Payment::create([
            'tenant_id' => Auth::id(),
            'rental_id' => $rental->id,
            'amount' => $amount,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'owner_share_amount' => $ownerShare,
            'payment_date' => now()->toDateString(),
            'transaction_id' => $validated['transaction_id'] ?? null,
            'payment_proof_path' => $paymentProofPath,
            'status' => 'pending',
            'verification_status' => 'pending',
            'notes' => $validated['notes'] ?? 'Tenant submitted payment proof from dashboard.',
        ]);

        if ($rental->house && $rental->house->owner_id) {
            AdminCommissionTransaction::updateOrCreate(
                ['payment_id' => $payment->id],
                [
                    'tenant_id' => Auth::id(),
                    'owner_id' => $rental->house->owner_id,
                    'property_id' => $rental->house_id,
                    'payment_amount' => $amount,
                    'admin_commission' => $commissionAmount,
                    'owner_share' => $ownerShare,
                    'transaction_date' => now()->toDateString(),
                    'status' => 'pending',
                    'notes' => 'Awaiting payment verification.',
                ]
            );
        }

        if ($rental->house && $rental->house->owner) {
            $rental->house->owner->notify(new WorkflowStatusNotification(
                'payment_submitted',
                'Payment Submitted',
                'Tenant submitted advance payment proof for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '. Waiting for admin verification.'
            ));
        }

        User::where('role', 'admin')->get()->each(function ($admin) use ($rental) {
            $admin->notify(new WorkflowStatusNotification(
                'payment_pending_verification',
                'Payment Verification Needed',
                'A payment proof is waiting verification for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '.'
            ));
        });

        return back()->with('success', 'Payment proof submitted. Admin verification is required before final approval.');
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
            'move_out_date' => 'nullable|date|after_or_equal:today',
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
            'reason' => $validated['reason'],
            'move_out_date' => $validated['move_out_date'] ?? null,
            'status' => 'requested',
        ]);

        if ($rental->house && $rental->house->owner) {
            $rental->house->owner->notify(new WorkflowStatusNotification(
                'move_out_requested',
                'Move-Out Requested',
                'Tenant requested move-out for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '.'
            ));
        }

        User::where('role', 'admin')->get()->each(function ($admin) use ($rental) {
            $admin->notify(new WorkflowStatusNotification(
                'move_out_requested',
                'Move-Out Requested',
                'A tenant requested move-out for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '.'
            ));
        });

        $rental->update([
            'notes' => trim(($rental->notes ? $rental->notes . PHP_EOL : '')
                . 'Move-out requested on ' . now()->format('Y-m-d H:i') . ' (Request #' . $moveOutRequest->id . ').'),
        ]);

        return back()->with('success', 'Move-out request submitted successfully.');
    }
}
