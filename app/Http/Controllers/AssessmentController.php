<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\ExamImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Quiz;
use App\Models\Assessment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;
use App\Exports\AssessmentTemplateExport;
use Illuminate\Support\Facades\Log;
use App\Models\AssessmentAccess;
use App\Imports\LrnAccessImport;

class AssessmentController extends Controller
{

    public function importAccess(Request $request, Assessment $assessment)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            Excel::import(new LrnAccessImport($assessment->id), $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'LRN list imported successfully!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed. Check if your file has an "lrn" header.'
            ], 500);
        }
    }

    public function index()
    {
        $assessments = DB::table('assessments')
            ->select('assessments.*')
            ->addSelect([
                'categories_count' => DB::table('assessment_categories')
                    ->whereColumn('assessment_id', 'assessments.id')
                    ->selectRaw('count(*)')
            ])
            ->orderBy('updated_at', 'desc') // newest updated first
            ->get();
        return view('dashboard.partials.admin.assessments', compact('assessments'));
    }

    public function create()
    {
        $assessmentId = DB::table('assessments')->insertGetId([
            'title' => 'Untitled Assessment',
            'year_level' => '',
            'description' => '',
            'access_key' => strtoupper(Str::random(6)),
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $assessment = DB::table('assessments')->where('id', $assessmentId)->first();
        $isNew = true;
        return view('dashboard.partials.admin.assessments-create', compact('assessment', 'isNew'));
    }

    public function builder($id)
    {
        $assessment = DB::table('assessments')->where('id', $id)->first();

        if (!$assessment) {
            abort(404, 'Assessment not found');
        }

        $categories = DB::table('assessment_categories')
            ->where('assessment_id', $id)
            ->orderBy('sort_order', 'asc') // Added Sort Order
            ->get()
            ->map(function ($category) {
                $category->questions = DB::table('assessment_questions')
                    ->where('category_id', $category->id)
                    ->orderBy('sort_order', 'asc') // Added Sort Order
                    ->get()
                    ->map(function ($question) {
                        $question->options = DB::table('assessment_options')
                            ->where('question_id', $question->id)
                            ->get();
                        return $question;
                    });
                return $category;
            });

        $isNew = false;
        return view('dashboard.partials.admin.assessments-create', compact('assessment', 'categories', 'isNew'));
    }

    public function storeQuestions(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            DB::table('assessments')->where('id', $id)->update([
                'title' => $request->title ?? 'Untitled Assessment',
                'year_level' => $request->year_level ?? '',
                'description' => $request->description ?? '',
                'status' => $request->status ?? 'draft',
                'draft_json' => null,
                'updated_at' => now()
            ]);

            // Tracking arrays to know what NOT to delete
            $keptCategoryIds = [];
            $keptQuestionIds = [];
            $keptOptionIds = [];

            $categories = $request->categories ?? [];

            // 1. PROCESS AND UPSERT
            foreach ($categories as $index => $cat) {
                $categoryId = isset($cat['id']) && is_numeric($cat['id']) ? $cat['id'] : null;

                $catData = [
                    'assessment_id' => $id,
                    'title' => $cat['title'] ?? 'New Section',
                    'time_limit' => $cat['time_limit'] ?? 0,
                    'sort_order' => $index + 1, // Start sorting at 1
                    'updated_at' => now(),
                ];

                if ($categoryId && DB::table('assessment_categories')->where('id', $categoryId)->exists()) {
                    DB::table('assessment_categories')->where('id', $categoryId)->update($catData);
                } else {
                    $catData['created_at'] = now();
                    $categoryId = DB::table('assessment_categories')->insertGetId($catData);
                }
                $keptCategoryIds[] = $categoryId;

                foreach ($cat['questions'] ?? [] as $qIndex => $q) {
                    $questionId = isset($q['id']) && is_numeric($q['id']) ? $q['id'] : null;

                    $qData = [
                        'category_id' => $categoryId,
                        'type' => $q['type'] ?? 'mcq',
                        'question_text' => $q['text'] ?? '',
                        'media_url' => $q['media_url'] ?? null,
                        'is_case_sensitive' => $q['is_case_sensitive'] ?? false,
                        'sort_order' => $qIndex + 1, // Start sorting at 1
                        'updated_at' => now(),
                    ];

                    if ($questionId && DB::table('assessment_questions')->where('id', $questionId)->exists()) {
                        DB::table('assessment_questions')->where('id', $questionId)->update($qData);
                    } else {
                        $qData['created_at'] = now();
                        $questionId = DB::table('assessment_questions')->insertGetId($qData);
                    }
                    $keptQuestionIds[] = $questionId;

                    $options = $q['options'] ?? [];
                    foreach ($options as $opt) {
                        $optId = isset($opt['id']) && is_numeric($opt['id']) ? $opt['id'] : null;

                        $optData = [
                            'question_id' => $questionId,
                            'option_text' => $opt['text'] ?? '',
                            'is_correct' => $opt['is_correct'] ?? false,
                            'updated_at' => now(),
                        ];

                        if ($optId && DB::table('assessment_options')->where('id', $optId)->exists()) {
                            DB::table('assessment_options')->where('id', $optId)->update($optData);
                        } else {
                            $optData['created_at'] = now();
                            $optId = DB::table('assessment_options')->insertGetId($optData);
                        }
                        $keptOptionIds[] = $optId;
                    }
                }
            }

            // 2. CLEANUP (Delete items removed from the builder)
            $allCategoryIdsForAssessment = DB::table('assessment_categories')->where('assessment_id', $id)->pluck('id');

            if ($allCategoryIdsForAssessment->isNotEmpty()) {
                // Delete removed options
                DB::table('assessment_options')
                    ->whereIn('question_id', function ($query) use ($allCategoryIdsForAssessment) {
                        $query->select('id')->from('assessment_questions')
                            ->whereIn('category_id', $allCategoryIdsForAssessment);
                    })
                    ->whereNotIn('id', $keptOptionIds)
                    ->delete();

                // Delete removed questions
                DB::table('assessment_questions')
                    ->whereIn('category_id', $allCategoryIdsForAssessment)
                    ->whereNotIn('id', $keptQuestionIds)
                    ->delete();
            }

            // Delete removed categories
            DB::table('assessment_categories')
                ->where('assessment_id', $id)
                ->whereNotIn('id', $keptCategoryIds)
                ->delete();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function uploadMedia(Request $request)
    {
        // Ensure the file is present in the request
        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');

            // 1. Capture the original name before the system renames it
            $originalName = $file->getClientOriginalName();

            // 2. Store the file in your preferred disk (e.g., 'public')
            // This generates a unique filename to prevent overwriting existing files
            $path = $file->store('assessment_media', 'public');

            // 3. Generate the full accessible URL
            $url = asset('storage/' . $path);

            // 4. Return the specific JSON keys your assessment.js expects
            return response()->json([
                'success' => true,
                'media_url' => $url,
                'media_type' => $file->getClientOriginalExtension(),
                'original_name' => $originalName, // This ensures the frontend keeps the real name
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No file was uploaded.'
        ], 400);
    }


    public function destroy($id)
    {
        try {
            // Manual cascade delete for all child records
            $categoryIds = DB::table('assessment_categories')->where('assessment_id', $id)->pluck('id');

            if ($categoryIds->isNotEmpty()) {
                $questionIds = DB::table('assessment_questions')->whereIn('category_id', $categoryIds)->pluck('id');

                if ($questionIds->isNotEmpty()) {
                    DB::table('assessment_options')->whereIn('question_id', $questionIds)->delete();
                }

                DB::table('assessment_questions')->whereIn('category_id', $categoryIds)->delete();
                DB::table('assessment_categories')->where('assessment_id', $id)->delete();
            }

            // Finally, delete the assessment
            DB::table('assessments')->where('id', $id)->delete();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function toggleResults(Request $request, Assessment $assessment)
    {
        // Flip the boolean
        $assessment->show_results = !$assessment->show_results;
        $assessment->save();

        return response()->json([
            'success' => true,
            'message' => $assessment->show_results ? 'Students will now see their results after the exam.' : 'Results are now hidden from students.',
            'new_status' => $assessment->show_results
        ]);
    }

    public function autosave(Request $request, $id)
    {
        try {
            // 1. If the frontend specifically asks to discard, wipe the draft completely.
            if ($request->has('clear_draft') && $request->clear_draft) {
                DB::table('assessments')->where('id', $id)->update([
                    'draft_json' => null
                ]);
                return response()->json(['success' => true]);
            }

            // 2. Otherwise, tuck ALL the unsaved typing safely inside draft_json.
            $draftData = [
                'title' => $request->title,
                'year_level' => $request->year_level,
                'description' => $request->description,
                'categories' => $request->categories ?? []
            ];

            DB::table('assessments')
                ->where('id', $id)
                ->update([
                    'draft_json' => json_encode($draftData),
                    'updated_at' => now()
                ]);

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            Log::error('Autosave Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function downloadTemplate()
    {
        return Excel::download(new AssessmentTemplateExport, 'assessment_template.xlsx');
    }

    public function importQuestions(Request $request, $id)
    {
        $request->validate([
            'exam_file' => 'required|mimes:xlsx,csv,xls|max:5120', // 5MB limit
        ]);

        DB::beginTransaction();
        try {
            // 1. Update basic test info and clear the temporary draft
            DB::table('assessments')->where('id', $id)->update([
                'title' => $request->title ?? 'Untitled Assessment',
                'year_level' => $request->year_level ?? '',
                'description' => $request->description ?? '',
                'status' => 'draft',
                'draft_json' => null,
                'updated_at' => now()
            ]);

            // 2. Process and save any manually created categories/questions from the UI
            if ($request->has('categories')) {
                $categories = json_decode($request->categories, true);

                // Delete existing database rows to replace with the current UI state
                $existingCategories = DB::table('assessment_categories')->where('assessment_id', $id)->pluck('id');
                if ($existingCategories->isNotEmpty()) {
                    $existingQuestions = DB::table('assessment_questions')->whereIn('category_id', $existingCategories)->pluck('id');
                    if ($existingQuestions->isNotEmpty()) {
                        DB::table('assessment_options')->whereIn('question_id', $existingQuestions)->delete();
                    }
                    DB::table('assessment_questions')->whereIn('category_id', $existingCategories)->delete();
                    DB::table('assessment_categories')->where('assessment_id', $id)->delete();
                }

                // Insert the payload from the frontend
                if (is_array($categories)) {
                    foreach ($categories as $index => $cat) {
                        $categoryId = DB::table('assessment_categories')->insertGetId([
                            'assessment_id' => $id,
                            'title' => $cat['title'] ?? 'New Section',
                            'time_limit' => (int) ($cat['time_limit'] ?? 0),
                            'sort_order' => $index + 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        foreach ($cat['questions'] ?? [] as $qIndex => $q) {
                            $questionId = DB::table('assessment_questions')->insertGetId([
                                'category_id' => $categoryId,
                                'type' => $q['type'] ?? 'mcq',
                                'question_text' => $q['text'] ?? '',
                                'media_url' => $q['media_url'] ?? null,
                                'is_case_sensitive' => $q['is_case_sensitive'] ?? false,
                                'sort_order' => $qIndex + 1,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            foreach ($q['options'] ?? [] as $opt) {
                                DB::table('assessment_options')->insert([
                                    'question_id' => $questionId,
                                    'option_text' => $opt['text'] ?? '',
                                    'is_correct' => $opt['is_correct'] ?? false,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            }

            // 3. Run the import using the correct Assessment ID (this appends to the DB)
            Excel::import(new ExamImport($id), $request->file('exam_file'));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Questions imported successfully!'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }


    public function manage(Assessment $assessment)
    {
        $assessment->loadCount(['categories', 'questions']);

        // Fetch the access list, and attempt to load the registered student data if it exists
        $whitelistedStudents = AssessmentAccess::with('student')
            ->where('assessment_id', $assessment->id)
            ->latest()
            ->get();

        return view('dashboard.partials.admin.assessments-manage', compact('assessment', 'whitelistedStudents'));
    }

    public function addAccess(Request $request, \App\Models\Assessment $assessment)
    {
        $request->validate([
            'lrn' => 'required|string|max:50'
        ]);

        $cleanLrn = trim($request->lrn);

        if (AssessmentAccess::where('assessment_id', $assessment->id)->where('lrn', $cleanLrn)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This LRN is already on the access list.'
            ]);
        }

        AssessmentAccess::create([
            'assessment_id' => $assessment->id,
            'lrn' => $cleanLrn,
            'status' => 'offline' // Default status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student LRN added successfully!'
        ]);
    }

    public function removeAccess(AssessmentAccess $access)
    {
        $access->delete();
        return response()->json(['success' => true, 'message' => 'Student access revoked.']);
    }

    public function toggleStatus(Request $request, Assessment $assessment)
    {
        try {
            $newStatus = $assessment->status === 'published' ? 'draft' : 'published';

            DB::table('assessments')
                ->where('id', $assessment->id)
                ->update(['status' => $newStatus, 'updated_at' => now()]);

            return response()->json([
                'success' => true,
                'new_status' => $newStatus,
                'message' => 'Assessment is now ' . ($newStatus === 'published' ? 'Live' : 'in Draft Mode')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function analytics(\App\Models\Assessment $assessment)
    {
        try {
            // 1. Participation Stats
            $totalStudents = \Illuminate\Support\Facades\DB::table('assessment_accesses')
                ->where('assessment_id', $assessment->id)
                ->count();

            $completedCount = \Illuminate\Support\Facades\DB::table('assessment_accesses')
                ->where('assessment_id', $assessment->id)
                ->where('status', 'finished')
                ->count();

            $completionRate = $totalStudents > 0 ? round(($completedCount / $totalStudents) * 100) : 0;

            // Get Students Who Did Not Take the Assessment (Strictly Offline)
            $notTakenStudents = \Illuminate\Support\Facades\DB::table('assessment_accesses')
                ->where('assessment_id', $assessment->id)
                ->where('status', 'offline')
                ->select('lrn')
                ->get();

            // 2. Total Questions
            $totalQuestions = \Illuminate\Support\Facades\DB::table('assessment_questions')
                ->join('assessment_categories', 'assessment_questions.category_id', '=', 'assessment_categories.id')
                ->where('assessment_categories.assessment_id', $assessment->id)
                ->count();

            // 3. Individual Student Scores
            $studentScoresRaw = \Illuminate\Support\Facades\DB::table('student_answers')
                ->join('assessment_questions', 'student_answers.question_id', '=', 'assessment_questions.id')
                ->join('assessment_categories', 'assessment_questions.category_id', '=', 'assessment_categories.id')
                ->join('assessment_options', 'student_answers.option_id', '=', 'assessment_options.id')
                ->where('assessment_categories.assessment_id', $assessment->id)
                ->select(
                    'student_answers.user_id',
                    \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN assessment_options.is_correct = 1 THEN 1 ELSE 0 END) as correct_count')
                )
                ->groupBy('student_answers.user_id')
                ->get();

            $highestScoreRaw = 0;
            $lowestScoreRaw = $totalQuestions > 0 ? $totalQuestions : 0; 
            $passedCount = 0;
            $failedCount = 0;
            $totalCorrectAnswers = 0;
            
            $scoreDistribution = [
                '90-100%' => 0, '80-89%' => 0, '70-79%' => 0, '60-69%' => 0, 'Below 60%' => 0,
            ];

            foreach ($studentScoresRaw as $scoreData) {
                $rawScore = $scoreData->correct_count;
                $pct = $totalQuestions > 0 ? round(($rawScore / $totalQuestions) * 100) : 0;
                $totalCorrectAnswers += $rawScore;

                if ($rawScore > $highestScoreRaw) $highestScoreRaw = $rawScore;
                if ($rawScore < $lowestScoreRaw) $lowestScoreRaw = $rawScore;

                if ($pct >= 75) $passedCount++; else $failedCount++;

                if ($pct >= 90) $scoreDistribution['90-100%']++;
                elseif ($pct >= 80) $scoreDistribution['80-89%']++;
                elseif ($pct >= 70) $scoreDistribution['70-79%']++;
                elseif ($pct >= 60) $scoreDistribution['60-69%']++;
                else $scoreDistribution['Below 60%']++;
            }

            if ($studentScoresRaw->isEmpty()) $lowestScoreRaw = 0; 

            $highestScorePct = $totalQuestions > 0 ? round(($highestScoreRaw / $totalQuestions) * 100) : 0;
            $lowestScorePct = $totalQuestions > 0 ? round(($lowestScoreRaw / $totalQuestions) * 100) : 0;
            $averageScoreRaw = $completedCount > 0 ? round($totalCorrectAnswers / $completedCount, 1) : 0;
            $averageScorePct = ($completedCount > 0 && $totalQuestions > 0) ? round(($totalCorrectAnswers / ($completedCount * $totalQuestions)) * 100) : 0;

            // Calculate Overall Average Time
            $overallAvgTimeRaw = \Illuminate\Support\Facades\DB::table('assessment_sessions')
                ->where('assessment_id', $assessment->id)
                ->where('is_completed', 1)
                ->avg(\Illuminate\Support\Facades\DB::raw('TIMESTAMPDIFF(SECOND, created_at, updated_at)'));
            
            $overallAvgTime = $overallAvgTimeRaw ? sprintf('%02d:%02d', floor($overallAvgTimeRaw / 60), round($overallAvgTimeRaw % 60)) . ' mins' : 'N/A';

            // 4. Section/Category Performance & Time
            $categoryPerformance = \Illuminate\Support\Facades\DB::table('assessment_categories')
                ->leftJoin('assessment_questions', 'assessment_categories.id', '=', 'assessment_questions.category_id')
                ->leftJoin('student_answers', 'assessment_questions.id', '=', 'student_answers.question_id')
                ->leftJoin('assessment_options', 'student_answers.option_id', '=', 'assessment_options.id')
                ->where('assessment_categories.assessment_id', $assessment->id)
                ->select(
                    'assessment_categories.id',
                    'assessment_categories.title',
                    \Illuminate\Support\Facades\DB::raw('COUNT(student_answers.id) as total_answers'),
                    \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN assessment_options.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers')
                )
                ->groupBy('assessment_categories.id', 'assessment_categories.title', 'assessment_categories.sort_order')
                ->orderBy('assessment_categories.sort_order')
                ->get();

            $categoryTimesRaw = \Illuminate\Support\Facades\DB::table('assessment_sessions')
                ->where('assessment_id', $assessment->id)
                ->where('is_completed', 1)
                ->select('category_id', \Illuminate\Support\Facades\DB::raw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_time_seconds'))
                ->groupBy('category_id')
                ->pluck('avg_time_seconds', 'category_id');

            $categoryLabels = [];
            $categoryScores = [];
            $categoryData = [];

            foreach ($categoryPerformance as $cat) {
                $scorePct = $cat->total_answers > 0 ? round(($cat->correct_answers / $cat->total_answers) * 100) : 0;
                $categoryLabels[] = \Illuminate\Support\Str::limit($cat->title, 20);
                $categoryScores[] = $scorePct;

                $avgTimeSec = $categoryTimesRaw[$cat->id] ?? 0;
                $categoryData[] = [
                    'title' => $cat->title,
                    'score_pct' => $scorePct,
                    'avg_time' => ($avgTimeSec > 0) ? sprintf('%02d:%02d', floor($avgTimeSec / 60), round($avgTimeSec % 60)) . ' mins' : 'N/A'
                ];
            }

            // 5. Item Analysis
            $itemAnalysis = \Illuminate\Support\Facades\DB::table('assessment_questions')
                ->join('assessment_categories', 'assessment_questions.category_id', '=', 'assessment_categories.id')
                ->leftJoin('student_answers', 'assessment_questions.id', '=', 'student_answers.question_id')
                ->leftJoin('assessment_options', 'student_answers.option_id', '=', 'assessment_options.id')
                ->where('assessment_categories.assessment_id', $assessment->id)
                ->select(
                    'assessment_questions.id',
                    'assessment_questions.question_text',
                    'assessment_categories.title as category_name',
                    \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN assessment_options.is_correct = 1 THEN 1 ELSE 0 END) as correct_count'),
                    \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN assessment_options.is_correct = 0 THEN 1 ELSE 0 END) as wrong_count')
                )
                ->groupBy('assessment_questions.id', 'assessment_questions.question_text', 'assessment_categories.title', 'assessment_categories.sort_order', 'assessment_questions.sort_order')
                ->orderBy('assessment_categories.sort_order')
                ->orderBy('assessment_questions.sort_order')
                ->get()
                ->map(function ($item) {
                    $total = $item->correct_count + $item->wrong_count;
                    $item->accuracy = $total > 0 ? round(($item->correct_count / $total) * 100) : 0;
                    $item->correct_count = (int) $item->correct_count;
                    $item->wrong_count = (int) $item->wrong_count;
                    return $item;
                });

            // 6. Actionable Insights
            $answeredItems = $itemAnalysis->filter(fn($item) => ($item->correct_count + $item->wrong_count) > 0);
            $mostMissed = $answeredItems->sortBy('accuracy')->take(3);
            $perfectQuestions = $answeredItems->filter(fn($item) => $item->accuracy == 100);

            return view('dashboard.partials.admin.assessments-analytics', compact(
                'assessment', 'totalQuestions', 'totalStudents', 'completedCount', 'completionRate', 'notTakenStudents',
                'averageScoreRaw', 'averageScorePct', 'overallAvgTime', 'highestScoreRaw', 'highestScorePct',
                'lowestScoreRaw', 'lowestScorePct', 'passedCount', 'failedCount', 'scoreDistribution',
                'categoryLabels', 'categoryScores', 'categoryData', 'itemAnalysis', 'mostMissed', 'perfectQuestions'
            ));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Analytics Load Error: ' . $e->getMessage());
            $totalStudents = $completedCount = $completionRate = $totalQuestions = $averageScoreRaw = $averageScorePct = 0;
            $highestScoreRaw = $highestScorePct = $lowestScoreRaw = $lowestScorePct = $passedCount = $failedCount = 0;
            $overallAvgTime = 'N/A';
            $scoreDistribution = ['90-100%' => 0, '80-89%' => 0, '70-79%' => 0, '60-69%' => 0, 'Below 60%' => 0];
            $notTakenStudents = collect([]);
            $categoryLabels = $categoryScores = $categoryData = $itemAnalysis = $mostMissed = $perfectQuestions = [];

            return view('dashboard.partials.admin.assessments-analytics', compact(
                'assessment', 'totalQuestions', 'totalStudents', 'completedCount', 'completionRate', 'notTakenStudents',
                'averageScoreRaw', 'averageScorePct', 'overallAvgTime', 'highestScoreRaw', 'highestScorePct',
                'lowestScoreRaw', 'lowestScorePct', 'passedCount', 'failedCount', 'scoreDistribution',
                'categoryLabels', 'categoryScores', 'categoryData', 'itemAnalysis', 'mostMissed', 'perfectQuestions'
            ));
        }
    }

    public function exportReport(Request $request, \App\Models\Assessment $assessment)
    {
        try {
            $totalStudents = \Illuminate\Support\Facades\DB::table('assessment_accesses')->where('assessment_id', $assessment->id)->count();
            $completedCount = \Illuminate\Support\Facades\DB::table('assessment_accesses')->where('assessment_id', $assessment->id)->where('status', 'finished')->count();
            $completionRate = $totalStudents > 0 ? round(($completedCount / $totalStudents) * 100) : 0;
            
            $notTakenStudents = \Illuminate\Support\Facades\DB::table('assessment_accesses')
                ->where('assessment_id', $assessment->id)->where('status', 'offline')->select('lrn')->get();

            $totalQuestions = \Illuminate\Support\Facades\DB::table('assessment_questions')->join('assessment_categories', 'assessment_questions.category_id', '=', 'assessment_categories.id')->where('assessment_categories.assessment_id', $assessment->id)->count();

            $studentScoresRaw = \Illuminate\Support\Facades\DB::table('student_answers')
                ->join('assessment_questions', 'student_answers.question_id', '=', 'assessment_questions.id')
                ->join('assessment_categories', 'assessment_questions.category_id', '=', 'assessment_categories.id')
                ->join('assessment_options', 'student_answers.option_id', '=', 'assessment_options.id')
                ->where('assessment_categories.assessment_id', $assessment->id)
                ->select('student_answers.user_id', \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN assessment_options.is_correct = 1 THEN 1 ELSE 0 END) as correct_count'))
                ->groupBy('student_answers.user_id')->get();

            $highestScoreRaw = 0; $lowestScoreRaw = $totalQuestions > 0 ? $totalQuestions : 0; 
            $passedCount = 0; $failedCount = 0; $totalCorrectAnswers = 0;

            foreach ($studentScoresRaw as $scoreData) {
                $rawScore = $scoreData->correct_count;
                $pct = $totalQuestions > 0 ? round(($rawScore / $totalQuestions) * 100) : 0;
                $totalCorrectAnswers += $rawScore;

                if ($rawScore > $highestScoreRaw) $highestScoreRaw = $rawScore;
                if ($rawScore < $lowestScoreRaw) $lowestScoreRaw = $rawScore;
                if ($pct >= 75) $passedCount++; else $failedCount++;
            }
            if ($studentScoresRaw->isEmpty()) $lowestScoreRaw = 0; 

            $highestScorePct = $totalQuestions > 0 ? round(($highestScoreRaw / $totalQuestions) * 100) : 0;
            $lowestScorePct = $totalQuestions > 0 ? round(($lowestScoreRaw / $totalQuestions) * 100) : 0;
            $averageScorePct = ($completedCount > 0 && $totalQuestions > 0) ? round(($totalCorrectAnswers / ($completedCount * $totalQuestions)) * 100) : 0;
            $averageScoreRaw = $completedCount > 0 ? round($totalCorrectAnswers / $completedCount, 1) : 0;

            $overallAvgTimeRaw = \Illuminate\Support\Facades\DB::table('assessment_sessions')->where('assessment_id', $assessment->id)->where('is_completed', 1)->avg(\Illuminate\Support\Facades\DB::raw('TIMESTAMPDIFF(SECOND, created_at, updated_at)'));
            $overallAvgTime = $overallAvgTimeRaw ? sprintf('%02d:%02d', floor($overallAvgTimeRaw / 60), round($overallAvgTimeRaw % 60)) . ' mins' : 'N/A';

            $categoryPerformance = \Illuminate\Support\Facades\DB::table('assessment_categories')
                ->leftJoin('assessment_questions', 'assessment_categories.id', '=', 'assessment_questions.category_id')
                ->leftJoin('student_answers', 'assessment_questions.id', '=', 'student_answers.question_id')
                ->leftJoin('assessment_options', 'student_answers.option_id', '=', 'assessment_options.id')
                ->where('assessment_categories.assessment_id', $assessment->id)
                ->select('assessment_categories.id', 'assessment_categories.title', \Illuminate\Support\Facades\DB::raw('COUNT(student_answers.id) as total_answers'), \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN assessment_options.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers'))
                ->groupBy('assessment_categories.id', 'assessment_categories.title', 'assessment_categories.sort_order')->orderBy('assessment_categories.sort_order')->get();

            $categoryTimesRaw = \Illuminate\Support\Facades\DB::table('assessment_sessions')
                ->where('assessment_id', $assessment->id)->where('is_completed', 1)
                ->select('category_id', \Illuminate\Support\Facades\DB::raw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_time_seconds'))
                ->groupBy('category_id')->pluck('avg_time_seconds', 'category_id');

            $categoryData = [];
            foreach ($categoryPerformance as $cat) {
                $scorePct = $cat->total_answers > 0 ? round(($cat->correct_answers / $cat->total_answers) * 100) : 0;
                $avgTimeSec = $categoryTimesRaw[$cat->id] ?? 0;
                $categoryData[] = [
                    'title' => $cat->title, 'score_pct' => $scorePct,
                    'avg_time' => ($avgTimeSec > 0) ? sprintf('%02d:%02d', floor($avgTimeSec / 60), round($avgTimeSec % 60)) . ' mins' : 'N/A'
                ];
            }

            $itemAnalysis = \Illuminate\Support\Facades\DB::table('assessment_questions')
                ->join('assessment_categories', 'assessment_questions.category_id', '=', 'assessment_categories.id')
                ->leftJoin('student_answers', 'assessment_questions.id', '=', 'student_answers.question_id')
                ->leftJoin('assessment_options', 'student_answers.option_id', '=', 'assessment_options.id')
                ->where('assessment_categories.assessment_id', $assessment->id)
                ->select('assessment_questions.id', 'assessment_questions.question_text', 'assessment_categories.title as category_name', \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN assessment_options.is_correct = 1 THEN 1 ELSE 0 END) as correct_count'), \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN assessment_options.is_correct = 0 THEN 1 ELSE 0 END) as wrong_count'))
                ->groupBy('assessment_questions.id', 'assessment_questions.question_text', 'assessment_categories.title', 'assessment_categories.sort_order', 'assessment_questions.sort_order')
                ->orderBy('assessment_categories.sort_order')->orderBy('assessment_questions.sort_order')->get()
                ->map(function ($item) {
                    $total = $item->correct_count + $item->wrong_count;
                    $item->accuracy = $total > 0 ? round(($item->correct_count / $total) * 100) : 0;
                    $item->correct_count = (int) $item->correct_count; $item->wrong_count = (int) $item->wrong_count;
                    return $item;
                });

            $answeredItems = $itemAnalysis->filter(fn($item) => ($item->correct_count + $item->wrong_count) > 0);
            $mostMissed = $answeredItems->sortBy('accuracy')->take(3);
            $perfectQuestions = $answeredItems->filter(fn($item) => $item->accuracy == 100);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Export Error: ' . $e->getMessage());
            $totalStudents = $completedCount = $completionRate = $totalQuestions = $averageScoreRaw = $averageScorePct = 0;
            $highestScoreRaw = $highestScorePct = $lowestScoreRaw = $lowestScorePct = $passedCount = $failedCount = 0;
            $overallAvgTime = 'N/A';
            $notTakenStudents = collect([]);
            $categoryData = $itemAnalysis = $mostMissed = $perfectQuestions = [];
        }

        $isPrint = $request->input('action') === 'print';
        $data = [
            'assessment' => $assessment, 'totalQuestions' => $totalQuestions ?? 0, 'totalStudents' => $totalStudents ?? 0, 'completedCount' => $completedCount ?? 0,
            'completionRate' => $completionRate ?? 0, 'notTakenStudents' => $notTakenStudents,
            'averageScoreRaw' => $averageScoreRaw ?? 0, 'averageScorePct' => $averageScorePct ?? 0, 'overallAvgTime' => $overallAvgTime ?? 'N/A',
            'highestScoreRaw' => $highestScoreRaw ?? 0, 'highestScorePct' => $highestScorePct ?? 0,
            'lowestScoreRaw' => $lowestScoreRaw ?? 0, 'lowestScorePct' => $lowestScorePct ?? 0,
            'passedCount' => $passedCount ?? 0, 'failedCount' => $failedCount ?? 0,
            'categoryData' => $categoryData ?? [], 'itemAnalysis' => $itemAnalysis ?? [], 'mostMissed' => $mostMissed ?? [], 'perfectQuestions' => $perfectQuestions ?? collect([]),
            'showOverview' => $request->has('check_overview'), 'showCategory' => $request->has('check_category'), 'showItemAnalysis' => $request->has('check_item_analysis'),
            'isPrint' => $isPrint
        ];

        if ($isPrint) return view('dashboard.partials.admin.assessments-report', $data);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.partials.admin.assessments-report', $data);
        return $pdf->download('Assessment_Report_' . \Illuminate\Support\Str::slug($assessment->title) . '_' . now()->format('Y_m_d') . '.pdf');
    }
    
}