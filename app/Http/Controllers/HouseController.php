<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Location;
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
        $locations = Location::orderBy('dzongkhag_name')->get();
        $houseTypes = ['1BHK', '2BHK', '3BHK', 'Apartment', 'Villa', 'Studio', 'Duplex'];

        return view('houses.index', compact('houses', 'locations', 'houseTypes'));
    }

    public function show(House $house)
    {
        $house->load(['locationModel', 'owner', 'houseImages']);
        $relatedHouses = House::available()
            ->with(['locationModel', 'houseImages'])
            ->where('id', '!=', $house->id)
            ->where('location_id', $house->location_id)
            ->take(3)
            ->get();

        return view('houses.show', compact('house', 'relatedHouses'));
    }

    public function create()
    {
        $locations = Location::orderBy('dzongkhag_name')->get();
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
            $validated['image'] = $imageFiles[0]->store('houses', 'public');
        }

        $validated['owner_id'] = Auth::id();
        $validated['status'] = 'pending'; // awaiting admin approval

        $house = House::create($validated);

        foreach ($imageFiles as $index => $file) {
            $storedPath = $index === 0
                ? $validated['image']
                : $file->store('houses', 'public');

            $house->houseImages()->create([
                'path' => $storedPath,
                'sort_order' => $index,
            ]);
        }

        return redirect()->route('houses.my-listings')
            ->with('success', 'House listed successfully! It is now pending admin approval before going live.');
    }

    public function edit(House $house)
    {
        $this->authorize('update', $house);
        $house->load('houseImages');
        $locations = Location::orderBy('dzongkhag_name')->get();
        $houseTypes = ['1BHK', '2BHK', '3BHK', 'Apartment', 'Villa', 'Studio', 'Duplex'];
        return view('houses.edit', compact('house', 'locations', 'houseTypes'));
    }

    public function update(Request $request, House $house)
    {
        $this->authorize('update', $house);

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
            'status'      => 'required|in:available,rented,pending',
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
            $newPath = $replacementFile->store('houses', 'public');

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
            $newCoverPath = $request->file('image')->store('houses', 'public');
            $validated['image'] = $newCoverPath;
        }

        $maxSortOrder = (int) ($house->houseImages()->max('sort_order') ?? -1);
        foreach ($request->file('new_images', []) as $file) {
            $storedPath = $file->store('houses', 'public');
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

        $house->update($validated);

        return redirect()->route('houses.show', $house)
            ->with('success', 'House updated successfully!');
    }

    public function destroy(House $house)
    {
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

        return redirect()->route('houses.index')
            ->with('success', 'House listing removed.');
    }

    public function myListings()
    {
        $houses = House::with('houseImages')->where('owner_id', Auth::id())->latest()->paginate(10);
        return view('houses.my-listings', compact('houses'));
    }
}
