<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');

    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');

    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
        ->name('password.update');
});


/*
|--------------------------------------------------------------------------
| Verification Notice Page
|--------------------------------------------------------------------------
*/

//Route::get('/email/verify', function () {
//    return view('auth.verify-email');
//})->name('verification.notice');


/*
|--------------------------------------------------------------------------
| Verification Routes
|--------------------------------------------------------------------------
*/

Route::post('/email/resend', function (Request $request) {
    // Grab the email from the hidden input field
    $email = $request->input('email');

    if (!$email) {
        return redirect()->route('login');
    }

    $user = User::where('email', $email)->first();

    if (!$user || $user->hasVerifiedEmail()) {
        return redirect()->route('login');
    }

    // Safely resend just the verification notification
    $user->sendEmailVerificationNotification();

    // Return back and flash the session data again so the modal STAYS OPEN
    return back()->with([
        'message' => 'Verification link resent successfully.',
        'show_verification_modal' => true,
        'registered_email' => $email // Flashing it again so the text in the modal stays populated
    ]);

})->name('verification.send');


Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {

    $user = User::findOrFail($id);

    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403);
    }

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    return redirect()->route('login')
        ->with('status', 'Email verified successfully. You can now login.');

})->middleware('signed')->name('verification.verify');
/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});

Route::middleware('auth')->group(function () {
    Route::get('/register-email', [AuthController::class, 'showRegisterEmail'])->name('register-email');
    Route::post('/register-email', [AuthController::class, 'storeRegisterEmail']);
});