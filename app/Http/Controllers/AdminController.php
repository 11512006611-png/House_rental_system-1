<?php

namespace App\Http\Controllers;

use App\Models\AdminCommissionTransaction;
use App\Models\AdminEarningsSummary;
use App\Models\House;
use App\Models\Inspection;
use App\Models\MaintenanceRequest;
use App\Models\MoveOutRequest;
use App\Models\Refund;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\User;
use App\Notifications\AdminCommissionReceived;
use App\Notifications\InspectionRequestConfirmed;
use App\Notifications\InspectionRequestRejected;
use App\Notifications\InspectionRequestRescheduled;
use App\Notifications\OwnerNetPaymentReceived;
use App\Notifications\WorkflowStatusNotification;
use App\Services\LeaseAgreementService;
use Carbon\Carbon;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
                ->whereNotIn('payment_type', ['security_deposit', 'refund'])
                ->whereYear('payment_date', $year)
                ->whereMonth('payment_date', $month)
                ->sum('amount');

            $commission = (float) Payment::where('status', 'paid')
                ->whereNotIn('payment_type', ['security_deposit', 'refund'])
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

    private function getMonthlyRentChartData(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date  = now()->subMonths($i);
            $year  = $date->year;
            $month = $date->month;

            $monthlyRent = (float) Payment::where('status', 'paid')
                ->whereIn('payment_type', ['monthly_rent', 'first_month_rent'])
                ->whereYear('payment_date', $year)
                ->whereMonth('payment_date', $month)
                ->sum('amount');

            $months[] = [
                'label' => $date->format('M Y'),
                'monthly_rent' => $monthlyRent,
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
                     ->where('payments.status', 'paid')
                     ->whereNotIn('payments.payment_type', ['security_deposit', 'refund']);
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

    private function notifyWorkflowStatus(User $user, string $type, string $title, string $message): bool
    {
        try {
            $user->notify(new WorkflowStatusNotification($type, $title, $message));
            return true;
        } catch (Throwable $e) {
            Log::warning('Workflow notification failed after status update.', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function inspectionScheduleText(Inspection $inspection): string
    {
        if ($inspection->scheduled_at) {
            return $inspection->scheduled_at->format('d M Y, h:i A');
        }

        if ($inspection->preferred_date) {
            return $inspection->preferred_date->format('d M Y') . ' (' . ($inspection->preferred_time ?? 'time not provided') . ')';
        }

        return 'schedule not set';
    }

    private function preferredInspectionSchedule(Inspection $inspection): ?Carbon
    {
        if (! $inspection->preferred_date) {
            return null;
        }

        $slot = match ($inspection->preferred_time) {
            '09:00' => '09:00:00',
            '11:00' => '11:00:00',
            '14:00' => '14:00:00',
            '16:00' => '16:00:00',
            '18:00' => '18:00:00',
            default => '09:00:00',
        };

        return Carbon::parse($inspection->preferred_date->format('Y-m-d') . ' ' . $slot);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $rate               = $this->commissionRate();
        $totalRevenue       = Payment::where('status', 'paid')
            ->whereNotIn('payment_type', ['security_deposit', 'refund'])
            ->sum('amount');
        $totalCommission    = Payment::where('status', 'paid')
            ->whereNotIn('payment_type', ['security_deposit', 'refund'])
            ->sum('commission_amount');
        $commissionThisMonth = Payment::where('status', 'paid')
            ->whereNotIn('payment_type', ['security_deposit', 'refund'])
            ->whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->sum('commission_amount');
        $netOwnerPayout = Payment::where('status', 'paid')
            ->whereNotIn('payment_type', ['security_deposit', 'refund'])
            ->selectRaw('COALESCE(SUM(amount - commission_amount), 0) as total')
            ->value('total');
        $commissionTransactions = Payment::where('status', 'paid')
            ->whereNotIn('payment_type', ['security_deposit', 'refund'])
            ->where('commission_amount', '>', 0)
            ->count();
        $totalOwners        = User::where('role', 'owner')->where('status', 'approved')->count();
        $totalTenants       = User::where('role', 'tenant')->where('status', 'approved')->count();
        $totalProperties    = House::count();
        $pendingUsers       = User::where('status', 'pending')->count();
        $pendingProperties  = House::where('status', 'pending')->count();
            $pendingRentals     = Rental::where('status', 'active')
                ->where('lease_status', 'requested')
                ->whereDoesntHave('leaseAgreement')
                ->count();
        $openMoveOutRequests = MoveOutRequest::whereIn('status', ['requested', 'approved'])->count();

        // New admin-controlled workflow counts
        $pendingInspections = Inspection::where('status', 'pending')->count();
        $pendingAgreements  = Rental::where('status', 'active')
            ->where('lease_status', 'requested')
            ->whereDoesntHave('leaseAgreement')
            ->count();
        $pendingPayments    = Payment::where('verification_status', 'pending')->count();
        $pendingAdvancePayments = Payment::where('verification_status', 'pending')
            ->whereIn('payment_type', ['first_month_rent', 'security_deposit'])
            ->count();
        $verifiedAdvancePayments = Payment::where('verification_status', 'verified')
            ->whereIn('payment_type', ['first_month_rent', 'security_deposit'])
            ->sum('amount');
        $pendingComplaints  = MaintenanceRequest::where('status', 'pending')->count();
        $pendingRefunds     = Refund::whereIn('status', ['draft', 'pending', 'approved'])->count();

        // Notification feeds
        $notifPendingUsers      = User::where('status', 'pending')->orderByDesc('created_at')->limit(5)->get();
        $notifPendingProperties = House::where('status', 'pending')->with('owner')->orderByDesc('created_at')->limit(5)->get();
        $notifPendingRentals    = Rental::where('status', 'pending')->with(['house', 'tenant'])->orderByDesc('created_at')->limit(5)->get();
            $notifPendingRentals    = Rental::where('status', 'active')
                ->where('lease_status', 'requested')
                ->whereDoesntHave('leaseAgreement')
                ->with(['house', 'tenant'])
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        $notifPendingAgreements  = Rental::where('status', 'active')
            ->where('lease_status', 'requested')
            ->whereDoesntHave('leaseAgreement')
            ->with(['house', 'tenant'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        $notifMoveOutRequests   = MoveOutRequest::with(['tenant', 'house'])
            ->whereIn('status', ['requested', 'approved'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        $notifRefunds = Refund::with(['tenant', 'house'])
            ->whereIn('status', ['draft', 'pending', 'approved'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        $notifAdvancePayments = Payment::with(['tenant', 'rental.house'])
            ->where('verification_status', 'pending')
            ->whereIn('payment_type', ['first_month_rent', 'security_deposit'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Recent transactions
        $recentTransactions = Payment::with(['tenant', 'rental.house.owner'])
            ->where('status', 'paid')
            ->whereNotIn('payment_type', ['security_deposit', 'refund'])
            ->orderByDesc('payment_date')
            ->limit(8)
            ->get();

        // Owner summary (top 5)
        $ownersSummary = $this->getOwnersWithStats()->limit(5)->get();

        // Monthly chart data
        $chartData = $this->getMonthlyChartData();
        $rentChartData = $this->getMonthlyRentChartData();

        return view('admin.dashboard', compact(
            'rate', 'totalRevenue', 'totalCommission',
            'commissionThisMonth', 'netOwnerPayout', 'commissionTransactions',
            'totalOwners', 'totalTenants', 'totalProperties',
            'pendingUsers', 'pendingProperties', 'pendingRentals', 'openMoveOutRequests',
            'pendingInspections', 'pendingAgreements', 'pendingPayments', 'pendingComplaints',
            'pendingRefunds', 'pendingAdvancePayments', 'verifiedAdvancePayments',
            'notifPendingUsers', 'notifPendingProperties', 'notifPendingRentals', 'notifPendingAgreements', 'notifMoveOutRequests', 'notifRefunds', 'notifAdvancePayments',
            'notifPendingRentals', 'notifPendingAgreements', 'notifMoveOutRequests', 'notifRefunds', 'notifAdvancePayments',
            'recentTransactions', 'ownersSummary', 'chartData', 'rentChartData'
        ));
    }

    public function reports(Request $request)
    {
        $monthsBack = max(1, min((int) $request->query('months', 12), 24));
        $startDate = now()->startOfMonth()->subMonths($monthsBack - 1);

        $summary = [
            'total_revenue' => (float) Payment::where('status', 'paid')
                ->whereNotIn('payment_type', ['security_deposit', 'refund'])
                ->whereDate('payment_date', '>=', $startDate)
                ->sum('amount'),
            'total_commission' => (float) Payment::where('status', 'paid')
                ->whereNotIn('payment_type', ['security_deposit', 'refund'])
                ->whereDate('payment_date', '>=', $startDate)
                ->sum('commission_amount'),
            'paid_transactions' => (int) Payment::where('status', 'paid')
                ->whereDate('payment_date', '>=', $startDate)
                ->count(),
            'verified_payments' => (int) Payment::where('verification_status', 'verified')
                ->whereDate('payment_date', '>=', $startDate)
                ->count(),
            'pending_verifications' => (int) Payment::where('verification_status', 'pending')->count(),
            'pending_refunds' => (int) Refund::whereIn('status', ['draft', 'pending', 'approved'])->count(),
        ];

        $monthlyRows = Payment::query()
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as ym")
            ->selectRaw('SUM(CASE WHEN status = ? AND payment_type NOT IN (?, ?) THEN amount ELSE 0 END) as revenue', ['paid', 'security_deposit', 'refund'])
            ->selectRaw('SUM(CASE WHEN status = ? AND payment_type NOT IN (?, ?) THEN commission_amount ELSE 0 END) as commission', ['paid', 'security_deposit', 'refund'])
            ->selectRaw('SUM(CASE WHEN verification_status = ? THEN 1 ELSE 0 END) as verified_count', ['verified'])
            ->selectRaw('SUM(CASE WHEN verification_status = ? THEN 1 ELSE 0 END) as pending_count', ['pending'])
            ->whereDate('payment_date', '>=', $startDate)
            ->groupBy('ym')
            ->orderBy('ym', 'desc')
            ->get();

        $recentPayments = Payment::with(['tenant', 'rental.house'])
            ->whereDate('payment_date', '>=', $startDate)
            ->orderByDesc('payment_date')
            ->limit(15)
            ->get();

        $chartData = $this->getMonthlyChartData();

        return view('admin.reports', compact(
            'monthsBack',
            'startDate',
            'summary',
            'monthlyRows',
            'recentPayments',
            'chartData'
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
                                      ->orWhere('email', 'like', '%' . $request->search . '%')
                                      ->orWhere('username', 'like', '%' . $request->search . '%')
                                      ->orWhere('phone', 'like', '%' . $request->search . '%'));
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

    public function showUserProfile(User $user)
    {
        abort_unless(in_array($user->role, ['owner', 'tenant'], true), 404);

        $user->loadCount(['houses as properties_count', 'rentals']);

        $latestRental = Rental::with('house')
            ->where('tenant_id', $user->id)
            ->latest()
            ->first();

        return view('admin.user-profile', compact('user', 'latestRental'));
    }

    public function approveUser(User $user)
    {
        $user->update(['status' => 'approved']);

        $notified = $this->notifyWorkflowStatus(
            $user,
            'user_approved',
            'Account Approved',
            'Your account has been approved by the administrator and is now active. You can log in and access the service.'
        );

        if (! $notified) {
            return back()->with('warning', "User \"{$user->name}\" was approved and saved, but notification email could not be sent.");
        }

        return back()->with('success', "User \"{$user->name}\" has been approved and can now log in.");
    }

    public function rejectUser(User $user)
    {
        $user->update(['status' => 'rejected']);

        $notified = $this->notifyWorkflowStatus(
            $user,
            'user_rejected',
            'Account Rejected',
            'Your account registration has been rejected by the administrator. Please contact support for details.'
        );

        if (! $notified) {
            return back()->with('warning', "User \"{$user->name}\" was rejected and saved, but notification email could not be sent.");
        }

        return back()->with('success', "User \"{$user->name}\" has been rejected.");
    }

    public function activateUser(User $user)
    {
        $user->update(['status' => 'approved']);

        $notified = $this->notifyWorkflowStatus(
            $user,
            'user_activated',
            'Account Activated',
            'Your account has been reactivated by an administrator. You can now access the platform again.'
        );

        if (! $notified) {
            return back()->with('warning', "User \"{$user->name}\" was activated and saved, but notification email could not be sent.");
        }

        return back()->with('success', "User \"{$user->name}\" is now active.");
    }

    public function deactivateUser(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot suspend an admin account.');
        }

        $user->update(['status' => 'suspended']);

        $notified = $this->notifyWorkflowStatus(
            $user,
            'user_suspended',
            'Account Suspended',
            'Your account has been temporarily suspended by an administrator. Please contact support for more information.'
        );

        if (! $notified) {
            return back()->with('warning', "User \"{$user->name}\" was suspended and saved, but notification email could not be sent.");
        }

        return back()->with('success', "User \"{$user->name}\" has been suspended.");
    }

    public function deleteUser(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot delete an admin account.');
        }

        if (! $user->isTenant()) {
            return back()->with('error', 'Only tenant accounts may be deleted from this page.');
        }

        if ($user->rentals()->exists()) {
            return back()->with('error', 'Tenant cannot be deleted while rental records exist. Please resolve their rentals first.');
        }

        $user->delete();

        return back()->with('success', 'Tenant account deleted successfully.');
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

    public function showProperty(House $house)
    {
        $house->load([
            'owner',
            'locationModel',
            'houseImages',
            'inspectedByAdmin',
            'rentals.tenant',
        ]);

        return view('admin.property-show', compact('house'));
    }

    public function updatePropertyImage(Request $request, House $house)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($house->image) {
            Storage::disk('public')->delete($house->image);
        }

        $path = $validated['image']->store('houses/main', 'public');
        $house->update(['image' => $path]);

        return back()->with('success', 'Property main photo updated successfully.');
    }

    public function approveProperty(Request $request, House $house)
    {
        if (! in_array($house->status, ['pending', 'rejected'], true)) {
            return back()->with('error', 'Only pending or rejected properties can be approved.');
        }

        if (! $house->inspection_scheduled_at) {
            return back()->with('error', 'Please schedule an inspection date and time before approving this property.');
        }

        $validated = $request->validate([
            'inspection_confirmed' => 'accepted',
            'admin_commission_rate' => 'required|numeric|min:0|max:100',
            'admin_inspection_notes' => 'required|string|min:3|max:2000',
        ], [
            'inspection_confirmed.accepted' => 'Please confirm the physical inspection before publishing this property.',
            'admin_inspection_notes.min' => 'Inspection notes must be at least 3 characters.',
        ]);

        $house->update([
            'status' => 'available',
            'admin_commission_rate' => (float) $validated['admin_commission_rate'],
            'inspected_by_admin_id' => Auth::id(),
            'inspected_at' => now(),
            'admin_inspection_notes' => trim((string) $validated['admin_inspection_notes']),
        ]);

        $house->loadMissing('owner');
        if ($house->owner) {
            $house->owner->notify(new WorkflowStatusNotification(
                'property_approved',
                'Property Approved',
                'Your property "' . $house->title . '" has been inspected, approved, and is now live. Admin commission is set to ' . number_format((float) $house->admin_commission_rate, 2) . '%. Property management is now handled by admin.'
            ));
        }

        return back()->with('success', "Property \"{$house->title}\" approved and is now live.");
    }

    public function rejectProperty(Request $request, House $house)
    {
        if (! in_array($house->status, ['pending', 'rejected'], true)) {
            return back()->with('error', 'Only pending or rejected properties can be rejected.');
        }

        if (! $house->inspection_scheduled_at) {
            return back()->with('error', 'Please schedule an inspection date and time before rejecting this property.');
        }

        $validated = $request->validate([
            'admin_inspection_notes' => 'required|string|min:3|max:2000',
        ], [
            'admin_inspection_notes.min' => 'Rejection reason must be at least 3 characters.',
        ]);

        $house->update([
            'status' => 'rejected',
            'inspected_by_admin_id' => Auth::id(),
            'inspected_at' => now(),
            'admin_inspection_notes' => trim((string) $validated['admin_inspection_notes']),
        ]);

        $house->loadMissing('owner');
        if ($house->owner) {
            $house->owner->notify(new WorkflowStatusNotification(
                'property_rejected',
                'Property Rejected',
                'Your property "' . $house->title . '" was rejected after admin inspection. Reason: ' . trim((string) $validated['admin_inspection_notes'])
            ));
        }

        return back()->with('success', "Property \"{$house->title}\" has been rejected.");
    }

    public function schedulePropertyInspection(Request $request, House $house)
    {
        if (! in_array($house->status, ['pending', 'rejected'], true)) {
            return back()->with('error', 'Inspection can only be scheduled for pending or rejected properties.');
        }

        $validated = $request->validate([
            'inspection_scheduled_at' => 'required|date|after_or_equal:now',
        ], [
            'inspection_scheduled_at.after_or_equal' => 'Inspection date and time must be now or in the future.',
        ]);

        $scheduledAt = \Carbon\Carbon::parse($validated['inspection_scheduled_at']);

        $house->update([
            'inspection_scheduled_at' => $scheduledAt,
            'inspection_schedule_acknowledged_at' => null,
        ]);

        $house->loadMissing('owner');
        if ($house->owner) {
            $house->owner->notify(new WorkflowStatusNotification(
                'property_inspection_scheduled',
                'Property Inspection Scheduled',
                'Admin scheduled inspection for your property "' . $house->title . '" on ' . $scheduledAt->format('d M Y, h:i A') . '. Please ensure access and property readiness.',
                ['scheduled_at' => $scheduledAt->toDateTimeString()]
            ));
        }

        return back()->with('success', 'Inspection date and time scheduled successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TRANSACTIONS & PAYMENTS
    // ─────────────────────────────────────────────────────────────────────────

    public function transactions(Request $request)
    {
        $query = Payment::with(['tenant', 'rental.house.owner'])->orderByDesc('payment_date');

        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('type')) {
            if ($request->type === 'first_month_rent') {
                $query->where('payment_type', 'first_month_rent');
            } elseif ($request->type === 'security_deposit') {
                $query->where('payment_type', 'security_deposit');
            } elseif ($request->type === 'monthly_rent') {
                $query->where('payment_type', 'monthly_rent');
            } elseif ($request->type === 'refund') {
                $query->where('payment_type', 'refund');
            }
        }
        if ($request->filled('month'))  {
            $query->whereRaw("DATE_FORMAT(COALESCE(billing_month, payment_date), '%Y-%m') = ?", [$request->month]);
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

    public function inspections(Request $request)
    {
        $query = Inspection::with(['tenant', 'house.owner', 'handledByAdmin'])
            ->orderByRaw("FIELD(status, 'pending', 'confirmed', 'completed', 'rescheduled', 'cancelled', 'rejected')")
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('tenant', function ($tenantQuery) use ($search) {
                    $tenantQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })->orWhereHas('house', function ($houseQuery) use ($search) {
                    $houseQuery->where('title', 'like', '%' . $search . '%')
                        ->orWhere('location', 'like', '%' . $search . '%');
                });
            });
        }

        $inspections = $query->paginate(20)->withQueryString();

        $pendingCount = Inspection::where('status', 'pending')->count();
        $confirmedCount = Inspection::where('status', 'confirmed')->count();
        $completedCount = Inspection::where('status', 'completed')->count();
        $rescheduledCount = Inspection::where('status', 'rescheduled')->count();
        $cancelledCount = Inspection::where('status', 'cancelled')->count();
        $rejectedCount = Inspection::where('status', 'rejected')->count();

        return view('admin.inspections', compact(
            'inspections',
            'pendingCount',
            'confirmedCount',
            'completedCount',
            'rescheduledCount',
            'cancelledCount',
            'rejectedCount'
        ));
    }

    public function confirmInspection(Inspection $inspection)
    {
        if (in_array($inspection->status, ['rejected', 'cancelled'], true)) {
            return back()->with('error', 'Rejected or cancelled inspection requests cannot be confirmed.');
        }

        $scheduledAt = $inspection->scheduled_at ?? $this->preferredInspectionSchedule($inspection);

        if (! $scheduledAt) {
            return back()->with('error', 'Inspection schedule is missing. Please reschedule it before confirming.');
        }

        $inspection->update([
            'status' => 'confirmed',
            'scheduled_at' => $scheduledAt,
            'admin_message' => null,
            'handled_by_admin_id' => Auth::id(),
            'handled_at' => now(),
        ]);

        $inspection->loadMissing(['tenant', 'house']);

        if ($inspection->tenant) {
            $inspection->tenant->notify(new InspectionRequestConfirmed(
                $inspection->house?->title ?? ('Property #' . $inspection->house_id),
                $this->inspectionScheduleText($inspection)
            ));
        }

        return back()->with('success', 'Inspection request confirmed and tenant notified.');
    }

    public function rescheduleInspection(Request $request, Inspection $inspection)
    {
        if (in_array($inspection->status, ['rejected', 'cancelled'], true)) {
            return back()->with('error', 'Rejected or cancelled inspection requests cannot be rescheduled.');
        }

        $validated = $request->validate([
            'scheduled_at' => 'required|date|after_or_equal:now',
            'admin_message' => 'required|string|min:3|max:2000',
        ], [
            'scheduled_at.after_or_equal' => 'The new inspection date and time must be now or in the future.',
            'admin_message.min' => 'Please provide a short message for the tenant.',
        ]);

        $scheduledAt = Carbon::parse($validated['scheduled_at']);

        $inspection->update([
            'status' => 'rescheduled',
            'scheduled_at' => $scheduledAt,
            'admin_message' => trim((string) $validated['admin_message']),
            'handled_by_admin_id' => Auth::id(),
            'handled_at' => now(),
        ]);

        $inspection->loadMissing(['tenant', 'house']);

        if ($inspection->tenant) {
            $inspection->tenant->notify(new InspectionRequestRescheduled(
                $inspection->house?->title ?? ('Property #' . $inspection->house_id),
                $scheduledAt->format('d M Y, h:i A'),
                trim((string) $validated['admin_message'])
            ));
        }

        return back()->with('success', 'Inspection request rescheduled and tenant notified.');
    }

    public function rejectInspection(Request $request, Inspection $inspection)
    {
        if ($inspection->status === 'cancelled') {
            return back()->with('error', 'Cancelled inspection requests cannot be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:3|max:2000',
        ], [
            'rejection_reason.min' => 'Please provide a valid rejection reason.',
        ]);

        $reason = trim((string) $validated['rejection_reason']);

        $inspection->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'admin_message' => null,
            'scheduled_at' => null,
            'handled_by_admin_id' => Auth::id(),
            'handled_at' => now(),
        ]);

        $inspection->loadMissing(['tenant', 'house']);

        if ($inspection->tenant) {
            $inspection->tenant->notify(new InspectionRequestRejected(
                $inspection->house?->title ?? ('Property #' . $inspection->house_id),
                $reason
            ));
        }

        return back()->with('success', 'Inspection request rejected and tenant notified.');
    }

    public function cancelInspection(Request $request, Inspection $inspection)
    {
        if (in_array($inspection->status, ['cancelled', 'rejected'], true)) {
            return back()->with('error', 'Inspection request is already closed.');
        }

        $validated = $request->validate([
            'cancel_reason' => 'nullable|string|max:2000',
        ]);

        $inspection->update([
            'status' => 'cancelled',
            'admin_message' => null,
            'scheduled_at' => null,
            'rejection_reason' => filled($validated['cancel_reason'] ?? null)
                ? trim((string) $validated['cancel_reason'])
                : null,
            'handled_by_admin_id' => Auth::id(),
            'handled_at' => now(),
        ]);

        return back()->with('success', 'Inspection request cancelled successfully.');
    }

    public function deleteInspection(Inspection $inspection)
    {
        $inspection->delete();

        return back()->with('success', 'Inspection request deleted successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RENTAL ACTIVITY
    // ─────────────────────────────────────────────────────────────────────────

    public function rentalActivity(Request $request)
    {
        $query = Rental::with(['house.owner', 'tenant', 'moveOutRequests', 'leaseAgreement'])->orderByDesc('created_at');

        if ($request->boolean('lease_queue')) {
            $query->where('status', 'active')
                ->where('stay_decision', 'yes')
                ->where(function ($statusQuery) {
                    $statusQuery->whereNull('lease_status')
                        ->orWhere('lease_status', '')
                        ->orWhere('lease_status', 'not_requested')
                        ->orWhere('lease_status', 'requested');
                })
                ->whereDoesntHave('leaseAgreement');
        }

        if ($request->filled('status')) { $query->where('status', $request->status); }

        if ($request->filled('stay_decision')) {
            if ($request->stay_decision === 'pending') {
                $query->whereNull('stay_decision');
            } else {
                $query->where('stay_decision', $request->stay_decision);
            }
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('tenant', function ($tenantQuery) use ($search) {
                    $tenantQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })->orWhereHas('house', function ($houseQuery) use ($search) {
                    $houseQuery->where('title', 'like', '%' . $search . '%')
                        ->orWhere('location', 'like', '%' . $search . '%');
                })->orWhereHas('house.owner', function ($ownerQuery) use ($search) {
                    $ownerQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            });
        }

        $rentals        = $query->paginate(20)->withQueryString();
        $totalActive    = Rental::where('status', 'active')->count();
        $totalPending   = Rental::where('status', 'pending')->count();
        $totalExpired   = Rental::where('status', 'expired')->count();
        $totalCancelled = Rental::where('status', 'cancelled')->count();
        $stayYesCount = Rental::where('stay_decision', 'yes')->count();
        $stayNoCount = Rental::where('stay_decision', 'no')->count();
        $stayPendingCount = Rental::whereNull('stay_decision')->count();
        $pendingLeaseUploads = Rental::where('status', 'active')
            ->where('stay_decision', 'yes')
            ->where(function ($statusQuery) {
                $statusQuery->whereNull('lease_status')
                    ->orWhere('lease_status', '')
                    ->orWhere('lease_status', 'not_requested')
                    ->orWhere('lease_status', 'requested');
            })
            ->whereDoesntHave('leaseAgreement')
            ->count();
        $openMoveOutRequests = MoveOutRequest::whereIn('status', ['requested', 'approved'])->count();

        return view('admin.rentals', compact(
            'rentals',
            'totalActive',
            'totalPending',
            'totalExpired',
            'totalCancelled',
            'stayYesCount',
            'stayNoCount',
            'stayPendingCount',
            'pendingLeaseUploads',
            'openMoveOutRequests'
        ));
    }

    public function showLeaseUploadForm(Rental $rental)
    {
        $rental->loadMissing(['house.owner', 'tenant', 'leaseAgreement']);

        if (! $rental->house || ! $rental->tenant) {
            return back()->with('error', 'Rental is missing house or tenant relation.');
        }

        if ($rental->status !== 'active' || $rental->stay_decision !== 'yes') {
            return back()->with('error', 'Lease upload is only available for active rentals after the tenant chooses Stay.');
        }

        if ($rental->leaseAgreement) {
            return redirect()->route('rentals.lease.download', $rental->leaseAgreement)
                ->with('success', 'Lease agreement is already uploaded.');
        }

        return view('admin.lease-upload', compact('rental'));
    }

    public function uploadLeaseAgreement(Request $request, Rental $rental)
    {
        $rental->loadMissing(['house.owner', 'tenant']);

        if (! $rental->house || ! $rental->tenant) {
            return back()->with('error', 'Rental is missing house or tenant relation.');
        }

        if ($rental->status !== 'active') {
            return back()->with('error', 'Lease can only be uploaded for active rentals.');
        }

        if ($rental->stay_decision !== 'yes') {
            return back()->with('error', 'Tenant must select Stay before lease upload.');
        }

        $validated = $request->validate([
            'lease_file' => 'required|file|mimes:pdf|max:10240',
            'monthly_rent' => 'nullable|numeric|min:1',
            'security_deposit_amount' => 'nullable|numeric|min:0',
            'duration_months' => 'nullable|integer|min:1|max:60',
            'lease_start_date' => 'nullable|date',
        ]);

        $storedPath = $request->file('lease_file')->store('lease-agreements', 'public');
        $monthlyRent = (float) ($validated['monthly_rent'] ?? $rental->monthly_rent ?? $rental->house->price ?? 0);
        if ($monthlyRent <= 0) {
            return back()->withErrors(['monthly_rent' => 'Monthly rent is required when rental/house rent is not set.'])->withInput();
        }

        $securityDeposit = (float) ($validated['security_deposit_amount'] ?? $rental->house->security_deposit_amount ?? 0);
        $durationMonths = (int) ($validated['duration_months'] ?? 12);
        $leaseStartDate = Carbon::parse($validated['lease_start_date'] ?? $rental->rental_date ?? now()->toDateString())->startOfDay();
        $leaseEndDate = $leaseStartDate->copy()->addMonthsNoOverflow($durationMonths);
        $twoMonthAdvance = (float) ($monthlyRent * 2);

        if ($existingLease = $rental->leaseAgreement) {
            if (! empty($existingLease->file_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($existingLease->file_path);
            }
        }

        $rental->update([
            'monthly_rent' => $monthlyRent,
            'lease_status' => 'requested',
            'lease_requested_at' => $rental->lease_requested_at ?? now(),
            'lease_reviewed_at' => null,
        ]);

        $rental->leaseAgreement()->updateOrCreate(
            ['rental_id' => $rental->id],
            [
                'owner_id' => $rental->house->owner_id,
                'tenant_id' => $rental->tenant_id,
                'house_id' => $rental->house_id,
                'file_path' => $storedPath,
                'original_name' => $request->file('lease_file')->getClientOriginalName(),
                'monthly_rent' => $monthlyRent,
                'deposit_amount' => $twoMonthAdvance,
                'security_deposit_amount' => $securityDeposit,
                'duration_months' => $durationMonths,
                'status' => 'sent',
                'payment_status' => 'pending',
                'tenant_review_status' => 'pending',
                'tenant_reviewed_at' => null,
                'tenant_review_note' => null,
                'lease_start_date' => $leaseStartDate->toDateString(),
                'lease_end_date' => $leaseEndDate->toDateString(),
                'tenant_signature_name' => null,
                'tenant_signed_at' => null,
                'uploaded_at' => now(),
            ]
        );

        $rental->tenant->notify(new WorkflowStatusNotification(
            'lease_sent_by_admin',
            'Lease Uploaded By Admin',
            'Admin uploaded lease agreement for ' . ($rental->house->title ?? ('Property #' . $rental->house_id))
            . '. Please review and accept/reject it first. Advance due after acceptance: Nu. ' . number_format($twoMonthAdvance, 0)
        ));

        return redirect()
            ->route('admin.rentals', ['lease_queue' => 1])
            ->with('success', 'Lease uploaded and sent to tenant dashboard for review.');
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
        $isTrustPayment = in_array($payment->payment_type, ['security_deposit', 'refund'], true) || (bool) $payment->held_by_admin;
        $billingMonthLabel = $payment->billing_month?->format('F Y') ?? $payment->payment_date?->format('F Y') ?? now()->format('F Y');

        if (! $rental || ! $house || ! $owner) {
            return back()->with('error', 'Payment record is missing rental, property, or owner relationship.');
        }

        if (! $isTrustPayment && ! $rental->leaseAgreement) {
            return back()->with('error', 'Lease agreement must be uploaded before verifying advance payment.');
        }

        DB::transaction(function () use ($payment, $rental, $house, $owner, $isTrustPayment, $billingMonthLabel) {
            $amount = (float) $payment->amount;

            if ($isTrustPayment) {
                $payment->update([
                    'commission_rate' => 0,
                    'commission_amount' => 0,
                    'owner_share_amount' => 0,
                    'held_by_admin' => true,
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
                        'admin_commission' => 0,
                        'owner_share' => 0,
                        'transaction_date' => now()->toDateString(),
                        'status' => 'verified',
                        'notes' => 'Trust payment verified by admin and held for potential refund.',
                    ]
                );
            } else {
                $rate = $house->admin_commission_rate !== null
                    ? (float) $house->admin_commission_rate
                    : $this->commissionRate();
                $commission = round($amount * ($rate / 100), 2);
                $ownerShare = round($amount - $commission, 2);

                $payment->update([
                    'billing_month' => $payment->billing_month ?? $payment->payment_date ?? now()->startOfMonth()->toDateString(),
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
                        'notes' => 'Payment verified by admin for ' . $billingMonthLabel . '.',
                    ]
                );

                $summary = AdminEarningsSummary::firstOrCreate(['id' => 1], [
                    'total_commission_earned' => 0,
                ]);

                $summary->update([
                    'total_commission_earned' => round(((float) $summary->total_commission_earned) + $payment->commission_amount, 2),
                    'last_transaction_at' => now(),
                ]);

                if ($rental->leaseAgreement) {
                    $rental->leaseAgreement->update([
                        'payment_status' => 'paid',
                        'uploaded_at' => $rental->leaseAgreement->uploaded_at ?? now(),
                    ]);
                }

                // Create or refresh lease agreement with updated payment info
                LeaseAgreementService::createOrRefreshAgreement($rental);
            }

            $rental->update([
                'lease_requested_at' => $rental->lease_requested_at ?? now(),
                'lease_reviewed_at' => $isTrustPayment ? $rental->lease_reviewed_at : now(),
            ]);

            // Check if all advance payments are verified
            $totalAdvancePayments = $rental->payments()
                ->whereIn('payment_type', ['first_month_rent', 'security_deposit'])
                ->count();

            $verifiedAdvancePayments = $rental->payments()
                ->whereIn('payment_type', ['first_month_rent', 'security_deposit'])
                ->where('verification_status', 'verified')
                ->count();

            if ($totalAdvancePayments > 0 && $totalAdvancePayments === $verifiedAdvancePayments) {
                $rental->update(['lease_status' => 'approved']);
            }
        });

        if ($payment->tenant) {
            $payment->tenant->notify(new WorkflowStatusNotification(
                'payment_verified',
                'Payment Verified',
                'Your payment for ' . $billingMonthLabel . ' for ' . ($house->title ?? ('Property #' . $rental->house_id)) . ' has been verified. The lease is now active.'
            ));
        }

        if (! in_array($payment->payment_type, ['security_deposit', 'refund'], true) && ! $payment->held_by_admin) {
            $owner->notify(new OwnerNetPaymentReceived(
                ($house->title ?? ('Property #' . $house->id)) . ' - ' . $billingMonthLabel,
                (float) $payment->owner_share_amount
            ));

            User::where('role', 'admin')->get()->each(function ($admin) use ($house, $payment) {
                $admin->notify(new AdminCommissionReceived(
                    ($house->title ?? ('Property #' . $house->id)) . ' - ' . ($payment->billing_month?->format('F Y') ?? $payment->payment_date?->format('F Y') ?? now()->format('F Y')),
                    (float) $payment->commission_amount
                ));
            });
        }

        return back()->with('success', $isTrustPayment
            ? 'Trust payment verified. The security deposit will be held by admin and not transferred to the owner.'
            : 'Payment verified. Commission and owner share were recorded.');
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

    public function deletePayment(Payment $payment)
    {
        if ($payment->verification_status === 'verified') {
            return back()->with('error', 'Verified payments cannot be deleted.');
        }

        DB::transaction(function () use ($payment) {
            if (! empty($payment->payment_proof_path)) {
                Storage::disk('public')->delete($payment->payment_proof_path);
            }

            if ($payment->commissionTransaction) {
                $payment->commissionTransaction->delete();
            }

            $payment->delete();
        });

        return back()->with('success', 'Payment record deleted successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MAINTENANCE REQUESTS
    // ─────────────────────────────────────────────────────────────────────────

    public function maintenanceRequests(Request $request)
    {
        $query = MaintenanceRequest::with(['tenant', 'house', 'owner'])
            ->orderByRaw("FIELD(status, 'pending', 'in_progress', 'resolved', 'rejected')")
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search by tenant name, property title, or house ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('tenant', function ($tenantQuery) use ($search) {
                    $tenantQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })
                ->orWhereHas('house', function ($houseQuery) use ($search) {
                    $houseQuery->where('title', 'like', '%' . $search . '%');
                });
            });
        }

        $maintenanceRequests = $query->paginate(15)->withQueryString();

        $statusCounts = [
            'pending' => MaintenanceRequest::where('status', 'pending')->count(),
            'approved_for_repair' => MaintenanceRequest::where('status', 'approved_for_repair')->count(),
            'under_repair' => MaintenanceRequest::where('status', 'under_repair')->count(),
            'resolved' => MaintenanceRequest::where('status', 'resolved')->count(),
            'rejected' => MaintenanceRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.maintenance', compact('maintenanceRequests', 'statusCounts'));
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

    // ─────────────────────────────────────────────────────────────────────────
    // MONTHLY SETTLEMENTS
    // ─────────────────────────────────────────────────────────────────────────

    public function monthlySettlements()
    {
        $owners = User::where('role', 'owner')->with(['houses.rentals.payments'])->get();

        $settlements = [];
        $currentMonth = now()->format('Y-m');

        foreach ($owners as $owner) {
            $monthlyData = \App\Models\MonthlySettlement::calculateMonthlySettlement($owner->id, $currentMonth);

            $settlements[] = [
                'owner' => $owner,
                'total_rent_collected' => $monthlyData['total_rent_collected'],
                'commission_amount' => $monthlyData['commission_amount'],
                'net_amount' => $monthlyData['net_amount'],
                'settlement_status' => $monthlyData['settlement_status'],
                'settlement_date' => $monthlyData['settlement_date'],
                'settlement_id' => $monthlyData['settlement_id'],
            ];
        }

        return view('admin.settlements.index', compact('settlements', 'currentMonth'));
    }

    public function ownerSettlementDetails(User $owner)
    {
        $currentMonth = now()->format('Y-m');
        $monthlyData = \App\Models\MonthlySettlement::calculateMonthlySettlement($owner->id, $currentMonth);

        // Get detailed payment breakdown
        $payments = Payment::whereHas('rental.house', function ($query) use ($owner) {
            $query->where('owner_id', $owner->id);
        })
        ->where('status', 'paid')
        ->whereYear('payment_date', now()->year)
        ->whereMonth('payment_date', now()->month)
        ->with(['rental.house', 'rental.tenant'])
        ->orderBy('payment_date', 'desc')
        ->get();

        return view('admin.settlements.owner-details', compact('owner', 'monthlyData', 'payments', 'currentMonth'));
    }

    public function processMonthlySettlement(Request $request, User $owner)
    {
        $rules = [
            'action' => 'required|in:settle,transfer',
            'settlement_month' => 'required|date_format:Y-m',
        ];

        // If transfer, allow optional proof image and notes (admin should not change owner bank/account)
        if ($request->input('action') === 'transfer') {
            $rules['transfer_proof'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120';
            $rules['transfer_notes'] = 'nullable|string|max:1000';
        }

        $request->validate($rules);

        $settlementMonth = $request->settlement_month;
        $action = $request->action;

        DB::beginTransaction();
        try {
            $settlement = \App\Models\MonthlySettlement::firstOrCreate(
                [
                    'owner_id' => $owner->id,
                    'settlement_month' => $settlementMonth,
                ],
                [
                    'total_rent_collected' => 0,
                    'commission_rate' => $this->commissionRate(),
                    'commission_amount' => 0,
                    'net_amount' => 0,
                    'status' => 'pending',
                    'processed_at' => null,
                    'transferred_at' => null,
                ]
            );

            // Recalculate amounts
            $monthlyData = \App\Models\MonthlySettlement::calculateMonthlySettlement($owner->id, $settlementMonth);

            $settlement->update([
                'total_rent_collected' => $monthlyData['total_rent_collected'],
                'commission_amount' => $monthlyData['commission_amount'],
                'net_amount' => $monthlyData['net_amount'],
            ]);

            if ($action === 'settle') {
                $settlement->update([
                    'status' => 'settled',
                    'processed_at' => now(),
                    'processed_by' => Auth::id(),
                    'transfer_notes' => 'Settlement reviewed and marked ready for transfer by admin.',
                ]);
            } elseif ($action === 'transfer') {
                // Handle optional proof upload
                $proofPath = null;
                if ($request->hasFile('transfer_proof')) {
                    $proofPath = $request->file('transfer_proof')->store('transfers', 'public');
                }

                // Use owner's bank details from database; admin should not edit account numbers here.
                $ownerAccountRaw = (string) ($owner->account_number ?? '');
                $digitsOnly = preg_replace('/\D+/', '', $ownerAccountRaw);
                $last4 = strlen($digitsOnly) >= 4 ? substr($digitsOnly, -4) : $digitsOnly;
                $maskedAccount = $last4 ? '**** **** ' . $last4 : null;

                $settlement->update([
                    'status' => 'transferred',
                    'processed_at' => now(),
                    'transferred_at' => now(),
                    'processed_by' => Auth::id(),
                    'transfer_notes' => trim((string) ($request->input('transfer_notes') ?: 'Transfer recorded by admin via bank/direct payout.')),
                    'transfer_proof_path' => $proofPath,
                    'owner_account_number' => $maskedAccount,
                ]);

                // Notify owner
                $owner->notify(new \App\Notifications\OwnerSettlementTransferred($settlement));
            }

            DB::commit();

            $message = $action === 'settle'
                ? 'Monthly settlement processed successfully.'
                : 'Settlement transfer recorded successfully.';

            return back()->with('success', $message);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Monthly settlement processing failed', [
                'owner_id' => $owner->id,
                'settlement_month' => $settlementMonth,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to process monthly settlement. Please try again.']);
        }
    }

    public function settlementReceipt(\App\Models\MonthlySettlement $settlement)
    {
        return view('admin.settlements.receipt', compact('settlement'));
    }
}
