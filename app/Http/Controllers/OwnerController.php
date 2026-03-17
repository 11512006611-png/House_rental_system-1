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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        // Stat cards
        $totalProperties     = House::where('owner_id', Auth::id())->count();
        $availableProperties = House::where('owner_id', Auth::id())->where('status', 'available')->count();
        $rentedProperties    = House::where('owner_id', Auth::id())->where('status', 'rented')->count();
        $pendingProperties   = House::where('owner_id', Auth::id())->where('status', 'pending')->count();

        $totalActiveTenants  = Rental::whereIn('house_id', $houseIds)
            ->where('status', 'active')
            ->distinct('tenant_id')
            ->count('tenant_id');

        $totalRevenue = Payment::whereHas('rental', fn($q) => $q->whereIn('house_id', $houseIds))
            ->where('status', 'paid')
            ->sum('amount');

        $pendingAmount = Payment::whereHas('rental', fn($q) => $q->whereIn('house_id', $houseIds))
            ->where('status', 'pending')
            ->sum('amount');

        $overdueAmount = Payment::whereHas('rental', fn($q) => $q->whereIn('house_id', $houseIds))
            ->where('status', 'overdue')
            ->sum('amount');

        // 6-month revenue chart data
        $chartData = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month   = now()->subMonths($i);
            $revenue = Payment::whereHas('rental', fn($q) => $q->whereIn('house_id', $houseIds))
                ->where('status', 'paid')
                ->whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->sum('amount');
            $chartData->push(['label' => $month->format('M Y'), 'revenue' => (float) $revenue]);
        }

        // Recent payments
        $recentPayments = Payment::with(['tenant', 'rental.house'])
            ->whereHas('rental', fn($q) => $q->whereIn('house_id', $houseIds))
            ->latest('payment_date')
            ->take(6)
            ->get();

        // Recent active tenants
        $recentTenants = Rental::with(['tenant', 'house'])
            ->whereIn('house_id', $houseIds)
            ->where('status', 'active')
            ->latest()
            ->take(5)
            ->get();

        $latestRentalRequests = Rental::with(['tenant', 'house'])
            ->whereIn('house_id', $houseIds)
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // Property list with active rental info
        $properties = House::where('owner_id', Auth::id())
            ->with(['rentals' => fn($q) => $q->where('status', 'active')->with('tenant')])
            ->latest()
            ->take(6)
            ->get();

        return view('owner.dashboard', compact(
            'owner',
            'totalProperties', 'availableProperties', 'rentedProperties', 'pendingProperties',
            'totalActiveTenants', 'totalRevenue', 'pendingAmount', 'overdueAmount',
            'chartData', 'recentPayments', 'recentTenants', 'latestRentalRequests', 'properties'
        ));
    }

    // ── Properties ────────────────────────────────────────────────────────────

    public function properties(Request $request)
    {
        $query = House::where('owner_id', Auth::id())
            ->with(['locationModel', 'rentals' => fn($q) => $q->where('status', 'active')->with('tenant')]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $houses          = $query->latest()->paginate(10)->withQueryString();
        $totalProperties = House::where('owner_id', Auth::id())->count();
        $availableCount  = House::where('owner_id', Auth::id())->where('status', 'available')->count();
        $rentedCount     = House::where('owner_id', Auth::id())->where('status', 'rented')->count();
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
            $query->whereYear('payment_date', $year)->whereMonth('payment_date', $month);
        }
        if ($request->filled('search')) {
            $query->whereHas('tenant', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        $payments      = $query->latest('payment_date')->paginate(15);
        $payments->appends($request->query());
        $totalRevenue  = Payment::whereHas('rental', fn($q) => $q->whereIn('house_id', $houseIds))->where('status', 'paid')->sum('amount');
        $totalPending  = Payment::whereHas('rental', fn($q) => $q->whereIn('house_id', $houseIds))->where('status', 'pending')->sum('amount');
        $totalOverdue  = Payment::whereHas('rental', fn($q) => $q->whereIn('house_id', $houseIds))->where('status', 'overdue')->sum('amount');
        $totalCount    = Payment::whereHas('rental', fn($q) => $q->whereIn('house_id', $houseIds))->count();

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

        $rental->update(['status' => 'active']);
        $rental->house->update(['status' => 'rented']);

        return back()->with('success', 'Rental request accepted. Tenant can now proceed with payment.');
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

        return back()->with('success', 'Rental request rejected.');
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

        $rental->update([
            'lease_status' => 'approved',
            'lease_reviewed_at' => now(),
        ]);

        if ($rental->tenant) {
            $rental->tenant->notify(new WorkflowStatusNotification(
                'lease_approved',
                'Lease Approved',
                'Your lease for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . ' has been approved by the owner.'
            ));
        }

        return back()->with('success', 'Lease agreement approved successfully.');
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

        if ($rental->status !== 'active') {
            return back()->with('error', 'Lease can only be uploaded for active rentals.');
        }

        if ($rental->lease_status !== 'requested') {
            return back()->with('error', 'Tenant must confirm stay before lease upload.');
        }

        $validated = $request->validate([
            'lease_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $storedPath = $request->file('lease_file')->store('lease-agreements', 'public');

        $rental->leaseAgreement()->updateOrCreate(
            ['rental_id' => $rental->id],
            [
                'owner_id' => Auth::id(),
                'file_path' => $storedPath,
                'original_name' => $request->file('lease_file')->getClientOriginalName(),
                'uploaded_at' => now(),
            ]
        );

        if ($rental->tenant) {
            $rental->tenant->notify(new WorkflowStatusNotification(
                'lease_sent',
                'Lease Sent',
                'Owner uploaded lease agreement for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '. Please review and proceed with payment.'
            ));
        }

        return back()->with('success', 'Lease agreement uploaded and tenant has been notified.');
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

    // ── Bank Details Management ───────────────────────────────────────────────

    public function bankDetails()
    {
        $owner = Auth::user();

        return view('owner.bank-details', compact('owner'));
    }

    public function updateBankDetails(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder_name' => 'required|string|max:100',
            'advance_payment_amount' => 'required|numeric|min:0',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->bank_name = $validated['bank_name'];
        $user->account_number = $validated['account_number'];
        $user->account_holder_name = $validated['account_holder_name'];
        $user->advance_payment_amount = $validated['advance_payment_amount'];
        $user->save();

        return back()->with('success', 'Bank details updated successfully. Tenants will see these details in lease agreements.');
    }

    // ── Digital Lease Agreement Generation ────────────────────────────────────

    public function generateAndSendLease(Request $request, Rental $rental)
    {
        if ((int) $rental->house->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($rental->status !== 'active') {
            return back()->with('error', 'Lease can only be generated for active rentals.');
        }

        if ($rental->lease_status !== 'not_requested') {
            return back()->with('error', 'Lease has already been requested for this rental.');
        }

        $validated = $request->validate([
            'advance_amount' => 'required|numeric|min:0',
            'lease_end_date' => 'required|date|after:today',
        ]);

        // Update rental end date
        $rental->update([
            'end_date' => $validated['lease_end_date'],
            'lease_status' => 'requested',
            'lease_requested_at' => now(),
        ]);

        // Store the advance amount on owner profile for lease generation
        /** @var User $user */
        $user = Auth::user();
        $user->advance_payment_amount = $validated['advance_amount'];
        $user->save();

        // Generate digital lease
        $leaseHtml = LeaseAgreementService::generateLeaseHTML($rental, $validated['advance_amount']);
        $filename = "lease_rental_{$rental->id}_{$rental->tenant_id}_" . now()->timestamp . ".html";
        Storage::disk('public')->put("leases/{$filename}", $leaseHtml);

        // Create lease agreement record
        $rental->leaseAgreement()->create([
            'owner_id' => Auth::id(),
            'file_path' => "leases/{$filename}",
            'original_name' => "Lease_Agreement_{$rental->id}.html",
            'uploaded_at' => now(),
        ]);

        // Notify tenant
        if ($rental->tenant) {
            $rental->tenant->notify(new WorkflowStatusNotification(
                'lease_sent',
                'Lease Agreement Ready',
                'Your lease agreement for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . ' has been generated. Please review it and proceed with payment.'
            ));
        }

        return back()->with('success', 'Digital lease agreement generated and sent to tenant successfully.');
    }

    public function viewGenerateLease(Rental $rental)
    {
        if ((int) $rental->house->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($rental->status !== 'active' && $rental->status !== 'pending') {
            abort(403, 'Can only generate lease for active or pending rentals.');
        }

        return view('owner.generate-lease', compact('rental'));
    }

    public function viewLeasePreview(Rental $rental)
    {
        if ((int) $rental->house->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $advanceAmount = $rental->house->owner->advance_payment_amount ?? $rental->monthly_rent;
        $leaseHtml = LeaseAgreementService::generateLeaseHTML($rental, $advanceAmount);

        return view('owner.lease-preview', ['leaseHtml' => $leaseHtml]);
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
        $approvedCount = Inspection::whereIn('house_id', $houseIds)->where('status', 'approved')->count();
        $completedCount = Inspection::whereIn('house_id', $houseIds)->where('status', 'completed')->count();

        return view('owner.inspections', compact('inspections', 'pendingCount', 'approvedCount', 'completedCount'));
    }

    public function approveInspection(Request $request, Inspection $inspection)
    {
        if ((int) $inspection->house->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($inspection->status !== 'pending') {
            return back()->with('error', 'Only pending inspections can be approved.');
        }

        $validated = $request->validate([
            'scheduled_at' => 'required|date_format:Y-m-d H:i',
            'owner_notes' => 'nullable|string|max:500',
        ]);

        $inspection->update([
            'status' => 'approved',
            'scheduled_at' => $validated['scheduled_at'],
            'owner_notes' => $validated['owner_notes'] ?? null,
        ]);

        if ($inspection->tenant) {
            $inspection->tenant->notify(new WorkflowStatusNotification(
                'inspection_approved',
                'Inspection Approved',
                'Your inspection request for ' . ($inspection->house->title ?? ('Property #' . $inspection->house_id)) . ' has been approved for ' . date('d M Y, g:i A', strtotime($validated['scheduled_at'])) . '.'
            ));
        }

        return back()->with('success', 'Inspection approved and tenant has been notified.');
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

        if ($inspection->status !== 'approved') {
            return back()->with('error', 'Only approved inspections can be marked as completed.');
        }

        $inspection->update(['status' => 'completed']);

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
        // Owner can view and comment on payment, but admin verifies
        if ((int) $payment->rental->house->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // For now, owner can mark as verified temporarily (admin should do final verification)
        // This is a workflow step before admin final verification

        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
            'notes' => 'nullable|string|max:500',
        ]);

        // Only update notes for owner review
        $payment->update([
            'notes' => ($payment->notes ? $payment->notes . ' | Owner: ' : 'Owner: ') . ($validated['notes'] ?? 'No additional notes'),
        ]);

        return back()->with('success', 'Payment review saved. Admin will finalize verification.');
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
}
