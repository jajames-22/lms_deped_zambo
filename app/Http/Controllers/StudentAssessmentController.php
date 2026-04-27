<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment; 
use App\Models\StudentAnswer;
use App\Models\AssessmentSession;
use Illuminate\Support\Facades\Auth;

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
        // Fetch the record instead of using exists() so we can update it
        $access = \App\Models\AssessmentAccess::where('assessment_id', $assessment->id)
                        ->where('lrn', auth()->user()->lrn) 
                        ->first();

        if (!$access) {
            return response()->json([
                'status' => 'error', 
                'message' => 'You are not authorized to take this assessment. Please contact your teacher.'
            ], 403); // 403 Forbidden
        }

        // --- NEW: UPDATE STATUS TO LOBBY IMMEDIATELY ON VERIFICATION ---
        if ($access->status === 'offline') {
            $access->update(['status' => 'lobby']);
        }
        // ---------------------------------------------------------------

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
        $assessment = \App\Models\Assessment::withCount('categories')
            ->where('access_key', $access_key)
            ->where('status', 'published')
            ->firstOrFail();

        $user = \Illuminate\Support\Facades\Auth::user();

        $completedCategoriesCount = \App\Models\AssessmentSession::where('user_id', $user->id)
            ->where('assessment_id', $assessment->id)
            ->where('is_completed', true)
            ->count();

        if ($assessment->categories_count > 0 && $completedCategoriesCount >= $assessment->categories_count) {
            return redirect()->route('student.assessment.results', $access_key);
        }

        // Fallback update just in case they navigated here directly
        $access = \App\Models\AssessmentAccess::where('assessment_id', $assessment->id)
            ->where('lrn', $user->lrn)
            ->first();

        if ($access && $access->status !== 'finished' && $access->status !== 'taking_exam') {
            $access->update(['status' => 'lobby']);
        }

        return view('dashboard.partials.student.assessmentExam.assessment-lobby', compact('assessment'));
    }

    public function exam(Request $request, $access_key)
    {
        // Add ordering based on the sort_order inside eager loading
        $assessment = \App\Models\Assessment::with([
            'categories' => function ($query) {
                $query->orderBy('sort_order', 'asc');
            },
            'categories.questions' => function ($query) {
                $query->orderBy('sort_order', 'asc');
            },
            'categories.questions.options'
        ])
        ->where('access_key', $access_key)
        ->where('status', 'published')
        ->firstOrFail();

        $user = \Illuminate\Support\Facades\Auth::user();

        // Hide is_correct for security
        $assessment->categories->each(function ($category) {
            $category->questions->each(function ($question) {
                $question->options->transform(function ($option) {
                    return ['id' => $option->id, 'question_id' => $option->question_id, 'option_text' => $option->option_text];
                });
            });
        });

        // Get an array of Category IDs the student has already SUBMITTED
        $completedCategoryIds = \App\Models\AssessmentSession::where('user_id', $user->id)
            ->where('assessment_id', $assessment->id)
            ->where('is_completed', true)
            ->pluck('category_id')
            ->toArray();

        $categoryId = $request->query('category_id');

        if ($categoryId) {
            // Prevent them from going back to a finished section via URL tampering
            if (in_array($categoryId, $completedCategoryIds)) {
                return redirect()->route('student.assessment.exam', $access_key); 
            }
            $currentCategory = $assessment->categories->where('id', $categoryId)->first();
        } else {
            // Find the FIRST category they haven't completed yet (The Smart Resume)
            $currentCategory = $assessment->categories->whereNotIn('id', $completedCategoryIds)->first();
        }

        // If they finished every section, boot them to the dashboard
        if (!$currentCategory) {
            return redirect()->route('student.assessment.results', $access_key);
        }

        // Load their saved timer and answers for the active section
        $session = \App\Models\AssessmentSession::where('user_id', $user->id)
            ->where('assessment_id', $assessment->id)
            ->where('category_id', $currentCategory->id)
            ->first();

       $existingAnswers = \App\Models\StudentAnswer::where('user_id', $user->id)
            ->where('assessment_id', $assessment->id)
            ->get()
            ->keyBy('question_id');

        // UPDATE STATUS TO TAKING EXAM
        $access = \App\Models\AssessmentAccess::where('assessment_id', $assessment->id)
            ->where('lrn', $user->lrn)
            ->first();

        if ($access && $access->status !== 'finished') {
            $access->update(['status' => 'taking_exam']);
        }

        return view('dashboard.partials.student.assessmentExam.assessment-exam', 
            compact('assessment', 'currentCategory', 'session', 'existingAnswers', 'access')
        );
    }

    public function autoSave(Request $request, $access_key)
    {
        try {
            $assessment = \App\Models\Assessment::where('access_key', $access_key)->firstOrFail();
            $user = \Illuminate\Support\Facades\Auth::user();

            // 1. Save the timer
            \App\Models\AssessmentSession::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'assessment_id' => $assessment->id,
                    'category_id' => $request->input('category_id'),
                ],
                ['time_remaining' => (int) $request->input('time_remaining')]
            );

            // 2. Save the answers (Check if answers exist first)
            if ($request->has('answers') && is_array($request->input('answers'))) {
                foreach ($request->input('answers') as $questionId => $answerData) {
                    
                    $selectedOptions = is_array($answerData) ? json_encode($answerData) : json_encode([$answerData]);
                    $answerText = is_string($answerData) ? $answerData : null;

                    \App\Models\StudentAnswer::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'assessment_id' => $assessment->id,
                            'question_id' => $questionId,
                        ],
                        [
                            'selected_options' => $selectedOptions,
                            'answer_text' => $answerText,
                        ]
                    );
                }
            }

            // 3. Save the pauses left to the database
            if ($request->has('pauses_left')) {
                $access = \App\Models\AssessmentAccess::where('assessment_id', $assessment->id)
                    ->where('lrn', $user->lrn)
                    ->first();

                if ($access) {
                    $access->update(['pauses_left' => (int) $request->input('pauses_left')]);
                }
            }

            return response()->json(['status' => 'success']);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Autosave Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function submit(Request $request, $access_key)
    {
        $assessment = \App\Models\Assessment::where('access_key', $access_key)->firstOrFail();
        $user = \Illuminate\Support\Facades\Auth::user();
        $currentCategoryId = $request->input('category_id');

        // 1. Mark THIS section as permanently completed
        \App\Models\AssessmentSession::updateOrCreate(
            [
                'user_id' => $user->id,
                'assessment_id' => $assessment->id,
                'category_id' => $currentCategoryId,
            ],
            [
                'is_completed' => true,
                'time_remaining' => 0 
            ]
        );

        // 2. Final save of their answers (Catching anything autosave missed in the last second)
        if ($request->has('answers') && is_array($request->input('answers'))) {
            foreach ($request->input('answers') as $questionId => $answerData) {
                $selectedOptions = is_array($answerData) ? json_encode($answerData) : json_encode([$answerData]);
                $answerText = is_string($answerData) ? $answerData : null;

                \App\Models\StudentAnswer::updateOrCreate(
                    ['user_id' => $user->id, 'assessment_id' => $assessment->id, 'question_id' => $questionId],
                    ['selected_options' => $selectedOptions, 'answer_text' => $answerText]
                );
            }
        }

        // 3. Check what sections are finished and find the next one
        $completedCategoryIds = \App\Models\AssessmentSession::where('user_id', $user->id)
            ->where('assessment_id', $assessment->id)
            ->where('is_completed', true)
            ->pluck('category_id')
            ->toArray();

        // Change 'id' to 'sort_order' to fetch the true next category in line
        $nextCategory = $assessment->categories()
            ->whereNotIn('id', $completedCategoryIds)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($nextCategory) {
            return redirect()->route('student.assessment.exam', ['access_key' => $access_key, 'category_id' => $nextCategory->id]);
        } else {
            
            // UPDATE STATUS TO FINISHED
            $access = \App\Models\AssessmentAccess::where('assessment_id', $assessment->id)
                ->where('lrn', $user->lrn)
                ->first();

            if ($access) {
                $access->update(['status' => 'finished']);
            }

            return redirect()->route('student.assessment.results', $access_key)
                             ->with('success', 'Assessment fully completed!');
        }
    }

    public function results($access_key)
    {
        // 1. Fetch the assessment and all questions/options, sorted correctly
        $assessment = \App\Models\Assessment::with([
            'categories' => function ($query) {
                $query->orderBy('sort_order', 'asc');
            },
            'categories.questions' => function ($query) {
                $query->orderBy('sort_order', 'asc');
            },
            'categories.questions.options'
        ])
        ->where('access_key', $access_key)
        ->firstOrFail();

        $user = \Illuminate\Support\Facades\Auth::user();

        // 2. Fetch the student's saved answers
        $studentAnswers = \App\Models\StudentAnswer::where('user_id', $user->id)
            ->where('assessment_id', $assessment->id)
            ->get()
            ->keyBy('question_id'); 

        $score = 0;
        $totalQuestions = 0;
        $detailedResultsByCategory = []; 

        // 3. Loop through every question to grade it
        foreach ($assessment->categories as $category) {
            $categoryData = [
                'title' => $category->title,
                'items' => []
            ];

            foreach ($category->questions as $question) {
                
                // Handle Instructions (Skip Grading entirely)
                if ($question->type === 'instruction') {
                    $categoryData['items'][] = (object) [
                        'is_instruction' => true,
                        'question' => $question,
                    ];
                    continue; 
                }

                $totalQuestions++;
                $studentAnswer = $studentAnswers->get($question->id);

                $isCorrect = false;
                $isPending = false;
                $correctAnswerText = '';
                
                $studentAnswerText = $studentAnswer ? trim($studentAnswer->answer_text) : '';
                $displayStudentAnswer = $studentAnswerText !== '' ? $studentAnswerText : 'No answer provided';

                // Handle Short Answer / Text Questions
                if ($question->type === 'text') {
                    
                    $correctOptions = $question->options->where('is_correct', true)->filter(function ($option) {
                        return trim($option->option_text) !== '';
                    });

                    if ($correctOptions->isNotEmpty()) {
                        $isPending = false;
                        $correctAnswerText = $correctOptions->pluck('option_text')->implode(' OR ');
                        $isCaseSensitive = $question->is_case_sensitive ?? false;

                        if ($studentAnswerText !== '') {
                            foreach ($correctOptions as $option) {
                                $expectedAnswer = trim($option->option_text);
                                
                                if ($isCaseSensitive) {
                                    if ($studentAnswerText === $expectedAnswer) { $isCorrect = true; break; }
                                } else {
                                    if (strtolower($studentAnswerText) === strtolower($expectedAnswer)) { $isCorrect = true; break; }
                                }
                            }
                            if ($isCorrect) $score++;
                        }
                        $studentAnswerText = $displayStudentAnswer;

                    } else {
                        // True Essay (Pending Manual Grading)
                        $isPending = true; 
                        $studentAnswerText = $displayStudentAnswer;
                        $correctAnswerText = 'Pending manual grading by instructor.';
                    }

                } 
                // Handle Multiple Choice & Checkboxes (Auto-graded)
                else {
                    $correctOptions = $question->options->where('is_correct', true);
                    $correctOptionIds = $correctOptions->pluck('id')->toArray();
                    $correctAnswerText = $correctOptions->pluck('option_text')->implode(', ');

                    if ($studentAnswer && $studentAnswer->selected_options) {
                        $selectedIds = json_decode($studentAnswer->selected_options, true) ?? [];
                        $selectedOptionsText = $question->options->whereIn('id', $selectedIds)->pluck('option_text')->implode(', ');
                        $studentAnswerText = $selectedOptionsText ?: 'Unknown selection';

                        if (count($selectedIds) === count($correctOptionIds) && empty(array_diff($selectedIds, $correctOptionIds))) {
                            $isCorrect = true;
                            $score++;
                        }
                    } else {
                        $studentAnswerText = 'No answer provided';
                    }
                }

                // 4. Bundle the data into the category
                $categoryData['items'][] = (object) [
                    'is_instruction' => false,
                    'question' => $question,
                    'is_correct' => $isCorrect,
                    'is_pending' => $isPending,
                    'student_answer_text' => $studentAnswerText,
                    'correct_answer_text' => $correctAnswerText,
                ];
            }

            $detailedResultsByCategory[] = (object) $categoryData;
        }

        return view('dashboard.partials.student.assessmentExam.assessment-result', compact(
            'assessment', 'score', 'totalQuestions', 'detailedResultsByCategory'
        ));
    }
}