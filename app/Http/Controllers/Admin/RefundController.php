<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\MoveOutRequest;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    public function index()
    {
        $refunds = Refund::with(['booking.house', 'tenant', 'moveOutRequest'])
            ->latest()
            ->paginate(20);

        $pendingMoveOutRequests = MoveOutRequest::with(['tenant', 'house'])
            ->whereIn('status', ['requested', 'approved'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.refunds.index', compact('refunds', 'pendingMoveOutRequests'));
    }

    public function create(MoveOutRequest $moveOutRequest)
    {
        $moveOutRequest->load(['rental.house', 'tenant']);

        $booking = $moveOutRequest->booking ?: Booking::with(['refund'])
            ->where('rental_id', $moveOutRequest->rental_id)
            ->first();

        abort_unless($booking, 404, 'Booking not found for this move-out request.');

        return view('admin.refunds.create', compact('moveOutRequest', 'booking'));
    }

    public function store(Request $request, MoveOutRequest $moveOutRequest)
    {
        $moveOutRequest->load(['rental.house', 'tenant']);

        $booking = $moveOutRequest->booking ?: Booking::where('rental_id', $moveOutRequest->rental_id)->first();
        abort_unless($booking, 404, 'Booking not found for this move-out request.');

        $validated = $request->validate([
            'damage_cost' => 'required|numeric|min:0',
            'pending_dues' => 'required|numeric|min:0',
            'inspection_notes' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
        ]);

        $verifiedHeldDeposit = (float) Payment::where('booking_id', $booking->id)
            ->where('payment_type', 'security_deposit')
            ->where('verification_status', 'verified')
            ->sum('amount');

        if ($verifiedHeldDeposit <= 0) {
            return back()->with('error', 'Refund cannot be processed until the security deposit payment is verified and held by admin.');
        }

        $alreadyRefunded = (float) Payment::where('booking_id', $booking->id)
            ->where('payment_type', 'refund')
            ->where('verification_status', 'verified')
            ->sum('amount');

        if ($alreadyRefunded > 0) {
            return back()->with('error', 'A verified refund already exists for this booking.');
        }

        $refundAmount = $this->calculateRefundAmount(
            $verifiedHeldDeposit,
            (float) $validated['damage_cost'],
            (float) $validated['pending_dues']
        );

        $refund = DB::transaction(function () use ($booking, $moveOutRequest, $validated, $refundAmount, $verifiedHeldDeposit) {
            $refund = Refund::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'move_out_request_id' => $moveOutRequest->id,
                    'house_id' => $booking->house_id,
                    'tenant_id' => $booking->tenant_id,
                    'processed_by_admin_id' => Auth::id(),
                    'security_deposit_amount' => $verifiedHeldDeposit,
                    'damage_cost' => (float) $validated['damage_cost'],
                    'pending_dues' => (float) $validated['pending_dues'],
                    'refund_amount' => $refundAmount,
                    'status' => 'processed',
                    'inspection_notes' => $validated['inspection_notes'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'processed_at' => now(),
                ]
            );

            Payment::create([
                'tenant_id' => $booking->tenant_id,
                'rental_id' => $booking->rental_id,
                'booking_id' => $booking->id,
                'amount' => $refundAmount,
                'rent_amount' => 0,
                'security_deposit_amount' => 0,
                'service_fee_rate' => 0,
                'service_fee_amount' => 0,
                'total_advance_amount' => 0,
                'commission_rate' => 0,
                'commission_amount' => 0,
                'owner_share_amount' => 0,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'payment_type' => 'refund',
                'held_by_admin' => false,
                'status' => 'paid',
                'verification_status' => 'verified',
                'verified_at' => now(),
                'notes' => 'Refund processed for booking #' . $booking->id,
            ]);

            $moveOutRequest->update([
                'status' => 'completed',
                'reviewed_at' => now(),
                'completed_at' => now(),
            ]);

            return $refund;
        });

        if ($booking->tenant) {
            $booking->tenant->notify(new \App\Notifications\WorkflowStatusNotification(
                'refund_processed',
                'Security Deposit Refund Processed',
                'Your refund for property ' . ($booking->house?->title ?? ('Booking #' . $booking->id)) . ' has been processed. Refund amount: Nu. ' . number_format($refund->refund_amount, 2)
            ));
        }

        return redirect()->route('admin.refunds.index')->with('success', 'Refund processed successfully.');
    }

    public function calculateRefundAmount(float $securityDeposit, float $damageCost, float $pendingDues): float
    {
        return max(0, round($securityDeposit - $damageCost - $pendingDues, 2));
    }
}
