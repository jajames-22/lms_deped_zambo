<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::get('/dashboard', function () {
    // Looks for resources/views/dashboard/dashboard.blade.php
    return view('dashboard.dashboard'); 
})->middleware('auth')->name('dashboard');

// The Partials (The pages that load inside the dashboard)
Route::prefix('dashboard')->group(function () {
    Route::get('/home', function () {
        return view('dashboard.partials.home');
    });

    Route::get('/courses', function () {
        return view('dashboard.partials.courses');
    });

    Route::get('/assignments', function () {
        return view('dashboard.partials.assignments');
    });

    Route::get('/statistics', function () {
        return view('dashboard.partials.statistics');
    });

    Route::get('/settings', function () {
        return view('dashboard.partials.settings');
    });
});

require __DIR__.'/auth.php';