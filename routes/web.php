<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\TenantReviewController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/houses', [HouseController::class, 'index'])->name('houses.index');
Route::get('/houses/{house}', [HouseController::class, 'show'])->name('houses.show');

// About & Contact pages
Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/services', function () {
    return view('services');
})->name('services');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::post('/contact', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'name'    => 'required|string|max:100',
        'email'   => 'required|email|max:150',
        'subject' => 'required|string|max:100',
        'message' => 'required|string|max:2000',
    ]);

    // Message stored in session; extend with Mail::send() when mail is configured
    return redirect()->route('contact')->with('contact_success', 'Thank you, ' . e($request->name) . '! Your message has been received. We will respond within 24 hours.');
})->name('contact.send');

// Auth routes (guests only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // House management (owners)
    Route::get('/houses/create/new', [HouseController::class, 'create'])->name('houses.create');
    Route::post('/houses', [HouseController::class, 'store'])->name('houses.store');
    Route::get('/houses/{house}/edit', [HouseController::class, 'edit'])->name('houses.edit');
    Route::put('/houses/{house}', [HouseController::class, 'update'])->name('houses.update');
    Route::delete('/houses/{house}', [HouseController::class, 'destroy'])->name('houses.destroy');
    Route::get('/my-listings', [HouseController::class, 'myListings'])->name('houses.my-listings');

    // Rental routes
    Route::post('/houses/{house}/rent', [RentalController::class, 'store'])->name('rentals.store');
    Route::get('/my-rentals', [RentalController::class, 'myRentals'])->name('rentals.my-rentals');
    Route::post('/rentals/{rental}/stay-decision', [RentalController::class, 'stayDecision'])->name('rentals.stay-decision');
    Route::post('/rentals/{rental}/agreement/accept', [RentalController::class, 'acceptAgreement'])->name('rentals.agreement.accept');
    Route::post('/rentals/{rental}/move-out', [RentalController::class, 'requestMoveOut'])->name('rentals.move-out.request');
    Route::get('/lease-agreements/{leaseAgreement}/download', [RentalController::class, 'downloadLease'])->name('rentals.lease.download');
    Route::post('/rentals/{rental}/pay', [RentalController::class, 'makePayment'])->name('rentals.pay');

    // Inspection routes (tenants)
    Route::post('/inspections', [InspectionController::class, 'store'])->name('inspections.store');
    Route::post('/inspections/{inspection}/cancel', [InspectionController::class, 'cancel'])->name('inspections.cancel');
});

// Tenant Dashboard routes
Route::prefix('tenant')->name('tenant.')->middleware(['auth', 'tenant'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'tenant'])->name('dashboard');
    Route::post('/reviews', [TenantReviewController::class, 'store'])->name('reviews.store');
});

// Legacy user dashboard alias routes
Route::prefix('user')->name('user.')->middleware(['auth', 'owner'])->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('owner.dashboard'))->name('dashboard');
});

