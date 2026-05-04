<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\House;
use App\Models\Payment;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function create(House $house)
    {
        $house->loadMissing('owner');

        return view('bookings.create', compact('house'));
    }

    public function store(Request $request, House $house)
    {
        $user = Auth::user();

        abort_unless(($user->role ?? null) === 'tenant', 403, 'Only tenants can create bookings.');

        if ($house->status !== 'available') {
            return back()->with('error', 'This property is not available for booking.');
        }

        $validated = $request->validate([
            'booking_date' => 'nullable|date|after_or_equal:today',
            'security_deposit_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $securityDeposit = (float) ($validated['security_deposit_amount'] ?? $house->security_deposit_amount ?? $house->price);
        $firstMonthRent = (float) $house->price;
        $serviceFeeRate = $house->admin_commission_rate !== null
            ? (float) $house->admin_commission_rate
            : (float) (DB::table('settings')->where('key', 'commission_rate')->value('value') ?? 10.0);
        // Advance now requires two months (rent + service fee per month) plus security deposit
        $breakdown = Payment::calculateAdvanceBreakdown($firstMonthRent * 2, $securityDeposit, $serviceFeeRate);

        $booking = DB::transaction(function () use ($house, $validated, $securityDeposit, $firstMonthRent, $serviceFeeRate, $breakdown, $user) {
            $rental = Rental::create([
                'house_id' => $house->id,
                'tenant_id' => $user->id,
                'rental_date' => $validated['booking_date'] ?? now()->toDateString(),
                'monthly_rent' => $firstMonthRent,
                'status' => 'pending',
                'notes' => trim((string) ($validated['notes'] ?? 'Booking created from tenant booking request.')),
            ]);

            $booking = Booking::create([
                'house_id' => $house->id,
                'tenant_id' => $user->id,
                'owner_id' => $house->owner_id,
                'rental_id' => $rental->id,
                'monthly_rent' => $firstMonthRent,
                'first_month_rent_amount' => $firstMonthRent,
                'security_deposit_amount' => $securityDeposit,
                'service_fee_rate' => $serviceFeeRate,
                'service_fee_amount' => $breakdown['service_fee_amount'],
                'total_advance_amount' => $breakdown['total_advance_amount'],
                'status' => 'payment_pending',
                'booking_date' => $validated['booking_date'] ?? now()->toDateString(),
                'notes' => trim((string) ($validated['notes'] ?? 'Booking created by tenant.')),
            ]);

            Payment::create([
                'tenant_id' => $user->id,
                'rental_id' => $rental->id,
                'booking_id' => $booking->id,
                'amount' => $breakdown['rent_payable_amount'],
                'rent_amount' => $breakdown['rent_amount'],
                'security_deposit_amount' => 0,
                'service_fee_rate' => $serviceFeeRate,
                'service_fee_amount' => $breakdown['service_fee_amount'],
                'total_advance_amount' => $breakdown['total_advance_amount'],
                'commission_rate' => $serviceFeeRate,
                'commission_amount' => $breakdown['service_fee_amount'],
                'owner_share_amount' => $breakdown['owner_share_amount'],
                'payment_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'payment_type' => 'first_month_rent',
                'held_by_admin' => false,
                'status' => 'pending',
                'verification_status' => 'pending',
                'notes' => 'Advance rent (including service fee) for booking #' . $booking->id,
            ]);

            Payment::create([
                'tenant_id' => $user->id,
                'rental_id' => $rental->id,
                'booking_id' => $booking->id,
                'amount' => $securityDeposit,
                'rent_amount' => 0,
                'security_deposit_amount' => $securityDeposit,
                'service_fee_rate' => 0,
                'service_fee_amount' => 0,
                'total_advance_amount' => $breakdown['total_advance_amount'],
                'commission_rate' => 0,
                'commission_amount' => 0,
                'owner_share_amount' => 0,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'payment_type' => 'security_deposit',
                'held_by_admin' => true,
                'status' => 'pending',
                'verification_status' => 'pending',
                'notes' => 'Security deposit held by admin for booking #' . $booking->id,
            ]);

            return $booking;
        });

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking created. First month rent and security deposit records are ready for admin review.');
    }

    public function show(Booking $booking)
    {
        $booking->load(['house.owner', 'tenant', 'payments', 'refund']);

        abort_unless((int) $booking->tenant_id === (int) Auth::id() || (Auth::user()?->role === 'admin'), 403);

        return view('bookings.show', compact('booking'));
    }
}
