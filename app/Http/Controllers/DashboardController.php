<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Inspection;
use App\Models\Payment;
use App\Models\Rental;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function tenant()
    {
        $tenantId = Auth::id();

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

            if ($rental->payments->where('verification_status', 'verified')->isNotEmpty()) {
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
            ->where('status', 'completed')
            ->pluck('house_id')
            ->unique()
            ->values();

        $pendingInspections  = $inspections->where('status', 'pending')->count();
        $approvedInspections = $inspections->where('status', 'approved')->count();

        // Notifications for inspections
        foreach ($inspections as $insp) {
            if ($insp->status === 'approved') {
                $notifications->push([
                    'type'    => 'success',
                    'message' => 'Your inspection request for "' . ($insp->house->title ?? 'a property') . '" has been approved'
                                 . ($insp->scheduled_at ? ' — scheduled for ' . $insp->scheduled_at->format('d M Y, g:i A') : '') . '.',
                ]);
            }
            if ($insp->status === 'rejected') {
                $notifications->push([
                    'type'    => 'danger',
                    'message' => 'Your inspection request for "' . ($insp->house->title ?? 'a property') . '" was declined by the owner.',
                ]);
            }
            if ($insp->status === 'completed') {
                $notifications->push([
                    'type'    => 'info',
                    'message' => 'Inspection for "' . ($insp->house->title ?? 'a property') . '" has been marked as completed.',
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
            'inspections',
            'completedInspectionHouseIds',
            'pendingInspections',
            'approvedInspections',
            'availableHouses'
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
}
