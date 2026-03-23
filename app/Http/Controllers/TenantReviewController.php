<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TenantReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:80'],
        ]);

        $request->user()->tenantReviews()->create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'location' => $validated['location'] ?? null,
            'is_visible' => true,
        ]);

        return redirect()
            ->to(route('tenant.dashboard') . '#share-review')
            ->with('tenant_review_success', 'Your review has been posted and is now visible on the home page.');
    }
}
