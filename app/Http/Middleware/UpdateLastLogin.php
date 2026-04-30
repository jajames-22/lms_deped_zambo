<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UpdateLastLogin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $today = now()->format('Y-m-d');
            $lastLogin = $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('Y-m-d') : null;

            // Only run this logic ONCE per day per user
            if ($lastLogin !== $today) {
                $yesterday = now()->subDay()->format('Y-m-d');

                // If they logged in yesterday, increase streak. Otherwise, reset to 1.
                if ($lastLogin === $yesterday) {
                    $user->current_streak += 1;
                } else {
                    $user->current_streak = 1;
                }

                $user->last_login_at = now();
                $user->timestamps = false; // Prevent modifying updated_at
                $user->save();
            }
        }

        return $next($request);
    }
}