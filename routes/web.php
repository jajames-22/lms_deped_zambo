<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

// Added Middleware to ensure only guests (non-logged-in users) see this
Route::get('/', function () {
    return view('index');
})->middleware('guest')->name('index');

// The Main Dashboard Layout
Route::middleware(['auth', 'verified'])->group(function () {
    // 🔹 This now points to the controller which will decide which layout to load
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.main');
});

// The Partials (Grouped to share middleware and name prefixes)
Route::prefix('dashboard')
    ->middleware(['auth', 'verified']) // Added 'verified' here so unverified users can't fetch partials
    ->name('dashboard.') // Prefixes names, e.g., 'dashboard.home'
    ->group(function () {

        Route::get('/home', [DashboardController::class, 'loadHomePartial'])->name('home');
        
        Route::get('/enrolled', [DashboardController::class, 'loadEnrolledPartial'])->name('enrolled');

        Route::get('/certificates', [DashboardController::class, 'loadCertificatesPartial'])->name('certificates');

        Route::get('/profile', [DashboardController::class, 'loadProfilePartial'])->name('profile');

        Route::get('/settings', [DashboardController::class, 'loadSettingsPartial'])->name('settings');

    });

require __DIR__ . '/auth.php';