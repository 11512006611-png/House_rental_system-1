<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Inspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InspectionController extends Controller
{
    /** Tenant: submit a new inspection request */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'house_id'       => 'required|exists:houses,id',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'required|in:Morning,Afternoon,Evening',
            'message'        => 'nullable|string|max:1000',
        ]);

        $validated['tenant_id'] = Auth::id();
        $validated['status']    = 'pending';

        Inspection::create($validated);

        return redirect()->route('tenant.dashboard')
            ->with('success', 'Inspection request submitted successfully! The owner will review and confirm your preferred schedule.');
    }

    /** Tenant: cancel their own pending inspection request */
    public function cancel(Inspection $inspection)
    {
        abort_if($inspection->tenant_id !== Auth::id(), 403);
        abort_if($inspection->status !== 'pending', 422, 'Only pending inspections can be cancelled.');

        $inspection->update(['status' => 'rejected']);

        return back()->with('success', 'Inspection request cancelled.');
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
        $pendingCount  = Inspection::whereIn('house_id', $houseIds)->where('status', 'pending')->count();
        $approvedCount = Inspection::whereIn('house_id', $houseIds)->where('status', 'approved')->count();

        return view('owner.inspections', compact('inspections', 'pendingCount', 'approvedCount'));
    }
}
