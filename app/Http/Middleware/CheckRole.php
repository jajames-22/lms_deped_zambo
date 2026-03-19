<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * The ...$roles allows us to pass multiple roles like 'admin,superadmin,teacher'
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Check if user is logged in
        // 2. Check if their role is inside the allowed list
        if (!auth()->check() || !in_array(auth()->user()->role, $roles)) {
            abort(403, 'Unauthorized access. You do not have permission to view this.');
        }

        return $next($request);
    }
}