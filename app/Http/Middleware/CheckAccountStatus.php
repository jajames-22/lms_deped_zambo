<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAccountStatus
{
    // Routes that pending teachers are allowed to access
    protected $pendingAllowedPrefixes = [
        'dashboard.main',
        'dashboard.home',
        'dashboard.profile',
        'profile.',
        'dashboard.notifications',
        'student.dashboard',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $status = Auth::user()->status;
            $role   = Auth::user()->role;

            // ⛔ Kick out suspended users immediately
            if ($status === 'suspended') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->withErrors([
                    'login_id' => 'Your session was terminated because your account has been suspended.'
                ]);
            }

            // ⛔ Block pending teachers from accessing content/management routes
            if ($role === 'teacher' && $status === 'pending') {
                $routeName = $request->route()?->getName() ?? '';

                $isAllowed = false;
                foreach ($this->pendingAllowedPrefixes as $prefix) {
                    if ($routeName === $prefix || str_starts_with($routeName, $prefix)) {
                        $isAllowed = true;
                        break;
                    }
                }

                if (!$isAllowed) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Your account is pending verification. Please wait for admin approval.'
                        ], 403);
                    }

                    return redirect()->route('dashboard.home')
                        ->with('warning', 'Your account is pending admin verification. You cannot access this section yet.');
                }
            }
        }

        return $next($request);
    }
}