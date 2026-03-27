<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Assessment;
use App\Models\AssessmentAccess;
use Illuminate\Http\Request;

class EnsureStudentHasAccess
{
    public function handle(Request $request, Closure $next)
    {
        $access_key = $request->route('access_key');
        $assessment = \App\Models\Assessment::where('access_key', $access_key)->first();

        if ($assessment) {
            // 👈 MAKE SURE THIS IS CHECKING 'lrn' AND NOT 'user_id'!
            $hasAccess = \App\Models\AssessmentAccess::where('assessment_id', $assessment->id)
                        ->where('lrn', auth()->user()->lrn)
                        ->exists();

            if ($hasAccess) {
                return $next($request);
            }
        }

        // This is the redirect that was bouncing you back!
        return redirect('/dashboard')->withErrors(['assessment_code' => 'You are not authorized for this exam.']);
    }
}