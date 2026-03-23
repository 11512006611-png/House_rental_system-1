<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\TenantReview;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $totalHouses = House::available()->count();

        $tenantReviews = TenantReview::with('user:id,name,role')
            ->where('is_visible', true)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(9)
            ->get()
            ->map(function ($review) {
                return (object) [
                    'title' => $review->title,
                    'message' => $review->message,
                    'name' => $review->user->name ?? 'Anonymous Tenant',
                    'location' => $review->location ?: 'Bhutan',
                    'avatarColor' => null,
                    'created_at' => $review->created_at,
                ];
            });

        $exampleReviews = collect([
            (object) [
                'title' => 'The services are very helpful',
                'message' => 'HRS Bhutan made finding a house in Thimphu so easy. Listings were accurate and the full process was smooth.',
                'name' => 'Dorji Wangchuk',
                'location' => 'Thimphu',
                'avatarColor' => '#1e90be',
                'created_at' => null,
            ],
            (object) [
                'title' => 'Found my apartment quickly',
                'message' => 'I was searching for months before HRS Bhutan. The filters helped me find exactly what I needed in Paro.',
                'name' => 'Pema Lhamo',
                'location' => 'Paro',
                'avatarColor' => '#7c3aed',
                'created_at' => null,
            ],
            (object) [
                'title' => 'Transparent and trustworthy',
                'message' => 'The home details matched what I saw during visits. It saved me time and avoided surprises.',
                'name' => 'Sonam Choden',
                'location' => 'Wangdue',
                'avatarColor' => '#10b981',
                'created_at' => null,
            ],
            (object) [
                'title' => 'Great support from the team',
                'message' => 'Whenever I had a question about the process, support replied quickly and clearly.',
                'name' => 'Yangchen Dema',
                'location' => 'Trongsa',
                'avatarColor' => '#f59e0b',
                'created_at' => null,
            ],
            (object) [
                'title' => 'Easy to compare listings',
                'message' => 'I compared rent, location, and amenities in one place and chose the best option for my budget.',
                'name' => 'Tashi Phuntsho',
                'location' => 'Phuentsholing',
                'avatarColor' => '#ef4444',
                'created_at' => null,
            ],
            (object) [
                'title' => 'Best experience so far',
                'message' => 'Clean interface, quick browsing, and clear rental steps. I would definitely recommend it to other tenants.',
                'name' => 'Kezang Lham',
                'location' => 'Bumthang',
                'avatarColor' => '#2563eb',
                'created_at' => null,
            ],
        ]);

        $testimonialItems = $tenantReviews
            ->sortByDesc('created_at')
            ->concat($exampleReviews)
            ->take(9)
            ->values();

        return view('home.index', compact('totalHouses', 'testimonialItems'));
    }
}
