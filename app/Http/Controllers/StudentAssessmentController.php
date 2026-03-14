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

        // 1. Find the assessment
        $assessment = \App\Models\Assessment::where('access_key', $request->assessment_code)
                        ->where('status', 'published')
                        ->first();

        // Error if code doesn't exist
        if (!$assessment) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Invalid or inactive assessment code.'
            ], 404);
        }

        // 2. Check if the logged-in student has access (AssessmentAccess)
        // Assuming your User model has 'user_id' which stores the LRN
        $hasAccess = \App\Models\AssessmentAccess::where('assessment_id', $assessment->id)
                        ->where('lrn', auth()->user()->user_id) 
                        ->exists();

        if (!$hasAccess) {
            return response()->json([
                'status' => 'error', 
                'message' => 'You are not authorized to take this assessment. Please contact your teacher.'
            ], 403); // 403 Forbidden
        }

        // 3. Success - Redirection
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
        // The Middleware already verified the assessment exists and the student is authorized!
        $assessment = \App\Models\Assessment::where('access_key', $access_key)->first();

        // Just return the view (Make sure the path matches your actual file!)
        return view('dashboard.partials.student.assessmentExam.assessment-lobby', compact('assessment')); 
    }

    public function exam($access_key)
    {
        // 1. Fetch the assessment and ALL relationships
        $assessment = \App\Models\Assessment::with(['categories.questions.options'])
                        ->where('access_key', $access_key)
                        ->where('status', 'published')
                        ->firstOrFail();

        // 2. SECURITY CHECK: Hide the 'is_correct' field from students!
        $assessment->categories->each(function ($category) {
            $category->questions->each(function ($question) {
                $question->options->transform(function ($option) {
                    // Return everything EXCEPT is_correct
                    return [
                        'id' => $option->id,
                        'question_id' => $option->question_id,
                        'option_text' => $option->option_text,
                    ];
                });
            });
        });

        // 3. For this example, let's load the FIRST category to start the exam.
        // If you want them to take it section-by-section, we grab index 0.
        $currentCategory = $assessment->categories->first();

        return view('dashboard.partials.student.assessmentExam.assessment-exam', compact('assessment', 'currentCategory'));
    }
}