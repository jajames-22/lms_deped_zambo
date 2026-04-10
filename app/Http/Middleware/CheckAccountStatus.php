<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAccountStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $status = Auth::user()->status;

            // ⛔ ONLY kick them out instantly if they are suspended
            if ($status === 'suspended') {
                
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect to login with the error message
                return redirect('/login')->withErrors([
                    'login_id' => 'Your session was terminated because your account has been suspended.'
                ]);
            }
        }

        return $next($request);
    }
}