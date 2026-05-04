<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Inspection;
use App\Models\MoveOutRequest;
use App\Models\Rental;
use App\Models\User;
use App\Notifications\WorkflowStatusNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class InspectionController extends Controller
{
    /** Tenant: submit a new inspection request */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'house_id'       => 'required|exists:houses,id',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'required|in:09:00,11:00,14:00,16:00,18:00',
            'message'        => 'nullable|string|max:1000',
        ]);

        $validated['tenant_id'] = Auth::id();
        $validated['status']    = 'pending';

        $inspection = Inspection::create($validated);

        $inspection->loadMissing(['tenant', 'house']);

        User::where('role', 'admin')->get()->each(function ($admin) use ($inspection) {
            try {
                $admin->notify(new WorkflowStatusNotification(
                    'inspection_requested',
                    'New Inspection Request',
                    'Tenant ' . ($inspection->tenant?->name ?? 'Unknown Tenant')
                    . ' requested an inspection for '
                    . ($inspection->house?->title ?? ('Property #' . $inspection->house_id))
                    . ' on ' . optional($inspection->preferred_date)->format('Y-m-d')
                    . ' (' . ($inspection->preferred_time ?? 'time not provided') . ').'
                ));
            } catch (Throwable $e) {
                Log::warning('Admin inspection notification failed.', [
                    'admin_id' => $admin->id,
                    'inspection_id' => $inspection->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        return back()->with('success', 'Inspection request submitted successfully. Admin has been notified and will review your preferred schedule.');
    }

    /** Tenant: cancel their own pending inspection request */
    public function cancel(Inspection $inspection)
    {
        abort_if($inspection->tenant_id !== Auth::id(), 403);
        abort_if($inspection->status !== 'pending', 422, 'Only pending inspections can be cancelled.');

        $inspection->update(['status' => 'rejected']);

        return back()->with('success', 'Inspection request cancelled.');
    }

    /** Tenant: decide stay or move out after confirmed inspection */
    public function tenantDecision(Request $request, Inspection $inspection)
    {
        abort_if($inspection->tenant_id !== Auth::id(), 403);

        if (! in_array($inspection->status, ['confirmed', 'completed'], true)) {
            return back()->with('error', 'Decision is only available after inspection is confirmed/completed.');
        }

        if (in_array($inspection->tenant_decision, ['stay', 'move_out'], true)) {
            return back()->with('error', 'Decision is already submitted for this inspection.');
        }

        $validated = $request->validate([
            'decision' => 'required|in:stay,move_out',
            'message' => 'nullable|string|max:1500',
            'move_out_date' => 'required_if:decision,move_out|nullable|date|after_or_equal:today',
        ]);

        $decision = $validated['decision'];
        $message = trim((string) ($validated['message'] ?? ''));

        $inspection->update([
            'tenant_decision' => $decision,
            'tenant_decision_message' => $message !== '' ? $message : null,
            'tenant_decision_at' => now(),
        ]);

        $inspection->loadMissing(['house.owner', 'tenant']);

        if ($decision === 'stay') {
            $existingRental = Rental::where('tenant_id', Auth::id())
                ->where('house_id', $inspection->house_id)
                ->whereIn('status', ['pending', 'active'])
                ->latest()
                ->first();

            if (! $existingRental && $inspection->house) {
                $existingRental = Rental::create([
                    'house_id' => $inspection->house_id,
                    'tenant_id' => Auth::id(),
                    'rental_date' => now()->toDateString(),
                    'monthly_rent' => $inspection->house->price,
                    'status' => 'active',
                    'lease_status' => 'requested',
                    'lease_requested_at' => now(),
                    'notes' => 'Auto-created after tenant selected Stay from inspection decision. Waiting admin lease upload and tenant advance payment.',
                ]);
            } elseif ($existingRental) {
                $existingRental->update([
                    'status' => 'active',
                    'lease_status' => 'requested',
                    'lease_requested_at' => now(),
                    'notes' => trim((string) ($existingRental->notes
                        ? $existingRental->notes . PHP_EOL
                        : '') . 'Tenant selected Stay after inspection. Waiting admin lease upload and tenant advance payment.'),
                ]);
            }

            if ($inspection->house && ! in_array($inspection->house->status, ['pending', 'rejected'], true)) {
                $inspection->house->update(['status' => 'rented']);
            }

            if ($inspection->house?->owner) {
                $inspection->house->owner->notify(new WorkflowStatusNotification(
                    'inspection_tenant_decision_stay',
                    'Tenant Chose To Stay',
                    'Tenant selected "I want to stay" after inspection for '
                    . ($inspection->house->title ?? ('Property #' . $inspection->house_id))
                    . ($message !== '' ? '. Message: ' . $message : '.')
                ));
            }

            User::where('role', 'admin')->get()->each(function ($admin) use ($inspection, $message) {
                $admin->notify(new WorkflowStatusNotification(
                    'inspection_tenant_decision_stay',
                    'Lease Agreement Required',
                    'Tenant selected "I want to stay" after inspection for '
                    . ($inspection->house?->title ?? ('Property #' . $inspection->house_id))
                    . '. Please upload lease agreement from Admin Rentals, then tenant will pay 2-month advance.'
                    . ($message !== '' ? ' Message: ' . $message : '')
                ));
            });

            return back()->with('success', 'Your Stay decision was submitted. Admin will upload lease agreement and then you can pay advance.');
        }

        $moveOutDate = $validated['move_out_date'];

        $activeRental = Rental::where('tenant_id', Auth::id())
            ->where('house_id', $inspection->house_id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if ($activeRental) {
            $openMoveOut = MoveOutRequest::where('rental_id', $activeRental->id)
                ->whereIn('status', ['requested', 'approved'])
                ->exists();

            if (! $openMoveOut) {
                MoveOutRequest::create([
                    'rental_id' => $activeRental->id,
                    'tenant_id' => Auth::id(),
                    'owner_id' => $activeRental->house->owner_id,
                    'house_id' => $activeRental->house_id,
                    'booking_id' => $activeRental->booking?->id,
                    'reason' => $message !== '' ? $message : 'Tenant selected move-out after inspection.',
                    'move_out_date' => $moveOutDate,
                    'status' => 'requested',
                ]);
            }
        }

        if ($inspection->house?->owner) {
            $inspection->house->owner->notify(new WorkflowStatusNotification(
                'inspection_tenant_decision_move_out',
                'Tenant Chose Move Out',
                'Tenant selected "No, I do not want to stay" after inspection for '
                . ($inspection->house->title ?? ('Property #' . $inspection->house_id))
                . '. Move-out date: ' . $moveOutDate
                . ($message !== '' ? '. Reason: ' . $message : '.')
            ));
        }

        User::where('role', 'admin')->get()->each(function ($admin) use ($inspection, $moveOutDate, $message) {
            $admin->notify(new WorkflowStatusNotification(
                'inspection_tenant_decision_move_out',
                'Tenant Decision: Move Out',
                'Tenant selected "No, I do not want to stay" after inspection for '
                . ($inspection->house?->title ?? ('Property #' . $inspection->house_id))
                . '. Move-out date: ' . $moveOutDate
                . ($message !== '' ? '. Reason: ' . $message : '.')
                . ' Please proceed with refund workflow.'
            ));
        });

        return back()->with('success', 'Your Move Out decision was submitted.');
    }

    /** Owner: approve an inspection request */
    public function approve(Request $request, Inspection $inspection)
    {
        $house = $inspection->house;
        abort_if($house->owner_id !== Auth::id(), 403);

        $validated = $request->validate([
            'owner_notes'  => 'nullable|string|max:500',
            'scheduled_at' => 'nullable|date',
        ]);

        $inspection->update(array_merge($validated, ['status' => 'approved']));

        return back()->with('success', 'Inspection approved and tenant will be notified.');
    }

    /** Owner: reject an inspection request */
    public function reject(Request $request, Inspection $inspection)
    {
        $house = $inspection->house;
        abort_if($house->owner_id !== Auth::id(), 403);

        $validated = $request->validate([
            'owner_notes' => 'nullable|string|max:500',
        ]);

        $inspection->update(array_merge($validated, ['status' => 'rejected']));

        return back()->with('success', 'Inspection request declined.');
    }

    /** Owner: mark inspection as completed */
    public function complete(Inspection $inspection)
    {
        $house = $inspection->house;
        abort_if($house->owner_id !== Auth::id(), 403);

        $inspection->update(['status' => 'completed']);

        return back()->with('success', 'Inspection marked as completed.');
    }

    /** Owner: list all inspection requests for their properties */
    public function ownerIndex(Request $request)
    {
        $houseIds = House::where('owner_id', Auth::id())->pluck('id');

        $query = Inspection::with(['house', 'tenant'])
            ->whereIn('house_id', $houseIds);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $inspections   = $query->latest()->paginate(15)->withQueryString();
        $pendingCount      = Inspection::whereIn('house_id', $houseIds)->where('status', 'pending')->count();
        $confirmedCount    = Inspection::whereIn('house_id', $houseIds)->where('status', 'confirmed')->count();
        $rescheduledCount  = Inspection::whereIn('house_id', $houseIds)->where('status', 'rescheduled')->count();
        $rejectedCount     = Inspection::whereIn('house_id', $houseIds)->where('status', 'rejected')->count();

        return view('owner.inspections', compact('inspections', 'pendingCount', 'confirmedCount', 'rescheduledCount', 'rejectedCount'));
    }
}