// Owner Dashboard routes
Route::prefix('owner')->name('owner.')->middleware(['auth', 'owner'])->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('dashboard');
    Route::get('/properties', [OwnerController::class, 'properties'])->name('properties');
    Route::get('/tenants',    [OwnerController::class, 'tenants'])->name('tenants');
    Route::get('/payments',   [OwnerController::class, 'payments'])->name('payments');
    Route::post('/rentals/{rental}/accept', [OwnerController::class, 'acceptRentalRequest'])->name('rentals.accept');
    Route::post('/rentals/{rental}/reject', [OwnerController::class, 'rejectRentalRequest'])->name('rentals.reject');
    Route::post('/rentals/{rental}/lease/upload', [OwnerController::class, 'uploadLeaseAgreement'])->name('rentals.lease.upload');
    Route::post('/rentals/{rental}/lease/approve', [OwnerController::class, 'approveLease'])->name('rentals.lease.approve');
    Route::post('/rentals/{rental}/lease/reject', [OwnerController::class, 'rejectLease'])->name('rentals.lease.reject');
    Route::post('/move-out-requests/{moveOutRequest}/approve', [OwnerController::class, 'approveMoveOutRequest'])->name('moveouts.approve');
    Route::post('/move-out-requests/{moveOutRequest}/complete', [OwnerController::class, 'completeMoveOutRequest'])->name('moveouts.complete');
    Route::post('/move-out-requests/{moveOutRequest}/reject', [OwnerController::class, 'rejectMoveOutRequest'])->name('moveouts.reject');
    
    // Bank details management
    Route::get('/bank-details', [OwnerController::class, 'bankDetails'])->name('bank-details');
    Route::post('/bank-details', [OwnerController::class, 'updateBankDetails'])->name('bank-details.update');
    
    // Digital lease generation
    Route::get('/rentals/{rental}/generate-lease', [OwnerController::class, 'viewGenerateLease'])->name('rentals.generate-lease');
    Route::post('/rentals/{rental}/generate-lease', [OwnerController::class, 'generateAndSendLease'])->name('rentals.generate-lease.store');
    Route::get('/rentals/{rental}/lease-preview', [OwnerController::class, 'viewLeasePreview'])->name('rentals.lease-preview');
    
    // Inspection management
    Route::get('/inspections', [OwnerController::class, 'inspections'])->name('inspections');
    Route::post('/inspections/{inspection}/approve', [OwnerController::class, 'approveInspection'])->name('inspections.approve');
    Route::post('/inspections/{inspection}/reject', [OwnerController::class, 'rejectInspection'])->name('inspections.reject');
    Route::post('/inspections/{inspection}/complete', [OwnerController::class, 'markInspectionCompleted'])->name('inspections.complete');
    
    // Payment verification
    Route::post('/payments/{payment}/verify', [OwnerController::class, 'verifyPayment'])->name('payments.verify');
    Route::get('/payments/{payment}/proof', [OwnerController::class, 'paymentProofView'])->name('payments.proof');
});

// Admin routes — requires auth + admin role
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard',                     [AdminController::class, 'dashboard'])->name('dashboard');

    // User management
    Route::get('/users',                         [AdminController::class, 'users'])->name('users');
    Route::get('/users/pending',                 [AdminController::class, 'pendingUsers'])->name('pending');
    Route::get('/owners',                        [AdminController::class, 'owners'])->name('owners');
    Route::get('/tenants',                       [AdminController::class, 'tenants'])->name('tenants');
    Route::post('/users/{user}/approve',         [AdminController::class, 'approveUser'])->name('users.approve');
    Route::post('/users/{user}/reject',          [AdminController::class, 'rejectUser'])->name('users.reject');
    Route::post('/users/{user}/activate',        [AdminController::class, 'activateUser'])->name('users.activate');
    Route::post('/users/{user}/deactivate',      [AdminController::class, 'deactivateUser'])->name('users.deactivate');

    // Property management
    Route::get('/properties',                    [AdminController::class, 'properties'])->name('properties');
    Route::post('/properties/{house}/approve',   [AdminController::class, 'approveProperty'])->name('properties.approve');
    Route::post('/properties/{house}/reject',    [AdminController::class, 'rejectProperty'])->name('properties.reject');

    // Transactions & rentals
    Route::get('/transactions',                  [AdminController::class, 'transactions'])->name('transactions');
    Route::post('/transactions/{payment}/verify', [AdminController::class, 'verifyPayment'])->name('transactions.verify');
    Route::post('/transactions/{payment}/reject', [AdminController::class, 'rejectPayment'])->name('transactions.reject');
    Route::get('/rentals',                       [AdminController::class, 'rentalActivity'])->name('rentals');

    // Settings
    Route::get('/settings',                      [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings',                     [AdminController::class, 'updateSettings'])->name('settings.update');
});
