<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Inspection;
use App\Models\Location;
use App\Models\User;
use App\Notifications\WorkflowStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HouseController extends Controller
{
    public function index(Request $request)
    {
        $query = House::with(['locationModel', 'owner', 'houseImages'])->available();

        if ($request->filled('location')) {
            $query->where('location_id', $request->location);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        $houses = $query->latest()->paginate(9);
        $locations = Location::orderedDzongkhags();
        $houseTypes = ['1BHK', '2BHK', '3BHK', 'Apartment', 'Villa', 'Studio', 'Duplex'];

        return view('houses.index', compact('houses', 'locations', 'houseTypes'));
    }

    public function show(House $house)
    {
        $currentUser = Auth::user();
        $tenantRental = null;
        $advancePaymentStatus = null;
        $advancePaymentEligible = false;

        if ($currentUser && $currentUser->role === 'tenant') {
            $tenantRental = $house->rentals()
                ->where('tenant_id', $currentUser->id)
                ->with('leaseAgreement')
                ->latest()
                ->first();
        }

        $canViewUnpublished = $currentUser && (
            ($currentUser->role === 'owner' && (int) $house->owner_id === (int) $currentUser->id) ||
            $currentUser->role === 'admin' ||
            ($currentUser->role === 'tenant' && $tenantRental !== null)
        );

        if ($house->status !== 'available' && ! $canViewUnpublished) {
            abort(404);
        }

        $house->load(['locationModel', 'owner', 'houseImages']);

            if ($tenantRental) {
                $inspectionCompleted = Inspection::where('tenant_id', $currentUser->id)
                    ->where('house_id', $house->id)
                    ->whereIn('status', ['confirmed', 'completed'])
                    ->exists();

                $advancePayments = $tenantRental->payments()->whereIn('payment_type', ['first_month_rent', 'security_deposit']);
                $hasPending = $advancePayments->where('verification_status', 'pending')->exists();
                $hasVerified = $advancePayments->where('verification_status', 'verified')->exists();
                $hasRejected = $advancePayments->where('verification_status', 'rejected')->exists();

                if ($hasPending) {
                    $advancePaymentStatus = 'pending';
                } elseif ($hasVerified) {
                    $advancePaymentStatus = 'verified';
                } elseif ($hasRejected) {
                    $advancePaymentStatus = 'rejected';
                } else {
                    $advancePaymentStatus = 'none';
                }

                $advancePaymentEligible = $tenantRental->lease_status === 'approved'
                    && ($tenantRental->leaseAgreement->tenant_review_status ?? 'pending') === 'accepted'
                    && ! $hasPending
                    && ! $hasVerified;
            }

        $relatedHouses = House::available()
            ->with(['locationModel', 'houseImages'])
            ->where('id', '!=', $house->id)
            ->where('location_id', $house->location_id)
            ->take(3)
            ->get();

        return view('houses.show', compact('house', 'relatedHouses', 'tenantRental', 'advancePaymentStatus', 'advancePaymentEligible'));
    }

    public function create()
    {
        $locations = Location::orderedDzongkhags();
        $houseTypes = ['1BHK', '2BHK', '3BHK', 'Apartment', 'Villa', 'Studio', 'Duplex'];
        return view('houses.create', compact('locations', 'houseTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'location'    => 'required|string|max:255',
            'type'        => 'required|in:1BHK,2BHK,3BHK,Apartment,Villa,Studio,Duplex',
            'price'       => 'required|numeric|min:0',
            'bedrooms'    => 'required|integer|min:1',
            'bathrooms'   => 'required|integer|min:1',
            'area'        => 'nullable|string|max:50',
            'address'     => 'nullable|string|max:500',
            'description' => 'nullable|string|max:2000',
            'images'      => 'required|array|min:3',
            'images.*'    => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'images.required' => 'Please upload at least 3 photos of the property.',
            'images.array'    => 'Please upload at least 3 photos of the property.',
            'images.min'      => 'Please upload at least 3 photos of the property.',
        ]);

        $imageFiles = $request->file('images', []);
        if (!empty($imageFiles)) {
            // Keep the legacy thumbnail field populated for backward compatibility.
            $validated['image'] = $this->storeProcessedImage($imageFiles[0], 'houses');
        }

        $validated['owner_id'] = Auth::id();
        $validated['status'] = 'pending'; // awaiting admin approval
        $validated['inspection_scheduled_at'] = null;
        $validated['inspected_by_admin_id'] = null;
        $validated['inspected_at'] = null;
        $validated['admin_inspection_notes'] = null;

        $house = House::create($validated);

        foreach ($imageFiles as $index => $file) {
            $storedPath = $index === 0
                ? $validated['image']
                : $this->storeProcessedImage($file, 'houses');

            $house->houseImages()->create([
                'path' => $storedPath,
                'sort_order' => $index,
            ]);
        }

        User::where('role', 'admin')->get()->each(function ($admin) use ($house) {
            $admin->notify(new WorkflowStatusNotification(
                'property_submitted_for_review',
                'New Property Submitted',
                'Owner submitted "' . ($house->title ?? ('Property #' . $house->id)) . '" for inspection and approval.'
            ));
        });

        return redirect()->route('houses.my-listings')
            ->with('success', 'House listed successfully! It is now pending admin approval before going live.');
    }

    public function edit(House $house)
    {
        if (Auth::user()?->role === 'owner' && $house->rentals()->where('status', 'active')->exists()) {
            abort(403, 'This property cannot be edited while it has an active tenant.');
        }

        $this->authorize('update', $house);
        $house->load('houseImages');
        $locations = Location::orderedDzongkhags();
        $houseTypes = ['1BHK', '2BHK', '3BHK', 'Apartment', 'Villa', 'Studio', 'Duplex'];
        return view('houses.edit', compact('house', 'locations', 'houseTypes'));
    }

    public function update(Request $request, House $house)
    {
        if (Auth::user()?->role === 'owner' && $house->rentals()->where('status', 'active')->exists()) {
            abort(403, 'This property cannot be edited while it has an active tenant.');
        }

        $this->authorize('update', $house);

        $originalStatus = $house->status;
        $updatedByOwner = Auth::user()?->role === 'owner';

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'location'    => 'required|string|max:255',
            'type'        => 'required|in:1BHK,2BHK,3BHK,Apartment,Villa,Studio,Duplex',
            'price'       => 'required|numeric|min:0',
            'bedrooms'    => 'required|integer|min:1',
            'bathrooms'   => 'required|integer|min:1',
            'area'        => 'nullable|string|max:50',
            'address'     => 'nullable|string|max:500',
            'description' => 'nullable|string|max:2000',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'new_images'   => 'nullable|array',
            'new_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'replace_images' => 'nullable|array',
            'replace_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'nullable|integer',
        ]);

        $deleteIds = collect($request->input('delete_images', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $coverPathDeleted = false;
        $newCoverPath = null;

        if (! $deleteIds->isEmpty()) {
            $imagesToDelete = $house->houseImages()->whereIn('id', $deleteIds)->get();

            foreach ($imagesToDelete as $galleryImage) {
                if ($house->image && $house->image === $galleryImage->path) {
                    $coverPathDeleted = true;
                }

                if ($galleryImage->path) {
                    Storage::disk('public')->delete($galleryImage->path);
                }
                $galleryImage->delete();
            }
        }

        foreach ($request->file('replace_images', []) as $imageId => $replacementFile) {
            $imageId = (int) $imageId;

            if ($deleteIds->contains($imageId)) {
                continue;
            }

            $galleryImage = $house->houseImages()->where('id', $imageId)->first();
            if (! $galleryImage) {
                continue;
            }

            $oldPath = $galleryImage->path;
            $newPath = $this->storeProcessedImage($replacementFile, 'houses');

            $galleryImage->update(['path' => $newPath]);

            if ($house->image && $house->image === $oldPath) {
                $validated['image'] = $newPath;
            }

            if ($oldPath && $oldPath !== $newPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        if ($request->hasFile('image')) {
            if ($house->image && ! $house->houseImages()->where('path', $house->image)->exists()) {
                Storage::disk('public')->delete($house->image);
            }
            $newCoverPath = $this->storeProcessedImage($request->file('image'), 'houses');
            $validated['image'] = $newCoverPath;
        }

        $maxSortOrder = (int) ($house->houseImages()->max('sort_order') ?? -1);
        foreach ($request->file('new_images', []) as $file) {
            $storedPath = $this->storeProcessedImage($file, 'houses');
            $house->houseImages()->create([
                'path' => $storedPath,
                'sort_order' => ++$maxSortOrder,
            ]);
        }

        if ($newCoverPath) {
            $firstGalleryImage = $house->houseImages()->orderBy('sort_order')->orderBy('id')->first();

            if ($firstGalleryImage) {
                if ($firstGalleryImage->path && $firstGalleryImage->path !== $newCoverPath) {
                    Storage::disk('public')->delete($firstGalleryImage->path);
                }

                $firstGalleryImage->update(['path' => $newCoverPath]);
            } else {
                $house->houseImages()->create([
                    'path' => $newCoverPath,
                    'sort_order' => 0,
                ]);
            }
        }

        $firstGalleryPath = $house->houseImages()->orderBy('sort_order')->orderBy('id')->value('path');
        if ($firstGalleryPath && ! $newCoverPath) {
            if (! empty($validated['image']) && $validated['image'] !== $firstGalleryPath) {
                Storage::disk('public')->delete($validated['image']);
            }
            $validated['image'] = $firstGalleryPath;
        } elseif (! $request->hasFile('image') && $coverPathDeleted) {
            $validated['image'] = null;
        }

        unset($validated['new_images'], $validated['replace_images'], $validated['delete_images']);

        // Owner updates must be reviewed by admin before becoming public again.
        if ($updatedByOwner && $originalStatus !== 'rented') {
            $validated['status'] = 'pending';
            $validated['admin_commission_rate'] = null;
            $validated['inspection_scheduled_at'] = null;
            $validated['inspected_by_admin_id'] = null;
            $validated['inspected_at'] = null;
            $validated['admin_inspection_notes'] = null;
        }

        $house->update($validated);

        if ($updatedByOwner && $originalStatus !== 'rented') {
            User::where('role', 'admin')->get()->each(function ($admin) use ($house) {
                $admin->notify(new WorkflowStatusNotification(
                    'property_updated_for_review',
                    'Property Update Needs Review',
                    'Owner updated "' . ($house->title ?? ('Property #' . $house->id)) . '". Please inspect and approve before publishing.'
                ));
            });
        }

        $redirect = redirect()->route('houses.show', $house);

        if (Auth::user()?->role === 'admin' && $request->input('return_to') === 'admin-property-show') {
            $redirect = redirect()->route('admin.properties.show', $house);
        }

        return $redirect->with('success', ($updatedByOwner && $originalStatus !== 'rented')
            ? 'House updated and sent for admin review. It will be visible to tenants after admin approval.'
            : 'House details updated successfully.');
    }

    public function destroy(House $house)
    {
        if (Auth::user()?->role === 'owner') {
            $hasActiveRental = $house->rentals()->where('status', 'active')->exists();

            if ($house->status === 'rented' || $hasActiveRental) {
                return back()->with('error', 'This property cannot be deleted while it has an active tenant.');
            }
        }

        $this->authorize('delete', $house);

        foreach ($house->houseImages as $galleryImage) {
            if ($galleryImage->path) {
                Storage::disk('public')->delete($galleryImage->path);
            }
        }

        if ($house->image) {
            Storage::disk('public')->delete($house->image);
        }

        $house->delete();

        if (Auth::user()?->role === 'owner') {
            return redirect()->route('owner.properties')
                ->with('success', 'Property deleted successfully.');
        }

        return redirect()->route('houses.index')
            ->with('success', 'House listing removed.');
    }

    public function myListings()
    {
        $houses = House::with('houseImages')->where('owner_id', Auth::id())->latest()->paginate(10);
        return view('houses.my-listings', compact('houses'));
    }

    public function acknowledgeInspectionSchedule(House $house)
    {
        abort_if((int) $house->owner_id !== (int) Auth::id(), 403);

        if (! in_array($house->status, ['pending', 'rejected'], true)) {
            return back()->with('error', 'Inspection acknowledgement is only available for pending or rejected properties.');
        }

        if (! $house->inspection_scheduled_at) {
            return back()->with('error', 'Admin has not scheduled an inspection date and time yet.');
        }

        if ($house->inspection_schedule_acknowledged_at) {
            return back()->with('success', 'Inspection schedule was already acknowledged.');
        }

        $house->update([
            'inspection_schedule_acknowledged_at' => now(),
        ]);

        User::where('role', 'admin')->get()->each(function ($admin) use ($house) {
            $admin->notify(new WorkflowStatusNotification(
                'property_inspection_schedule_acknowledged',
                'Inspection Schedule Acknowledged',
                'Owner acknowledged the inspection schedule for "' . ($house->title ?? ('Property #' . $house->id)) . '". You can now proceed with inspection decision.'
            ));
        });

        return back()->with('success', 'Inspection schedule received. Admin can now proceed with inspection decision.');
    }

    private function storeProcessedImage($file, $folder)
    {
        return $file->store($folder, 'public');
    }
}
