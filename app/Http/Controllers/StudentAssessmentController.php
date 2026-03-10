<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment; // Make sure your model matches the table you provided

class StudentAssessmentController extends Controller
{
    /**
     * Catches the code from the modal, checks if it exists, and redirects.
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'assessment_code' => 'required|string'
        ]);

        // Look for the assessment
        $assessment = \App\Models\Assessment::where('access_key', $request->assessment_code)
                                ->where('status', 'published') // Ensure you are checking the right status!
                                ->first();

        // If not found or not active, return a JSON error
        if (!$assessment) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Invalid or inactive assessment code.'
            ], 404);
        }

        // If found, return the URL of the lobby so JavaScript can redirect to it
        return response()->json([
            'status' => 'success', 
            'redirect_url' => route('student.assessment.lobby', ['access_key' => $assessment->access_key])
        ]);
    }

    /**
     * Displays the full standalone view with details and the "Start" button.
     */
    public function lobby($access_key)
    {
        $assessment = Assessment::where('access_key', $access_key)->firstOrFail();
        
        return view('dashboard.partials.student.assessment-lobby', compact('assessment'));
    }
}