<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.show', [
            'user' => Auth::user(),
        ]);
    }

    public function edit()
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'username' => ['sometimes', 'required', 'string', 'max:100', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'after:1900-01-01', 'before:today'],
            'profile_image' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['sometimes', 'nullable', 'string', 'confirmed', 'min:8', 'max:255'],
        ]);

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $validated['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
        }

        $updateData = [];

        if ($request->exists('username')) {
            $updateData['username'] = $validated['username'];
        }

        if ($request->exists('email')) {
            $updateData['email'] = $validated['email'];
        }

        if ($request->exists('phone')) {
            $updateData['phone'] = $validated['phone'] ?? null;
        }

        if ($request->exists('date_of_birth')) {
            $updateData['date_of_birth'] = $validated['date_of_birth'] ?? null;
        }

        if (isset($validated['profile_image'])) {
            $updateData['profile_image'] = $validated['profile_image'];
        }

        if (! empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        if (! empty($updateData)) {
            $user->update($updateData);
        }

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    }
}
