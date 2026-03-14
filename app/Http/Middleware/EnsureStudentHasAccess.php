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
        // 1. Get the access_key from the URL (e.g., /lobby/XKWPO0)
        $accessKey = $request->route('access_key');

        // 2. Find the assessment
        $assessment = Assessment::where('access_key', $accessKey)
                        ->where('status', 'published')
                        ->first();

        if (!$assessment) {
            return redirect()->route('student.dashboard')->with('error', 'Assessment not found.');
        }

        // 3. Check authorization (using user_id as the LRN)
        $hasAccess = AssessmentAccess::where('assessment_id', $assessment->id)
                        ->where('lrn', auth()->user()->user_id) 
                        ->exists();

        if (!$hasAccess) {
            return redirect()->route('student.dashboard')->with('error', 'Access Denied: You are not on the authorized list.');
        }

        // If they pass, allow them to proceed
        return $next($request);
    }
}