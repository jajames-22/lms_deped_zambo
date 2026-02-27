<?php

use Illuminate\Support\Facades\Route;

// Added Middleware to ensure users logged in can access
Route::get('/', function () {
    return view('index');
})->middleware('guest')->name('index');

Route::get('/dashboard', function () {
    return view('dashboard.dashboard');
})->middleware('auth')->name('dashboard');

// The Partials (The pages that load inside the dashboard)
Route::prefix('dashboard')->group(function () {
    Route::get('/home', function () {
        return view('dashboard.partials.home');
    })->middleware('auth')->name('home');

    Route::get('/courses', function () {
        return view('dashboard.partials.courses');
    })->middleware('auth')->name('dashboard');

    Route::get('/assignments', function () {
        return view('dashboard.partials.assignments');
    })->middleware('auth')->name('assignments');

    Route::get('/statistics', function () {
        return view('dashboard.partials.statistics');
    })->middleware('auth')->name('statistics');

    Route::get('/settings', function () {
        return view('dashboard.partials.settings');
    })->middleware('auth')->name('settings');
});

require __DIR__ . '/auth.php';