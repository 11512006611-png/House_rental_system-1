<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Rental;
use App\Models\User;
use App\Notifications\WorkflowStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceRequestController extends Controller
{
    public function tenantIndex()
    {
        $admin = User::where('role', 'admin')->first();
        $tenantId = Auth::id();

        $activeRentals = Rental::with('house')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->get();

        $requests = MaintenanceRequest::with(['house', 'owner'])
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->paginate(10);

        $statusCounts = [
            'pending' => MaintenanceRequest::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
            'approved_for_repair' => MaintenanceRequest::where('tenant_id', $tenantId)->where('status', 'approved_for_repair')->count(),
            'under_repair' => MaintenanceRequest::where('tenant_id', $tenantId)->where('status', 'under_repair')->count(),
            'resolved' => MaintenanceRequest::where('tenant_id', $tenantId)->where('status', 'resolved')->count(),
        ];

        return view('tenant.maintenance', compact('activeRentals', 'requests', 'statusCounts', 'admin'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rental_id' => 'required|exists:rentals,id',
            'category' => 'required|in:water,electricity,plumbing,security,cleaning,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'description' => 'required|string|max:1500',
            'preferred_visit_date' => 'nullable|date|after_or_equal:today',
        ]);

        $rental = Rental::with(['house', 'house.owner'])
            ->where('id', $validated['rental_id'])
            ->where('tenant_id', Auth::id())
            ->where('status', 'active')
            ->first();

        if (! $rental || ! $rental->house || ! $rental->house->owner_id) {
            return back()->with('error', 'You can only request maintenance for your active rental property.');
        }

        $maintenanceRequest = MaintenanceRequest::create([
            'house_id' => $rental->house_id,
            'rental_id' => $rental->id,
            'tenant_id' => Auth::id(),
            'owner_id' => $rental->house->owner_id,
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'description' => $validated['description'],
            'preferred_visit_date' => $validated['preferred_visit_date'] ?? null,
            'status' => 'pending',
        ]);

        if ($rental->house->owner) {
            $rental->house->owner->notify(new WorkflowStatusNotification(
                'maintenance_requested',
                'New Maintenance Request',
                'Tenant ' . Auth::user()->name . ' reported a ' . ucfirst($maintenanceRequest->category) . ' issue for ' . ($rental->house->title ?? ('Property #' . $rental->house_id)) . '. Priority: ' . ucfirst($maintenanceRequest->priority) . '.'
            ));
        }

        return back()->with('success', 'Maintenance request submitted successfully. Owner has been notified.');
    }

    public function ownerIndex(Request $request)
    {
        $query = MaintenanceRequest::with(['tenant', 'house'])
            ->where('owner_id', Auth::id())
            ->orderByRaw("FIELD(status, 'pending', 'in_progress', 'resolved', 'rejected')")
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('tenant', function ($tenantQuery) use ($search) {
                    $tenantQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })->orWhereHas('house', function ($houseQuery) use ($search) {
                    $houseQuery->where('title', 'like', '%' . $search . '%');
                })->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $requests = $query->paginate(12)->withQueryString();

        $statusCounts = [
            'pending' => MaintenanceRequest::where('owner_id', Auth::id())->where('status', 'pending')->count(),
            'in_progress' => MaintenanceRequest::where('owner_id', Auth::id())->where('status', 'in_progress')->count(),
            'resolved' => MaintenanceRequest::where('owner_id', Auth::id())->where('status', 'resolved')->count(),
        ];

        return view('owner.maintenance', compact('requests', 'statusCounts'));
    }

    public function updateStatus(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        if ((int) $maintenanceRequest->owner_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:in_progress,resolved,rejected',
            'owner_response' => 'nullable|string|max:1200',
        ]);

        $maintenanceRequest->update([
            'status' => $validated['status'],
            'owner_response' => $validated['owner_response'] ?? $maintenanceRequest->owner_response,
            'resolved_at' => $validated['status'] === 'resolved' ? now() : null,
        ]);

        if ($maintenanceRequest->tenant) {
            $maintenanceRequest->tenant->notify(new WorkflowStatusNotification(
                'maintenance_status_updated',
                'Maintenance Request Updated',
                'Your maintenance request for ' . ($maintenanceRequest->house->title ?? ('Property #' . $maintenanceRequest->house_id)) . ' is now ' . str_replace('_', ' ', $validated['status']) . '.'
            ));
        }

        return back()->with('success', 'Maintenance request status updated and tenant has been notified.');
    }

    // ADMIN WORKFLOW METHODS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Admin directly approves complaint for repair (for obvious/visible issues)
     * Skips inspection and arranges service immediately
     */
    public function adminApproveForRepair(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admin can approve repairs.');
        }

        $validated = $request->validate([
            'payment_responsibility' => 'required|in:owner,tenant',
            'admin_notes' => 'required|string|max:1000',
        ]);

        $maintenanceRequest->update([
            'status' => 'approved_for_repair',
            'payment_responsibility' => $validated['payment_responsibility'],
            'admin_notes' => $validated['admin_notes'],
            'needs_inspection' => false,
            'approved_for_repair_at' => now(),
        ]);

        // Notify tenant and owner
        if ($maintenanceRequest->tenant) {
            $maintenanceRequest->tenant->notify(new WorkflowStatusNotification(
                'maintenance_approved',
                'Maintenance Complaint Approved for Repair',
                'Your complaint for ' . ($maintenanceRequest->house->title ?? ('Property #' . $maintenanceRequest->house_id)) . ' has been approved for repair. Payment responsibility: ' . ucfirst($validated['payment_responsibility']) . '.'
            ));
        }

        if ($maintenanceRequest->owner) {
            $maintenanceRequest->owner->notify(new WorkflowStatusNotification(
                'maintenance_approved',
                'Maintenance Complaint Approved',
                'Tenant complaint for property ' . ($maintenanceRequest->house->title ?? ('Property #' . $maintenanceRequest->house_id)) . ' approved for repair. Payment responsibility: ' . ucfirst($validated['payment_responsibility']) . '.'
            ));
        }

        return back()->with('success', 'Complaint approved for repair. Service arrangement in progress.');
    }

    /**
     * Admin requests inspection before deciding on repair
     * Used when cause is unclear or responsibility needs verification
     */
    public function adminRequestInspection(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admin can request inspections.');
        }

        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
            'inspection_reason' => 'required|in:unclear_cause,verify_responsibility,condition_assessment',
        ]);

        $maintenanceRequest->update([
            'needs_inspection' => true,
            'admin_notes' => $validated['admin_notes'],
            'status' => 'pending', // Stays pending until inspection is done
        ]);

        // Notify tenant of inspection request
        if ($maintenanceRequest->tenant) {
            $reason = match ($validated['inspection_reason']) {
                'unclear_cause' => 'cause of damage needs verification',
                'verify_responsibility' => 'payment responsibility needs confirmation',
                'condition_assessment' => 'property condition assessment is required',
                default => 'inspection is required'
            };

            $maintenanceRequest->tenant->notify(new WorkflowStatusNotification(
                'inspection_requested',
                'Property Inspection Required',
                'An inspection is required for your complaint at ' . ($maintenanceRequest->house->title ?? ('Property #' . $maintenanceRequest->house_id)) . ' because ' . $reason . '.'
            ));
        }

        return back()->with('success', 'Inspection requested. Tenant will be contacted to schedule.');
    }

    /**
     * Admin records inspection results and decides on repair
     */
    public function adminCompleteInspection(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admin can complete inspections.');
        }

        $validated = $request->validate([
            'inspection_notes' => 'required|string|max:1500',
            'approve_repair' => 'required|boolean',
            'payment_responsibility' => 'required_if:approve_repair,true|in:owner,tenant',
            'rejection_reason' => 'required_if:approve_repair,false|string|max:500',
        ]);

        if (!$maintenanceRequest->needs_inspection) {
            return back()->with('error', 'This complaint does not require inspection.');
        }

        $maintenanceRequest->update([
            'inspection_notes' => $validated['inspection_notes'],
        ]);

        if ($validated['approve_repair']) {
            $maintenanceRequest->update([
                'status' => 'approved_for_repair',
                'payment_responsibility' => $validated['payment_responsibility'],
                'approved_for_repair_at' => now(),
                'needs_inspection' => false,
            ]);

            if ($maintenanceRequest->tenant) {
                $maintenanceRequest->tenant->notify(new WorkflowStatusNotification(
                    'maintenance_approved',
                    'Inspection Complete - Repair Approved',
                    'Your complaint for ' . ($maintenanceRequest->house->title ?? ('Property #' . $maintenanceRequest->house_id)) . ' has been approved for repair after inspection. Payment responsibility: ' . ucfirst($validated['payment_responsibility']) . '.'
                ));
            }

            return back()->with('success', 'Inspection complete. Complaint approved for repair.');
        } else {
            $maintenanceRequest->update([
                'status' => 'rejected',
                'needs_inspection' => false,
            ]);

            if ($maintenanceRequest->tenant) {
                $maintenanceRequest->tenant->notify(new WorkflowStatusNotification(
                    'maintenance_rejected',
                    'Complaint Not Approved for Repair',
                    'Your complaint for ' . ($maintenanceRequest->house->title ?? ('Property #' . $maintenanceRequest->house_id)) . ' could not be approved. Reason: ' . $validated['rejection_reason']
                ));
            }

            return back()->with('success', 'Inspection complete. Complaint rejected.');
        }
    }

    /**
     * Admin updates repair status (started repairs, completed repairs)
     */
    public function adminUpdateRepairStatus(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admin can update repair status.');
        }

        $validated = $request->validate([
            'status' => 'required|in:under_repair,resolved',
        ]);

        if ($maintenanceRequest->status !== 'approved_for_repair' && $maintenanceRequest->status !== 'under_repair') {
            return back()->with('error', 'Invalid status transition. Complaint must be approved for repair first.');
        }

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'under_repair') {
            $updateData['under_repair_at'] = now();
        } elseif ($validated['status'] === 'resolved') {
            $updateData['resolved_at'] = now();
        }

        $maintenanceRequest->update($updateData);

        $message = $validated['status'] === 'under_repair'
            ? 'Repair work has started.'
            : 'Repair work is complete.';

        if ($maintenanceRequest->tenant) {
            $maintenanceRequest->tenant->notify(new WorkflowStatusNotification(
                'maintenance_status_updated',
                'Maintenance Status Updated',
                'Your complaint for ' . ($maintenanceRequest->house->title ?? ('Property #' . $maintenanceRequest->house_id)) . ' is now ' . str_replace('_', ' ', $validated['status']) . '. ' . $message
            ));
        }

        return back()->with('success', 'Repair status updated and tenant notified.');
    }
}
