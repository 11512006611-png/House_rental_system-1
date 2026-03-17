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
        ]);

        if ($request->hasFile('image')) {
            if ($house->image) {
                Storage::disk('public')->delete($house->image);
            }
            $validated['image'] = $request->file('image')->store('houses', 'public');
        }

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
