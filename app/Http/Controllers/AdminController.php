<?php

namespace App\Http\Controllers;

use App\Models\AdminCommissionTransaction;
use App\Models\AdminEarningsSummary;
use App\Models\House;
use App\Models\MoveOutRequest;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\User;
use App\Notifications\AdminCommissionReceived;
use App\Notifications\OwnerNetPaymentReceived;
use App\Notifications\WorkflowStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS (defined first so all methods can call them)
    // ─────────────────────────────────────────────────────────────────────────

    private function commissionRate(): float
    {
        $db = DB::table('settings')->where('key', 'commission_rate')->value('value');
        return $db !== null ? (float) $db : 10.0;
    }

    private function getMonthlyChartData(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date  = now()->subMonths($i);
            $year  = $date->year;
            $month = $date->month;

            $revenue = (float) Payment::where('status', 'paid')
                ->whereYear('payment_date', $year)
                ->whereMonth('payment_date', $month)
                ->sum('amount');

            $commission = (float) Payment::where('status', 'paid')
                ->whereYear('payment_date', $year)
                ->whereMonth('payment_date', $month)
                ->sum('commission_amount');

            $months[] = [
                'label'      => $date->format('M Y'),
                'revenue'    => $revenue,
                'commission' => $commission,
            ];
        }
        return $months;
    }

    private function getOwnersWithStats()
    {
        return DB::table('users')
            ->where('users.role', 'owner')
            ->leftJoin('houses', 'houses.owner_id', '=', 'users.id')
            ->leftJoin('rentals', 'rentals.house_id', '=', 'houses.id')
            ->leftJoin('payments', function ($join) {
                $join->on('payments.rental_id', '=', 'rentals.id')
                     ->where('payments.status', 'paid');
            })
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.phone',
                'users.status',
                'users.created_at',
                DB::raw('COUNT(DISTINCT houses.id) as total_properties'),
                DB::raw('COALESCE(SUM(payments.amount), 0) as total_revenue'),
                DB::raw('COALESCE(SUM(payments.commission_amount), 0) as total_commission')
            )
            ->groupBy(
                'users.id', 'users.name', 'users.email',
                'users.phone', 'users.status', 'users.created_at'
            )
            ->orderByDesc('total_revenue');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $rate               = $this->commissionRate();
        $totalRevenue       = Payment::where('status', 'paid')->sum('amount');
        $totalCommission    = Payment::where('status', 'paid')->sum('commission_amount');
        $commissionThisMonth = Payment::where('status', 'paid')
            ->whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->sum('commission_amount');
        $netOwnerPayout = Payment::where('status', 'paid')
            ->selectRaw('COALESCE(SUM(amount - commission_amount), 0) as total')
            ->value('total');
        $commissionTransactions = Payment::where('status', 'paid')
            ->where('commission_amount', '>', 0)
            ->count();
        $totalOwners        = User::where('role', 'owner')->where('status', 'approved')->count();
        $totalTenants       = User::where('role', 'tenant')->where('status', 'approved')->count();
        $totalProperties    = House::count();
        $pendingUsers       = User::where('status', 'pending')->count();
        $pendingProperties  = House::where('status', 'pending')->count();
        $pendingRentals     = Rental::where('status', 'pending')->count();
        $openMoveOutRequests = MoveOutRequest::whereIn('status', ['requested', 'approved'])->count();

        // Notification feeds
        $notifPendingUsers      = User::where('status', 'pending')->orderByDesc('created_at')->limit(5)->get();
        $notifPendingProperties = House::where('status', 'pending')->with('owner')->orderByDesc('created_at')->limit(5)->get();
        $notifPendingRentals    = Rental::where('status', 'pending')->with(['house', 'tenant'])->orderByDesc('created_at')->limit(5)->get();

        // Recent transactions
        $recentTransactions = Payment::with(['tenant', 'rental.house.owner'])
            ->where('status', 'paid')
            ->orderByDesc('payment_date')
            ->limit(8)
            ->get();

        // Owner summary (top 5)
        $ownersSummary = $this->getOwnersWithStats()->limit(5)->get();

        // Monthly chart data
        $chartData = $this->getMonthlyChartData();

        return view('admin.dashboard', compact(
            'rate', 'totalRevenue', 'totalCommission',
            'commissionThisMonth', 'netOwnerPayout', 'commissionTransactions',
            'totalOwners', 'totalTenants', 'totalProperties',
            'pendingUsers', 'pendingProperties', 'pendingRentals', 'openMoveOutRequests',
            'notifPendingUsers', 'notifPendingProperties', 'notifPendingRentals',
            'recentTransactions', 'ownersSummary', 'chartData'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // USER MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────────

    public function users(Request $request)
    {
        $query = User::whereIn('role', ['owner', 'tenant'])
            ->withCount(['houses as properties_count', 'rentals'])
            ->orderByDesc('created_at');

        if ($request->filled('role'))   { $query->where('role',   $request->role); }
        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('search')) {
            $query->where(fn($q) => $q->where('name', 'like', '%' . $request->search . '%')
                                      ->orWhere('email', 'like', '%' . $request->search . '%'));
        }

        $users            = $query->paginate(20)->withQueryString();
        $totalAll         = User::whereIn('role', ['owner', 'tenant'])->count();
        $totalActive      = User::whereIn('role', ['owner', 'tenant'])->where('status', 'approved')->count();
        $totalSusp        = User::whereIn('role', ['owner', 'tenant'])->where('status', 'suspended')->count();
        $totalPendingCount= User::whereIn('role', ['owner', 'tenant'])->where('status', 'pending')->count();

        return view('admin.users', compact('users', 'totalAll', 'totalActive', 'totalSusp', 'totalPendingCount'));
    }

    public function pendingUsers()
    {
        $pendingUsers = User::where('status', 'pending')->orderByDesc('created_at')->get();
        return view('admin.pending', compact('pendingUsers'));
    }

    public function owners()
    {
        $owners = $this->getOwnersWithStats()->get();
        foreach ($owners as $owner) {
            $owner->properties = House::where('owner_id', $owner->id)->orderBy('title')->get();
        }
        return view('admin.owners', compact('owners'));
    }

    public function tenants()
    {
        $tenants = User::where('role', 'tenant')
            ->withCount('rentals')
            ->with(['rentals' => fn($q) => $q->with('house')->latest()->limit(1)])
            ->orderByDesc('created_at')
            ->get();
        return view('admin.tenants', compact('tenants'));
    }

    public function approveUser(User $user)
    {
        $user->update(['status' => 'approved']);
        return back()->with('success', "User \"{$user->name}\" has been approved and can now log in.");
    }

    public function rejectUser(User $user)
    {
        $user->update(['status' => 'rejected']);
        return back()->with('success', "User \"{$user->name}\" has been rejected.");
    }

    public function activateUser(User $user)
    {
        $user->update(['status' => 'approved']);
        return back()->with('success', "User \"{$user->name}\" is now active.");
    }

    public function deactivateUser(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot suspend an admin account.');
        }
        $user->update(['status' => 'suspended']);
        return back()->with('success', "User \"{$user->name}\" has been suspended.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PROPERTY MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────────

    public function properties(Request $request)
    {
        $query = House::with(['owner', 'locationModel'])
            ->withCount('rentals')
            ->orderByDesc('created_at');

        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('search')) {
            $query->where(fn($q) => $q->where('title', 'like', '%' . $request->search . '%')
                                      ->orWhere('location', 'like', '%' . $request->search . '%'));
        }

        $properties     = $query->paginate(20)->withQueryString();
        $totalPending   = House::where('status', 'pending')->count();
        $totalAvailable = House::where('status', 'available')->count();
        $totalRented    = House::where('status', 'rented')->count();
        $totalRejected  = House::where('status', 'rejected')->count();

        return view('admin.properties', compact(
            'properties', 'totalPending', 'totalAvailable', 'totalRented', 'totalRejected'
        ));
    }

    public function approveProperty(House $house)
    {
        $house->update(['status' => 'available']);
        return back()->with('success', "Property \"{$house->title}\" approved and is now live.");
    }

    public function rejectProperty(House $house)
    {
        $house->update(['status' => 'rejected']);
        return back()->with('success', "Property \"{$house->title}\" has been rejected.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TRANSACTIONS & PAYMENTS
    // ─────────────────────────────────────────────────────────────────────────

    public function transactions(Request $request)
    {
        $query = Payment::with(['tenant', 'rental.house.owner'])->orderByDesc('payment_date');

        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('month'))  {
            $query->whereRaw("DATE_FORMAT(payment_date, '%Y-%m') = ?", [$request->month]);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('tenant', function ($tenantQuery) use ($request) {
                    $tenantQuery->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                })->orWhereHas('rental.house', function ($houseQuery) use ($request) {
                    $houseQuery->where('title', 'like', '%' . $request->search . '%');
                })->orWhere('transaction_id', 'like', '%' . $request->search . '%');
            });
        }

        $payments        = $query->paginate(20)->withQueryString();
        $totalRevenue    = Payment::where('status', 'paid')->sum('amount');
        $totalCommission = Payment::where('status', 'paid')->sum('commission_amount');
        $totalPayments   = Payment::count();

        return view('admin.transactions', compact(
            'payments', 'totalRevenue', 'totalCommission', 'totalPayments'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RENTAL ACTIVITY
    // ─────────────────────────────────────────────────────────────────────────

    public function rentalActivity(Request $request)
    {
        $query = Rental::with(['house.owner', 'tenant', 'moveOutRequests'])->orderByDesc('created_at');

        if ($request->filled('status')) { $query->where('status', $request->status); }

        $rentals        = $query->paginate(20)->withQueryString();
        $totalActive    = Rental::where('status', 'active')->count();
        $totalPending   = Rental::where('status', 'pending')->count();
        $totalExpired   = Rental::where('status', 'expired')->count();
        $totalCancelled = Rental::where('status', 'cancelled')->count();
        $openMoveOutRequests = MoveOutRequest::whereIn('status', ['requested', 'approved'])->count();

        return view('admin.rentals', compact(
            'rentals', 'totalActive', 'totalPending', 'totalExpired', 'totalCancelled', 'openMoveOutRequests'
        ));
    }

    public function verifyPayment(Payment $payment)
    {
        if ($payment->verification_status === 'verified') {
            return back()->with('error', 'Payment is already verified.');
        }

        $payment->loadMissing(['tenant', 'rental.house.owner']);
        $rental = $payment->rental;
        $house = $rental?->house;
        $owner = $house?->owner;

        if (! $rental || ! $house || ! $owner) {
            return back()->with('error', 'Payment record is missing rental, property, or owner relationship.');
        }

        DB::transaction(function () use ($payment, $rental, $house, $owner) {
            $rate = $this->commissionRate();
            $amount = (float) $payment->amount;
            $commission = round($amount * ($rate / 100), 2);
            $ownerShare = round($amount - $commission, 2);

            $payment->update([
                'commission_rate' => $rate,
                'commission_amount' => $commission,
                'owner_share_amount' => $ownerShare,
                'status' => 'paid',
                'verification_status' => 'verified',
                'verified_at' => now(),
            ]);

            AdminCommissionTransaction::updateOrCreate(
                ['payment_id' => $payment->id],
                [
                    'tenant_id' => $payment->tenant_id,
                    'owner_id' => $owner->id,
                    'property_id' => $house->id,
                    'payment_amount' => $amount,
                    'admin_commission' => $commission,
                    'owner_share' => $ownerShare,
                    'transaction_date' => now()->toDateString(),
                    'status' => 'verified',
                    'notes' => 'Payment verified by admin.',
                ]
            );

            $summary = AdminEarningsSummary::firstOrCreate(['id' => 1], [
                'total_commission_earned' => 0,
            ]);

            $summary->update([
                'total_commission_earned' => round(((float) $summary->total_commission_earned) + $commission, 2),
                'last_transaction_at' => now(),
            ]);

            $rental->update([
                'lease_status' => 'requested',
                'lease_requested_at' => now(),
            ]);
        });

        if ($payment->tenant) {
            $payment->tenant->notify(new WorkflowStatusNotification(
                'payment_verified',
                'Payment Verified',
                'Your payment for ' . ($house->title ?? ('Property #' . $rental->house_id)) . ' has been verified. Waiting for final lease approval.'
            ));
        }

        $owner->notify(new OwnerNetPaymentReceived(
            $house->title ?? ('Property #' . $house->id),
            (float) $payment->owner_share_amount
        ));

        User::where('role', 'admin')->get()->each(function ($admin) use ($house, $payment) {
            $admin->notify(new AdminCommissionReceived(
                $house->title ?? ('Property #' . $house->id),
                (float) $payment->commission_amount
            ));
        });

        return back()->with('success', 'Payment verified. Commission and owner share were recorded.');
    }

    public function rejectPayment(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'rejection_note' => 'required|string|max:500',
        ]);

        if ($payment->verification_status === 'verified') {
            return back()->with('error', 'Verified payments cannot be rejected.');
        }

        $payment->loadMissing(['tenant', 'rental.house.owner']);

        $payment->update([
            'status' => 'overdue',
            'verification_status' => 'rejected',
            'notes' => trim(($payment->notes ? $payment->notes . PHP_EOL : '') . 'Verification rejected: ' . $validated['rejection_note']),
        ]);

        AdminCommissionTransaction::updateOrCreate(
            ['payment_id' => $payment->id],
            [
                'tenant_id' => $payment->tenant_id,
                'owner_id' => $payment->rental?->house?->owner_id,
                'property_id' => $payment->rental?->house_id,
                'payment_amount' => (float) $payment->amount,
                'admin_commission' => 0,
                'owner_share' => 0,
                'transaction_date' => now()->toDateString(),
                'status' => 'rejected',
                'notes' => $validated['rejection_note'],
            ]
        );

        if ($payment->tenant) {
            $payment->tenant->notify(new WorkflowStatusNotification(
                'payment_rejected',
                'Payment Rejected',
                'Your payment proof was rejected. Reason: ' . $validated['rejection_note']
            ));
        }

        if ($payment->rental?->house?->owner) {
            $payment->rental->house->owner->notify(new WorkflowStatusNotification(
                'payment_rejected',
                'Payment Rejected',
                'Payment proof was rejected for ' . ($payment->rental->house->title ?? ('Property #' . $payment->rental->house_id)) . '.'
            ));
        }

        return back()->with('success', 'Payment proof rejected and notifications sent.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SETTINGS
    // ─────────────────────────────────────────────────────────────────────────

    public function settings()
    {
        $commissionRate = $this->commissionRate();
        return view('admin.settings', compact('commissionRate'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate(['commission_rate' => 'required|numeric|min:0|max:100']);
        DB::table('settings')->updateOrInsert(
            ['key'   => 'commission_rate'],
            ['value' => $request->commission_rate, 'updated_at' => now()]
        );
        return back()->with('success', 'Commission rate updated to ' . $request->commission_rate . '%.');
    }
}
