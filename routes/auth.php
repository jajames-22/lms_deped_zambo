<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;

Route::middleware('guest')->group(function () {

    // LOGIN
    Route::get('/login', [AuthController::class, 'showLogin']) // function connects to the auth controller
        ->middleware('guest') //for logged out users only
        ->name('login'); //basis for the route

    Route::post('/login', [AuthController::class, 'login']);

    //REGSITER
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->middleware('guest')
        ->name('register');

    Route::post('/register', [AuthController::class, 'register']);




    // PASSWORD RESET
    // Show forgot password form
    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])
        ->name('password.request');

    // Send reset link email
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])
        ->name('password.email');

    // Show reset password form
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('password.reset');

    // Handle password reset
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth') //for logged in users only
    ->name('logout');