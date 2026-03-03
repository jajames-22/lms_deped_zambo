<?php

use Illuminate\Support\Facades\Route;

// Added Middleware to ensure only guests (non-logged-in users) see this
Route::get('/', function () {
    return view('index');
})->middleware('guest')->name('index');

// The Main Dashboard Layout
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.dashboard');
    });
});
// The Partials (Grouped to share middleware and name prefixes)
Route::prefix('dashboard')
    ->middleware('auth') // Applies 'auth' to all routes in this group
    ->name('dashboard.') // Prefixes names, e.g., 'dashboard.home'
    ->group(function () {

        Route::get('/home', function () {
            return view('dashboard.partials.home');
        })->name('home');

        Route::get('/courses', function () {
            return view('dashboard.partials.courses');
        })->name('courses'); // FIXED: Was previously named 'dashboard'
    
        Route::get('/assignments', function () {
            return view('dashboard.partials.assignments');
        })->name('assignments');

        Route::get('/statistics', function () {
            return view('dashboard.partials.statistics');
        })->name('statistics');

        Route::get('/settings', function () {
            return view('dashboard.partials.settings');
        })->name('settings');

    });

require __DIR__ . '/auth.php';