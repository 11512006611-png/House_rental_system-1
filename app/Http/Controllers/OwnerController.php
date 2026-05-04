<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Inspection;
use App\Models\MoveOutRequest;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\User;
use App\Notifications\WorkflowStatusNotification;
use App\Services\LeaseAgreementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class OwnerController extends Controller
{
    // ── Helpers ───────────────────────────────────────────────────────────────

    private function houseIds()
    {
        return House::where('owner_id', Auth::id())->pluck('id');
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $owner   = Auth::user();
        $houseIds = $this->houseIds();

        // Properties list with active rental info
        $properties = House::where('owner_id', Auth::id())
            ->with(['locationModel', 'rentals' => fn($q) => $q->where('status', 'active')->with('tenant')])
            ->latest()
            ->get();

        // Monthly payments (recurring rent payments - typically equal to monthly rent)
        $monthlyPayments = Payment::with(['tenant', 'rental.house'])
            ->whereHas('rental', function($q) use ($houseIds) {
                $q->whereIn('house_id', $houseIds);
            })
            ->whereIn('payment_type', ['monthly_rent', 'first_month_rent'])
            ->where('status', 'paid')
            ->latest('payment_date')
            ->take(10)
            ->get();

        $recentNotifications = DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $owner->id)
            ->latest()
            ->take(5)
            ->get();

        return view('owner.dashboard', compact(
            'owner', 'properties', 'monthlyPayments', 'recentNotifications'
        ));
    }

    // ── Properties ────────────────────────────────────────────────────────────

    public function properties(Request $request)
    {
        $query = House::where('owner_id', Auth::id())
            ->with(['locationModel', 'rentals' => fn($q) => $q->where('status', 'active')->with('tenant')]);

        if ($request->filled('status')) {
            if ($request->status === 'rented') {
                $query->whereHas('rentals', fn($q) => $q->where('status', 'active'));
            } elseif ($request->status === 'available') {
                $query->whereNotIn('status', ['pending', 'rejected'])
                    ->whereDoesntHave('rentals', fn($q) => $q->where('status', 'active'));
            } else {
                $query->where('status', $request->status);
            }
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $houses          = $query->latest()->paginate(10)->withQueryString();
        $totalProperties = House::where('owner_id', Auth::id())->count();
        $availableCount  = House::where('owner_id', Auth::id())
            ->whereNotIn('status', ['pending', 'rejected'])
            ->whereDoesntHave('rentals', fn($q) => $q->where('status', 'active'))
            ->count();
        $rentedCount     = House::where('owner_id', Auth::id())
            ->whereHas('rentals', fn($q) => $q->where('status', 'active'))
            ->count();
        $pendingCount    = House::where('owner_id', Auth::id())->where('status', 'pending')->count();

        return view('owner.properties', compact(
            'houses', 'totalProperties', 'availableCount', 'rentedCount', 'pendingCount'
        ));
    }

    // ── Tenants ───────────────────────────────────────────────────────────────

    public function tenants(Request $request)
    {
        $houseIds = $this->houseIds();

        $query = Rental::with(['tenant', 'house', 'payments', 'leaseAgreement', 'moveOutRequests'])
            ->whereIn('house_id', $houseIds);

        if ($request->boolean('move_out')) {
            $query->whereHas('moveOutRequests');
        }

        if ($request->boolean('lease_queue')) {
            $query->where('status', 'active')
                ->where('lease_status', 'requested')
                ->whereDoesntHave('leaseAgreement');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->whereHas('tenant', fn($q) => $q
                ->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
            );
        }

        $rentals      = $query->latest()->paginate(12)->withQueryString();
        $totalActive  = Rental::whereIn('house_id', $houseIds)->where('status', 'active')->count();
        $totalPending = Rental::whereIn('house_id', $houseIds)->where('status', 'pending')->count();
        $totalExpired = Rental::whereIn('house_id', $houseIds)->where('status', 'expired')->count();

        return view('owner.tenants', compact('rentals', 'totalActive', 'totalPending', 'totalExpired'));
    }

    // ── Payments ──────────────────────────────────────────────────────────────

    public function payments(Request $request)
    {
        $houseIds = $this->houseIds();

        $query = Payment::with(['tenant', 'rental.house'])
            ->whereHas('rental', fn($q) => $q->whereIn('house_id', $houseIds));

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->month);
            $query->whereYear(DB::raw('COALESCE(billing_month, payment_date)'), $year)
                ->whereMonth(DB::raw('COALESCE(billing_month, payment_date)'), $month);
        }
        if ($request->filled('search')) {
            $query->whereHas('tenant', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        $payments      = $query->latest('payment_date')->paginate(15);
        $payments->appends($request->query());
        $rentQueryBase = Payment::whereHas('rental', fn($q) => $q->whereIn('house_id', $houseIds))
            ->whereIn('payment_type', ['monthly_rent', 'first_month_rent']);

        $totalRevenue  = (clone $rentQueryBase)->where('status', 'paid')->sum('amount');
        $totalPending  = (clone $rentQueryBase)->where('status', 'pending')->sum('amount');
        $totalOverdue  = (clone $rentQueryBase)->where('status', 'overdue')->sum('amount');
        $totalCount    = (clone $rentQueryBase)->count();

        return view('owner.payments', compact(
            'payments', 'totalRevenue', 'totalPending', 'totalOverdue', 'totalCount'
        ));
    }

    public function acceptRentalRequest(Rental $rental)
    {
        if ((int) $rental->house->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($rental->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be accepted.');
        }

        $hasActive = Rental::where('house_id', $rental->house_id)
            ->where('status', 'active')
            ->where('id', '!=', $rental->id)
            ->exists();

        if ($hasActive) {
            return back()->with('error', 'This property already has an active rental.');
        }

        $rental->update([
            'status' => 'active',
            'lease_status' => $rental->lease_status ?: 'not_requested',
        ]);
        $rental->house->update(['status' => 'rented']);

        // Notify tenant of acceptance
        if ($rental->tenant) {
            $rental->tenant->notify(new WorkflowStatusNotification(
                'rental_accepted_by_owner',
                'Rental Request Accepted',
                'Your rental request for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . ' has been accepted. Please make your payment from the Tenant Dashboard using mBoB, mPay, BDBL, or Cash details.'
            ));
        }

        return back()->with('success', 'Rental request accepted. Tenant has been notified and can now proceed with payment.');
    }

    public function rejectRentalRequest(Rental $rental)
    {
        if ((int) $rental->house->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($rental->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        $rental->update(['status' => 'cancelled']);

        // Notify tenant of rejection
        if ($rental->tenant) {
            $rental->tenant->notify(new WorkflowStatusNotification(
                'rental_rejected_by_owner',
                'Rental Request Declined',
                'Unfortunately, your rental request for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . ' has been declined by the owner. Please feel free to browse other properties.'
            ));
        }

        return back()->with('success', 'Rental request rejected. Tenant has been notified.');
    }

    public function approveLease(Rental $rental)
    {
        if ((int) $rental->house->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (! $rental->leaseAgreement) {
            return back()->with('error', 'Upload lease agreement file before approving.');
        }

        $verifiedPaymentExists = Payment::where('rental_id', $rental->id)
            ->where('verification_status', 'verified')
            ->exists();

        if (! $verifiedPaymentExists) {
            return back()->with('error', 'Payment must be verified by admin before final lease approval.');
        }

        if ($rental->lease_status !== 'requested') {
            return back()->with('error', 'This lease agreement is not pending approval.');
        }

        if ($rental->leaseAgreement->owner_signed_at) {
            return back()->with('error', 'You have already approved this agreement.');
        }

        $rental->leaseAgreement->update([
            'owner_signature_name' => Auth::user()->name,
            'owner_signed_at' => now(),
        ]);

        if ($rental->leaseAgreement->tenant_signed_at) {
            $rental->update([
                'lease_status' => 'approved',
                'lease_reviewed_at' => now(),
            ]);
        }

        LeaseAgreementService::regeneratePdf($rental->leaseAgreement->fresh());

        if ($rental->tenant) {
            $rental->tenant->notify(new WorkflowStatusNotification(
                'agreement_approved_by_owner',
                'Agreement Approved by Owner',
                'Owner approved the agreement for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '. '
                . ($rental->lease_status === 'approved' ? 'The lease process is now complete.' : 'Please accept the agreement to complete the process.')
            ));
        }

        return back()->with('success', 'Agreement approved digitally.');
    }

    public function rejectLease(Rental $rental)
    {
        if ((int) $rental->house->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($rental->lease_status !== 'requested') {
            return back()->with('error', 'This lease agreement is not pending approval.');
        }

        $rental->update([
            'lease_status' => 'rejected',
            'lease_reviewed_at' => now(),
        ]);

        if ($rental->tenant) {
            $rental->tenant->notify(new WorkflowStatusNotification(
                'lease_rejected',
                'Lease Rejected',
                'Your lease for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . ' was rejected by the owner.'
            ));
        }

        return back()->with('success', 'Lease agreement rejected. Tenant has been notified on dashboard.');
    }

    public function uploadLeaseAgreement(Request $request, Rental $rental)
    {
        if ((int) $rental->house->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return back()->with('error', 'Lease upload is handled by admin after tenant confirms stay.');
    }

    public function approveMoveOutRequest(Request $request, MoveOutRequest $moveOutRequest)
    {
        if ((int) $moveOutRequest->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($moveOutRequest->status !== 'requested') {
            return back()->with('error', 'Only requested move-out can be approved.');
        }

        $validated = $request->validate([
            'owner_note' => 'nullable|string|max:1000',
        ]);

        $moveOutRequest->update([
            'status' => 'approved',
            'owner_note' => $validated['owner_note'] ?? null,
            'reviewed_at' => now(),
        ]);

        if ($moveOutRequest->tenant) {
            $moveOutRequest->tenant->notify(new WorkflowStatusNotification(
                'move_out_approved',
                'Move-Out Approved',
                'Owner approved your move-out request for ' . ($moveOutRequest->house->title ?? ('Property #' . $moveOutRequest->house_id)) . '.'
            ));
        }

        User::where('role', 'admin')->get()->each(function ($admin) use ($moveOutRequest) {
            $admin->notify(new WorkflowStatusNotification(
                'move_out_approved',
                'Move-Out Approved',
                'Move-out request was approved for ' . ($moveOutRequest->house->title ?? ('Property #' . $moveOutRequest->house_id)) . '.'
            ));
        });

        return back()->with('success', 'Move-out request approved.');
    }

    public function completeMoveOutRequest(MoveOutRequest $moveOutRequest)
    {
        if ((int) $moveOutRequest->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (! in_array($moveOutRequest->status, ['requested', 'approved'], true)) {
            return back()->with('error', 'This move-out request cannot be completed.');
        }

        $moveOutRequest->update([
            'status' => 'completed',
            'completed_at' => now(),
            'reviewed_at' => now(),
        ]);

        $rental = $moveOutRequest->rental;
        if ($rental) {
            $rental->update([
                'status' => 'expired',
                'end_date' => $moveOutRequest->move_out_date ?? now()->toDateString(),
            ]);

            if ($rental->house) {
                $rental->house->update(['status' => 'available']);
            }
        }

        if ($moveOutRequest->tenant) {
            $moveOutRequest->tenant->notify(new WorkflowStatusNotification(
                'move_out_completed',
                'Move-Out Completed',
                'Your move-out request is completed for ' . ($moveOutRequest->house->title ?? ('Property #' . $moveOutRequest->house_id)) . '.'
            ));
        }

        return back()->with('success', 'Move-out marked as completed and rental closed.');
    }

    // ── Bank details (owner managed) ──────────────────────────────────────

    public function bankDetails()
    {
        /** @var User $owner */
        $owner = User::query()->findOrFail(Auth::id());

        $decryptedAccount = '';
        if (! empty($owner->account_number)) {
            try {
                $decryptedAccount = Crypt::decryptString($owner->account_number);
            } catch (\Throwable $e) {
                // If decryption fails assume value is plaintext or malformed; show raw digits
                $decryptedAccount = (string) $owner->account_number;
            }
        }

        $digitsOnly = preg_replace('/\D+/', '', $decryptedAccount);
        $last4 = strlen($digitsOnly) >= 4 ? substr($digitsOnly, -4) : $digitsOnly;
        $masked = $last4 ? '**** **** ' . $last4 : null;

        return view('owner.bank-details', compact('owner', 'masked'));
    }

    public function updateBankDetails(Request $request)
    {
        /** @var User $owner */
        $owner = User::query()->findOrFail(Auth::id());

        $bankOptions = array_keys(\App\Enums\Bank::getList());

        $validated = $request->validate([
            'bank_name' => ['nullable', 'string', 'max:50'],
            'account_holder_name' => ['required', 'string', 'max:150'],
            'account_number' => ['nullable', 'string', 'min:4', 'max:100'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        $data = [
            'bank_name' => $validated['bank_name'] ?? $owner->bank_name,
            'account_holder_name' => trim((string) $validated['account_holder_name']),
            'phone' => $validated['phone'] ?? $owner->phone,
        ];

        // Only update account_number if provided
        if (! empty($validated['account_number'])) {
            try {
                $encrypted = Crypt::encryptString(trim((string) $validated['account_number']));
                $data['account_number'] = $encrypted;
            } catch (\Throwable $e) {
                Log::warning('Failed to encrypt owner account number', ['owner_id' => $owner->id, 'error' => $e->getMessage()]);
                return back()->withErrors(['account_number' => 'Failed to secure account number. Please try again.']);
            }
        }

        $owner->update($data + ['bank_details_updated_at' => now()]);

        // Log the update (masking stored value for log)
        $maskedForLog = null;
        if (! empty($validated['account_number'])) {
            $digits = preg_replace('/\D+/', '', $validated['account_number']);
            $last4log = strlen($digits) >= 4 ? substr($digits, -4) : $digits;
            $maskedForLog = $last4log ? '**** **** ' . $last4log : null;
        }
        Log::info('Owner updated bank details', ['owner_id' => $owner->id, 'bank_name' => $data['bank_name'], 'account_holder' => $data['account_holder_name'], 'account_mask' => $maskedForLog]);

        $owner->notify(new WorkflowStatusNotification(
            'bank_details_updated',
            'Bank Details Updated',
            'Your bank details were updated successfully.'
        ));

        return back()->with('success', 'Bank details updated successfully.');
    }

    public function rejectMoveOutRequest(Request $request, MoveOutRequest $moveOutRequest)
    {
        if ((int) $moveOutRequest->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (! in_array($moveOutRequest->status, ['requested', 'approved'], true)) {
            return back()->with('error', 'This move-out request cannot be rejected.');
        }

        $validated = $request->validate([
            'owner_note' => 'required|string|max:1000',
        ]);

        $moveOutRequest->update([
            'status' => 'rejected',
            'owner_note' => $validated['owner_note'],
            'reviewed_at' => now(),
        ]);

        if ($moveOutRequest->tenant) {
            $moveOutRequest->tenant->notify(new WorkflowStatusNotification(
                'move_out_rejected',
                'Move-Out Rejected',
                'Your move-out request was rejected for ' . ($moveOutRequest->house->title ?? ('Property #' . $moveOutRequest->house_id)) . '. Please review owner note.'
            ));
        }

        return back()->with('success', 'Move-out request rejected.');
    }

    // ── Digital Lease Agreement Generation ────────────────────────────────────

    public function generateAndSendLease(Request $request, Rental $rental)
    {
        return back()->with('error', 'Lease agreement upload is handled by admin after the tenant selects Stay.');
    }

    public function viewGenerateLease(Rental $rental)
    {
        return redirect()->route('owner.tenants')
            ->with('error', 'Lease agreement upload is handled by admin after tenant stay confirmation.');
    }

    public function viewLeasePreview(Rental $rental)
    {
        return redirect()->route('owner.tenants')
            ->with('error', 'Lease preview is disabled because lease is uploaded by admin.');
    }

    // ── Inspection Management ──────────────────────────────────────────────────

    public function inspections(Request $request)
    {
        $houseIds = $this->houseIds();

        $query = Inspection::with(['tenant', 'house'])
            ->whereIn('house_id', $houseIds);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->whereHas('tenant', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        $inspections = $query->latest()->paginate(10);
        $pendingCount = Inspection::whereIn('house_id', $houseIds)->where('status', 'pending')->count();
        $confirmedCount = Inspection::whereIn('house_id', $houseIds)->where('status', 'confirmed')->count();
        $rescheduledCount = Inspection::whereIn('house_id', $houseIds)->where('status', 'rescheduled')->count();
        $rejectedCount = Inspection::whereIn('house_id', $houseIds)->where('status', 'rejected')->count();

        return view('owner.inspections', compact('inspections', 'pendingCount', 'confirmedCount', 'rescheduledCount', 'rejectedCount'));
    }

    public function approveInspection(Request $request, Inspection $inspection)
    {
        if ((int) $inspection->house->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($inspection->status !== 'pending') {
            return back()->with('error', 'Only pending inspections can be confirmed.');
        }

        $validated = $request->validate([
            'scheduled_at' => 'required|date',
            'owner_notes' => 'nullable|string|max:500',
        ]);

        $scheduledAt = Carbon::parse($validated['scheduled_at']);

        $inspection->update([
            'status' => 'confirmed',
            'scheduled_at' => $scheduledAt,
            'owner_notes' => $validated['owner_notes'] ?? null,
        ]);

        if ($inspection->tenant) {
            $inspection->tenant->notify(new WorkflowStatusNotification(
                'inspection_confirmed',
                'Inspection Confirmed',
                'Your inspection request for ' . ($inspection->house->title ?? ('Property #' . $inspection->house_id)) . ' has been confirmed for ' . $scheduledAt->format('d M Y, g:i A') . '.'
            ));
        }

        return back()->with('success', 'Inspection confirmed and tenant has been notified.');
    }

    public function rejectInspection(Request $request, Inspection $inspection)
    {
        if ((int) $inspection->house->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($inspection->status !== 'pending') {
            return back()->with('error', 'Only pending inspections can be rejected.');
        }

        $validated = $request->validate([
            'owner_notes' => 'required|string|max:500',
        ]);

        $inspection->update([
            'status' => 'rejected',
            'owner_notes' => $validated['owner_notes'],
            'rejection_reason' => $validated['owner_notes'],
        ]);

        if ($inspection->tenant) {
            $inspection->tenant->notify(new WorkflowStatusNotification(
                'inspection_rejected',
                'Inspection Declined',
                'Your inspection request for ' . ($inspection->house->title ?? ('Property #' . $inspection->house_id)) . ' was declined. Reason: ' . $validated['owner_notes']
            ));
        }

        return back()->with('success', 'Inspection rejected and tenant has been notified.');
    }

    public function markInspectionCompleted(Inspection $inspection)
    {
        if ((int) $inspection->house->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($inspection->status !== 'confirmed') {
            return back()->with('error', 'Only confirmed inspections can be marked as completed.');
        }

        $inspection->update(['status' => 'confirmed']);

        if ($inspection->tenant) {
            $inspection->tenant->notify(new WorkflowStatusNotification(
                'inspection_completed',
                'Inspection Completed',
                'Your inspection for ' . ($inspection->house->title ?? ('Property #' . $inspection->house_id)) . ' has been marked as complete by the owner.'
            ));
        }

        return back()->with('success', 'Inspection marked as completed.');
    }

    // ── Payment Verification ───────────────────────────────────────────────────

    public function verifyPayment(Request $request, Payment $payment)
    {
        if ((int) $payment->rental->house->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($payment->verification_status === 'verified') {
            return back()->with('error', 'This advance payment is already marked as completed.');
        }

        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validated['status'] === 'verified') {
            $payment->update([
                'status' => 'paid',
                'verification_status' => 'verified',
                'verified_at' => now(),
                'notes' => trim(($payment->notes ? $payment->notes . ' | ' : '') . 'Owner confirmed advance payment as completed.' . (!empty($validated['notes']) ? ' Note: ' . $validated['notes'] : '')),
            ]);

            if ($payment->tenant) {
                $payment->tenant->notify(new WorkflowStatusNotification(
                    'advance_payment_completed',
                    'Advance Payment Completed',
                    'Owner confirmed your advance payment for ' . ($payment->rental->house->title ?? ('Property #' . $payment->rental->house_id)) . '. You can now shift to the place.'
                ));
            }

            return back()->with('success', 'Advance payment marked as complete. Tenant has been notified.');
        }

        $payment->update([
            'status' => 'overdue',
            'verification_status' => 'rejected',
            'notes' => trim(($payment->notes ? $payment->notes . ' | ' : '') . 'Owner rejected payment confirmation.' . (!empty($validated['notes']) ? ' Reason: ' . $validated['notes'] : '')),
        ]);

        if ($payment->tenant) {
            $payment->tenant->notify(new WorkflowStatusNotification(
                'advance_payment_rejected',
                'Advance Payment Rejected',
                'Owner rejected your advance payment for ' . ($payment->rental->house->title ?? ('Property #' . $payment->rental->house_id)) . '. Please submit valid payment details again.'
            ));
        }

        return back()->with('success', 'Payment marked as rejected. Tenant has been notified.');
    }

    public function paymentProofView(Payment $payment)
    {
        if ((int) $payment->rental->house->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$payment->payment_proof_path || !Storage::disk('public')->exists($payment->payment_proof_path)) {
            return back()->with('error', 'Payment proof file not found.');
        }

        return response()->download(storage_path('app/public/' . $payment->payment_proof_path));
    }

    public function clearNotifications()
    {
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Unauthorized action.');
        }

        $user->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications cleared.');
    }

    // ── Monthly Earnings ──────────────────────────────────────────────────────

    public function monthlyEarnings()
    {
        $owner = Auth::user();
        $currentMonth = now()->format('Y-m');

        // Get all settlements for this owner
        $settlements = \App\Models\MonthlySettlement::where('owner_id', $owner->id)
            ->orderBy('settlement_month', 'desc')
            ->get();

        // Current month data
        $currentMonthData = \App\Models\MonthlySettlement::calculateMonthlySettlement($owner->id, $currentMonth);

        // Recent payments for current month
        $recentPayments = Payment::whereHas('rental.house', function ($query) use ($owner) {
            $query->where('owner_id', $owner->id);
        })
        ->where('status', 'paid')
        ->whereYear('payment_date', now()->year)
        ->whereMonth('payment_date', now()->month)
        ->with(['rental.house', 'rental.tenant'])
        ->orderBy('payment_date', 'desc')
        ->take(10)
        ->get();

        return view('owner.earnings', compact('settlements', 'currentMonthData', 'recentPayments', 'currentMonth'));
    }
}
