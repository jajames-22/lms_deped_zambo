<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin']) // function connects to the auth controller
        ->middleware('guest') //for logged out users only
        ->name('login'); //basis for the route

    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->middleware('guest')
        ->name('register');

    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth') //for logged in users only
    ->name('logout');