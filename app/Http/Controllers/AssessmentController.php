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
        // Added 'questions' to the array so both counts are fetched!
        $assessments = \App\Models\Assessment::withCount(['categories', 'questions'])
            ->orderBy('updated_at', 'desc')
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
            // 1. Update the main Assessment record
            DB::table('assessments')->where('id', $id)->update([
                'title' => $request->title ?? 'Untitled Assessment',
                'year_level' => $request->year_level ?? '',
                'description' => $request->description ?? '',
                'status' => $request->status ?? 'draft',
                'draft_json' => null,
                'updated_at' => now()
            ]);

            $keptCategoryIds = [];
            $keptQuestionIds = [];
            $keptOptionIds = [];

            $categories = $request->categories ?? [];

            foreach ($categories as $index => $cat) {
                // Check for existing ID
                $categoryId = (isset($cat['id']) && is_numeric($cat['id'])) ? $cat['id'] : null;

                $catData = [
                    'assessment_id' => $id,
                    'title' => $cat['title'] ?? 'New Section',
                    'time_limit' => $cat['time_limit'] ?? 0,
                    'sort_order' => $index + 1,
                    'updated_at' => now(),
                ];

                // UPDATE if exists, else INSERT
                if ($categoryId && DB::table('assessment_categories')->where('id', $categoryId)->exists()) {
                    DB::table('assessment_categories')->where('id', $categoryId)->update($catData);
                } else {
                    $catData['created_at'] = now();
                    $categoryId = DB::table('assessment_categories')->insertGetId($catData);
                }
                $keptCategoryIds[] = $categoryId;

                foreach ($cat['questions'] ?? [] as $qIndex => $q) {
                    $questionId = (isset($q['id']) && is_numeric($q['id'])) ? $q['id'] : null;

                    $qData = [
                        'category_id' => $categoryId,
                        'type' => $q['type'] ?? 'mcq',
                        'question_text' => $q['text'] ?? '',
                        'media_url' => $q['media_url'] ?? null,
                        'media_name' => $q['media_name'] ?? null,
                        'is_case_sensitive' => $q['is_case_sensitive'] ?? false,
                        'sort_order' => $qIndex + 1,
                        'updated_at' => now(),
                    ];

                    if ($questionId && DB::table('assessment_questions')->where('id', $questionId)->exists()) {
                        DB::table('assessment_questions')->where('id', $questionId)->update($qData);
                    } else {
                        $qData['created_at'] = now();
                        $questionId = DB::table('assessment_questions')->insertGetId($qData);
                    }
                    $keptQuestionIds[] = $questionId;

                    foreach ($q['options'] ?? [] as $opt) {
                        $optId = (isset($opt['id']) && is_numeric($opt['id'])) ? $opt['id'] : null;

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

            // 2. TARGETED CLEANUP
            // Only delete records that belong to this assessment but were NOT in the $kept arrays.

            // Delete Options
            DB::table('assessment_options')
                ->whereIn('question_id', function ($query) use ($id) {
                    $query->select('id')->from('assessment_questions')
                        ->whereIn('category_id', function ($sub) use ($id) {
                            $sub->select('id')->from('assessment_categories')->where('assessment_id', $id);
                        });
                })
                ->whereNotIn('id', $keptOptionIds)
                ->delete();

            // Delete Questions
            DB::table('assessment_questions')
                ->whereIn('category_id', function ($query) use ($id) {
                    $query->select('id')->from('assessment_categories')->where('assessment_id', $id);
                })
                ->whereNotIn('id', $keptQuestionIds)
                ->delete();

            // Delete Categories
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
                'media_type' => $file->getClientMimeType(),
                'media_name' => $originalName,
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

            $keptCategoryIds = [];
            $keptQuestionIds = [];
            $keptOptionIds = [];

            // 2. Process and save manual UI changes using Upsert
            if ($request->has('categories')) {
                $categories = json_decode($request->categories, true);

                $keptCategoryIds = [];
                $keptQuestionIds = [];
                $keptOptionIds = [];

                if (is_array($categories)) {
                    foreach ($categories as $index => $cat) {
                        $categoryId = (isset($cat['id']) && is_numeric($cat['id'])) ? $cat['id'] : null;
                        $catData = [
                            'assessment_id' => $id,
                            'title' => $cat['title'] ?? 'New Section',
                            'time_limit' => (int) ($cat['time_limit'] ?? 0),
                            'sort_order' => $index + 1,
                            'updated_at' => now(),
                        ];

                        // UPSERT Category
                        if ($categoryId && DB::table('assessment_categories')->where('id', $categoryId)->exists()) {
                            DB::table('assessment_categories')->where('id', $categoryId)->update($catData);
                        } else {
                            $catData['created_at'] = now();
                            $categoryId = DB::table('assessment_categories')->insertGetId($catData);
                        }
                        $keptCategoryIds[] = $categoryId;

                        foreach ($cat['questions'] ?? [] as $qIndex => $q) {
                            $questionId = (isset($q['id']) && is_numeric($q['id'])) ? $q['id'] : null;
                            $qData = [
                                'category_id' => $categoryId,
                                'type' => $q['type'] ?? 'mcq',
                                'question_text' => $q['text'] ?? '',
                                'media_url' => $q['media_url'] ?? null,
                                'is_case_sensitive' => $q['is_case_sensitive'] ?? false,
                                'sort_order' => $qIndex + 1,
                                'updated_at' => now(),
                            ];

                            // UPSERT Question
                            if ($questionId && DB::table('assessment_questions')->where('id', $questionId)->exists()) {
                                DB::table('assessment_questions')->where('id', $questionId)->update($qData);
                            } else {
                                $qData['created_at'] = now();
                                $questionId = DB::table('assessment_questions')->insertGetId($qData);
                            }
                            $keptQuestionIds[] = $questionId;

                            foreach ($q['options'] ?? [] as $opt) {
                                $optId = (isset($opt['id']) && is_numeric($opt['id'])) ? $opt['id'] : null;
                                $optData = [
                                    'question_id' => $questionId,
                                    'option_text' => $opt['text'] ?? '',
                                    'is_correct' => $opt['is_correct'] ?? false,
                                    'updated_at' => now(),
                                ];

                                // UPSERT Option
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
                }

                // Perform targeted cleanup for items removed from UI
                $this->cleanupRemovedItems($id, $keptCategoryIds, $keptQuestionIds, $keptOptionIds);
            }

            // 3. Run the Excel import (This now uses the updated ExamImport logic)
            // Note: The ExamImport should NOT clear data internally anymore; it should append/update.
            Excel::import(new ExamImport($id), $request->file('exam_file'));

            // 4. FINAL TARGETED CLEANUP 
            // We only delete items that weren't in the $kept arrays AND weren't just added by the Excel Import
            // (Note: To be 100% perfect, you would need to merge IDs from ExamImport into the 'kept' arrays)

            // If your ExamImport also needs to be non-destructive, ensure it returns or tracks its IDs.
            // For a simpler "Safe Import", we commit the manual changes here.

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Questions imported and manual changes synced successfully!'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cleans up deleted categories, questions, and options from the database.
     */
    private function cleanupRemovedItems($assessmentId, array $keptCategoryIds, array $keptQuestionIds, array $keptOptionIds)
    {
        // 1. Delete Options that were removed
        \Illuminate\Support\Facades\DB::table('assessment_options')
            ->whereIn('question_id', function ($query) use ($assessmentId) {
                $query->select('id')->from('assessment_questions')
                    ->whereIn('category_id', function ($sub) use ($assessmentId) {
                        $sub->select('id')->from('assessment_categories')->where('assessment_id', $assessmentId);
                    });
            })
            ->whereNotIn('id', $keptOptionIds)
            ->delete();

        // 2. Delete Questions that were removed
        \Illuminate\Support\Facades\DB::table('assessment_questions')
            ->whereIn('category_id', function ($query) use ($assessmentId) {
                $query->select('id')->from('assessment_categories')->where('assessment_id', $assessmentId);
            })
            ->whereNotIn('id', $keptQuestionIds)
            ->delete();

        // 3. Delete Categories that were removed
        \Illuminate\Support\Facades\DB::table('assessment_categories')
            ->where('assessment_id', $assessmentId)
            ->whereNotIn('id', $keptCategoryIds)
            ->delete();
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
            // 1. Cohort Participation & Pacing Stats
            $totalStudents = \Illuminate\Support\Facades\DB::table('assessment_accesses')->where('assessment_id', $assessment->id)->count();
            $completedCount = \Illuminate\Support\Facades\DB::table('assessment_accesses')->where('assessment_id', $assessment->id)->where('status', 'finished')->count();
            $completionRate = $totalStudents > 0 ? round(($completedCount / $totalStudents) * 100) : 0;

            $userTimesRaw = \Illuminate\Support\Facades\DB::table('assessment_sessions')
                ->where('assessment_id', $assessment->id)->where('is_completed', 1)
                ->select('user_id', \Illuminate\Support\Facades\DB::raw('SUM(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as total_time'))
                ->groupBy('user_id')->pluck('total_time', 'user_id');

            $timesArray = $userTimesRaw->values()->filter(fn($t) => $t > 0)->toArray();
            $avgTimeSecs = count($timesArray) > 0 ? array_sum($timesArray) / count($timesArray) : 0;
            $avgTimeFormat = $avgTimeSecs > 0 ? floor($avgTimeSecs / 60) . 'm ' . round($avgTimeSecs % 60) . 's' : 'N/A';
            $avgTimeMins = $avgTimeSecs > 0 ? round($avgTimeSecs / 60, 2) : 0;

            // 2. Total Questions (EXCLUDING INSTRUCTIONS)
            $questionsData = \Illuminate\Support\Facades\DB::table('assessment_questions')
                ->join('assessment_categories', 'assessment_questions.category_id', '=', 'assessment_categories.id')
                ->where('assessment_categories.assessment_id', $assessment->id)
                ->where('assessment_questions.type', '!=', 'instruction')
                ->select(
                    'assessment_questions.id',
                    'assessment_questions.type',
                    'assessment_questions.question_text',
                    'assessment_questions.is_case_sensitive',
                    'assessment_categories.id as category_id',
                    'assessment_categories.title as category_title',
                    'assessment_categories.sort_order as cat_sort',
                    'assessment_questions.sort_order as q_sort'
                )
                ->orderBy('cat_sort')->orderBy('q_sort')
                ->get();

            $totalQuestions = $questionsData->count();

            // 3. Pre-process Questions & Options for Strict Grading Rules
            $optionsData = \Illuminate\Support\Facades\DB::table('assessment_options')
                ->whereIn('question_id', $questionsData->pluck('id'))
                ->get();

            $qMap = [];
            $catScoresMap = [];

            foreach ($questionsData as $q) {
                if (!isset($catScoresMap[$q->category_id])) {
                    $catScoresMap[$q->category_id] = [
                        'title' => $q->category_title,
                        'total_answers' => 0,
                        'correct_answers' => 0
                    ];
                }

                $opts = $optionsData->where('question_id', $q->id);
                $correctOptionIds = [];
                $correctTexts = [];
                $originalCorrectTexts = [];
                $distractorMap = [];

                foreach ($opts as $opt) {
                    $distractorMap[$opt->id] = (object) [
                        'id' => $opt->id,
                        'text' => $opt->option_text,
                        'is_correct' => $opt->is_correct,
                        'count' => 0
                    ];
                    if ($opt->is_correct) {
                        $correctOptionIds[] = (string) $opt->id;
                        $correctTexts[] = $q->is_case_sensitive ? trim($opt->option_text) : strtolower(trim($opt->option_text));
                        $originalCorrectTexts[] = trim($opt->option_text);
                    }
                }

                $qMap[$q->id] = [
                    'q' => $q,
                    'correct_option_ids' => $correctOptionIds,
                    'correct_texts' => $correctTexts,
                    'original_correct_texts' => $originalCorrectTexts,
                    'options' => $distractorMap,
                    'text_responses' => [],
                    'stats' => ['correct' => 0, 'wrong' => 0]
                ];
            }

            // 4. Grade Student Answers strictly per Question Type
            $answers = \Illuminate\Support\Facades\DB::table('student_answers')
                ->join('assessment_questions', 'student_answers.question_id', '=', 'assessment_questions.id')
                ->join('assessment_categories', 'assessment_questions.category_id', '=', 'assessment_categories.id')
                ->where('assessment_categories.assessment_id', $assessment->id)
                ->where('assessment_questions.type', '!=', 'instruction')
                ->select('student_answers.user_id', 'student_answers.question_id', 'student_answers.selected_options')
                ->get();

            $userScoresMap = [];

            foreach ($answers as $ans) {
                if (!isset($qMap[$ans->question_id]))
                    continue;

                $qInfo = &$qMap[$ans->question_id];
                $type = $qInfo['q']->type;
                $isCaseSens = $qInfo['q']->is_case_sensitive;
                $catId = $qInfo['q']->category_id;

                $selected = json_decode($ans->selected_options, true) ?? [];
                if (!is_array($selected))
                    $selected = [$selected];

                $isCorrect = false;

                if ($type === 'checkbox') {
                    $selectedStr = array_map('strval', $selected);
                    $correctStr = $qInfo['correct_option_ids'];
                    if (count($selectedStr) === count($correctStr) && empty(array_diff($selectedStr, $correctStr)) && empty(array_diff($correctStr, $selectedStr))) {
                        $isCorrect = true;
                    }
                    foreach ($selectedStr as $sId) {
                        if (isset($qInfo['options'][$sId]))
                            $qInfo['options'][$sId]->count++;
                    }

                } elseif ($type === 'text') {
                    $ansText = $selected[0] ?? '';
                    $ansTextClean = trim((string) $ansText);
                    $displayStr = $ansTextClean === '' ? '(Blank)' : $ansTextClean;
                    $ansTextCmp = $isCaseSens ? $ansTextClean : strtolower($ansTextClean);

                    if (in_array($ansTextCmp, $qInfo['correct_texts'])) {
                        $isCorrect = true;
                    }

                    if (!isset($qInfo['text_responses'][$ansTextCmp])) {
                        $qInfo['text_responses'][$ansTextCmp] = [
                            'display' => $displayStr,
                            'count' => 0,
                            'is_correct' => $isCorrect
                        ];
                    }
                    $qInfo['text_responses'][$ansTextCmp]['count']++;

                } else {
                    $selectedStr = array_map('strval', $selected);
                    foreach ($selectedStr as $sId) {
                        if (in_array($sId, $qInfo['correct_option_ids'])) {
                            $isCorrect = true;
                        }
                        if (isset($qInfo['options'][$sId]))
                            $qInfo['options'][$sId]->count++;
                    }
                }

                if (!isset($userScoresMap[$ans->user_id]))
                    $userScoresMap[$ans->user_id] = 0;

                if ($isCorrect) {
                    $userScoresMap[$ans->user_id]++;
                    $qInfo['stats']['correct']++;
                    $catScoresMap[$catId]['correct_answers']++;
                } else {
                    $qInfo['stats']['wrong']++;
                }

                $catScoresMap[$catId]['total_answers']++;
            }

            // 5. Build Individual Scores & Proficiency Levels array
            $highestScoreRaw = 0;
            $scoresArray = [];
            $totalPercentageSum = 0;
            $proficiencyLevels = [
                'Highly Proficient (90-100%)' => 0,
                'Proficient (75-89%)' => 0,
                'Nearly Proficient (50-74%)' => 0,
                'Low Proficient (25-49%)' => 0,
                'Not Proficient (0-24%)' => 0,
            ];
            $scatterData = [];

            foreach ($userScoresMap as $userId => $rawScore) {
                $pct = $totalQuestions > 0 ? round(($rawScore / $totalQuestions) * 100, 2) : 0;

                $scoresArray[] = $pct;
                $totalPercentageSum += $pct;

                if ($rawScore > $highestScoreRaw)
                    $highestScoreRaw = $rawScore;

                if ($pct >= 90)
                    $proficiencyLevels['Highly Proficient (90-100%)']++;
                elseif ($pct >= 75)
                    $proficiencyLevels['Proficient (75-89%)']++;
                elseif ($pct >= 50)
                    $proficiencyLevels['Nearly Proficient (50-74%)']++;
                elseif ($pct >= 25)
                    $proficiencyLevels['Low Proficient (25-49%)']++;
                else
                    $proficiencyLevels['Not Proficient (0-24%)']++;

                $timeSecs = $userTimesRaw->get($userId, 0);
                $timeMins = round($timeSecs / 60, 2);
                if ($timeMins > 0) {
                    $scatterData[] = [
                        'x' => $timeMins,
                        'y' => $pct
                    ];
                }
            }

            $scoresCount = count($scoresArray);
            $overallMPS = $scoresCount > 0 ? round($totalPercentageSum / $scoresCount, 2) : 0;

            // --- NEW: Calculate Overall Descriptive Level ---
            $overallMasteryLevel = 'Not Proficient';
            $masteryColor = 'text-red-600';
            if ($overallMPS >= 90) {
                $overallMasteryLevel = 'Highly Proficient';
                $masteryColor = 'text-green-600';
            } elseif ($overallMPS >= 75) {
                $overallMasteryLevel = 'Proficient';
                $masteryColor = 'text-blue-600';
            } elseif ($overallMPS >= 50) {
                $overallMasteryLevel = 'Nearly Proficient';
                $masteryColor = 'text-amber-600';
            } elseif ($overallMPS >= 25) {
                $overallMasteryLevel = 'Low Proficient';
                $masteryColor = 'text-orange-600';
            }

            $proficientCount = $proficiencyLevels['Highly Proficient (90-100%)'] + $proficiencyLevels['Proficient (75-89%)'];
            $proficiencyRate = $scoresCount > 0 ? round(($proficientCount / $scoresCount) * 100, 1) : 0;

            // 6. School Benchmarking
            $userIds = array_keys($userScoresMap);
            $users = \App\Models\User::with('school')->whereIn('id', $userIds)->get()->keyBy('id');
            $schoolScores = [];

            foreach ($userScoresMap as $userId => $rawScore) {
                $user = $users->get($userId);
                $schoolName = ($user && $user->school) ? $user->school->name : 'Independent / Unassigned';

                if (!isset($schoolScores[$schoolName])) {
                    $schoolScores[$schoolName] = ['total_score' => 0, 'student_count' => 0];
                }
                $schoolScores[$schoolName]['total_score'] += $rawScore;
                $schoolScores[$schoolName]['student_count']++;
            }

            $schoolLeaderboard = collect($schoolScores)->map(function ($data, $name) use ($totalQuestions) {
                $mps = $data['student_count'] > 0 && $totalQuestions > 0 ? round(($data['total_score'] / ($data['student_count'] * $totalQuestions)) * 100, 2) : 0;
                return (object) ['name' => $name, 'mps' => $mps, 'student_count' => $data['student_count']];
            })->sortByDesc('mps')->values();

            // 7. Full Competency Breakdown (With LMC & MMC extraction)
            $competencies = collect([]);
            foreach ($catScoresMap as $catId => $data) {
                $totalAns = $data['total_answers'];
                $correctAns = $data['correct_answers'];
                $mps = $totalAns > 0 ? round(($correctAns / $totalAns) * 100, 2) : 0;

                $competencies->push((object) [
                    'title' => $data['title'],
                    'mps' => $mps
                ]);
            }

            // --- NEW: Extract Most and Least Mastered Competencies ---
            $mostMastered = $competencies->isNotEmpty() ? $competencies->sortByDesc('mps')->first() : null;
            $leastMastered = $competencies->isNotEmpty() ? $competencies->sortBy('mps')->first() : null;

            // 8. Psychometric Item Analysis & Top Misconceptions
            $itemAnalysis = collect([]);
            $allDistractors = collect([]);

            foreach ($qMap as $qId => $data) {
                $total = $data['stats']['correct'] + $data['stats']['wrong'];
                $diffIndex = $total > 0 ? round(($data['stats']['correct'] / $total) * 100) : 0;
                $distractorStats = [];

                if ($data['q']->type === 'text') {
                    foreach ($data['text_responses'] as $resp) {
                        $pct = $total > 0 ? round(($resp['count'] / $total) * 100) : 0;
                        $distractorStats[] = (object) ['text' => $resp['display'], 'pct' => $pct, 'is_correct' => $resp['is_correct']];
                        if (!$resp['is_correct'] && $pct > 0) {
                            $allDistractors->push((object) ['question_text' => $data['q']->question_text, 'category_name' => $data['q']->category_title, 'distractor_text' => $resp['display'], 'pct' => $pct]);
                        }
                    }
                    foreach ($data['original_correct_texts'] as $correctText) {
                        $cmpKey = $data['q']->is_case_sensitive ? trim($correctText) : strtolower(trim($correctText));
                        if (!isset($data['text_responses'][$cmpKey])) {
                            $distractorStats[] = (object) ['text' => $correctText, 'pct' => 0, 'is_correct' => true];
                        }
                    }
                    usort($distractorStats, fn($a, $b) => $b->pct <=> $a->pct);
                } else {
                    foreach ($data['options'] as $opt) {
                        $pct = $total > 0 ? round(($opt->count / $total) * 100) : 0;
                        $distractorStats[] = (object) ['text' => $opt->text, 'pct' => $pct, 'is_correct' => $opt->is_correct];
                        if (!$opt->is_correct && $pct > 0) {
                            $allDistractors->push((object) ['question_text' => $data['q']->question_text, 'category_name' => $data['q']->category_title, 'distractor_text' => $opt->text, 'pct' => $pct]);
                        }
                    }
                }

                $itemAnalysis->push((object) [
                    'id' => $qId,
                    'question_text' => $data['q']->question_text,
                    'category_name' => $data['q']->category_title,
                    'correct_count' => $data['stats']['correct'],
                    'wrong_count' => $data['stats']['wrong'],
                    'difficulty_index' => $diffIndex,
                    'distractor_stats' => $distractorStats
                ]);
            }

            $topMisconceptions = $allDistractors->sortByDesc('pct')->take(3)->values();

            return view('dashboard.partials.admin.assessments-analytics', compact(
                'assessment',
                'totalQuestions',
                'totalStudents',
                'completedCount',
                'completionRate',
                'overallMPS',
                'overallMasteryLevel',
                'masteryColor',
                'mostMastered',
                'leastMastered',
                'proficiencyRate',
                'avgTimeFormat',
                'schoolLeaderboard',
                'proficiencyLevels',
                'competencies',
                'topMisconceptions',
                'itemAnalysis',
                'scatterData',
                'avgTimeMins'
            ));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Analytics Load Error: ' . $e->getMessage());
            $totalStudents = $completedCount = $completionRate = $totalQuestions = $overallMPS = $proficiencyRate = $avgTimeMins = 0;
            $overallMasteryLevel = 'N/A';
            $masteryColor = 'text-gray-500';
            $mostMastered = null;
            $leastMastered = null;
            $proficiencyLevels = ['Highly Proficient (90-100%)' => 0, 'Proficient (75-89%)' => 0, 'Nearly Proficient (50-74%)' => 0, 'Low Proficient (25-49%)' => 0, 'Not Proficient (0-24%)' => 0];
            $competencies = $schoolLeaderboard = $topMisconceptions = collect([]);
            $itemAnalysis = [];
            $avgTimeFormat = 'N/A';
            $scatterData = [];

            return view('dashboard.partials.admin.assessments-analytics', compact(
                'assessment',
                'totalQuestions',
                'totalStudents',
                'completedCount',
                'completionRate',
                'overallMPS',
                'overallMasteryLevel',
                'masteryColor',
                'mostMastered',
                'leastMastered',
                'proficiencyRate',
                'avgTimeFormat',
                'schoolLeaderboard',
                'proficiencyLevels',
                'competencies',
                'topMisconceptions',
                'itemAnalysis',
                'scatterData',
                'avgTimeMins'
            ));
        }
    }


    public function exportReport(Request $request, \App\Models\Assessment $assessment)
    {
        try {
            // 1. Cohort Participation & Pacing
            $totalStudents = \Illuminate\Support\Facades\DB::table('assessment_accesses')->where('assessment_id', $assessment->id)->count();
            $completedCount = \Illuminate\Support\Facades\DB::table('assessment_accesses')->where('assessment_id', $assessment->id)->where('status', 'finished')->count();
            $completionRate = $totalStudents > 0 ? round(($completedCount / $totalStudents) * 100) : 0;

            $notTakenStudents = \Illuminate\Support\Facades\DB::table('assessment_accesses')
                ->where('assessment_id', $assessment->id)->where('status', 'offline')->select('lrn')->get();

            $userTimesRaw = \Illuminate\Support\Facades\DB::table('assessment_sessions')
                ->where('assessment_id', $assessment->id)->where('is_completed', 1)
                ->select('user_id', \Illuminate\Support\Facades\DB::raw('SUM(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as total_time'))
                ->groupBy('user_id')->pluck('total_time', 'user_id');

            $timesArray = $userTimesRaw->values()->filter(fn($t) => $t > 0)->toArray();
            $avgTimeSecs = count($timesArray) > 0 ? array_sum($timesArray) / count($timesArray) : 0;
            $avgTimeFormat = $avgTimeSecs > 0 ? floor($avgTimeSecs / 60) . 'm ' . round($avgTimeSecs % 60) . 's' : 'N/A';

            // 2. Pre-process Questions & Options
            $questionsData = \Illuminate\Support\Facades\DB::table('assessment_questions')
                ->join('assessment_categories', 'assessment_questions.category_id', '=', 'assessment_categories.id')
                ->where('assessment_categories.assessment_id', $assessment->id)
                ->where('assessment_questions.type', '!=', 'instruction')
                ->select(
                    'assessment_questions.id',
                    'assessment_questions.type',
                    'assessment_questions.question_text',
                    'assessment_questions.is_case_sensitive',
                    'assessment_categories.id as category_id',
                    'assessment_categories.title as category_title',
                    'assessment_categories.sort_order as cat_sort',
                    'assessment_questions.sort_order as q_sort'
                )->orderBy('cat_sort')->orderBy('q_sort')->get();

            $totalQuestions = $questionsData->count();
            $optionsData = \Illuminate\Support\Facades\DB::table('assessment_options')->whereIn('question_id', $questionsData->pluck('id'))->get();

            $qMap = [];
            $catScoresMap = [];
            foreach ($questionsData as $q) {
                if (!isset($catScoresMap[$q->category_id])) {
                    $catScoresMap[$q->category_id] = ['title' => $q->category_title, 'total_answers' => 0, 'correct_answers' => 0];
                }
                $opts = $optionsData->where('question_id', $q->id);
                $correctOptionIds = [];
                $correctTexts = [];
                $originalCorrectTexts = [];
                $distractorMap = [];

                foreach ($opts as $opt) {
                    $distractorMap[$opt->id] = (object) ['id' => $opt->id, 'text' => $opt->option_text, 'is_correct' => $opt->is_correct, 'count' => 0];
                    if ($opt->is_correct) {
                        $correctOptionIds[] = (string) $opt->id;
                        $correctTexts[] = $q->is_case_sensitive ? trim($opt->option_text) : strtolower(trim($opt->option_text));
                        $originalCorrectTexts[] = trim($opt->option_text);
                    }
                }
                $qMap[$q->id] = [
                    'q' => $q,
                    'correct_option_ids' => $correctOptionIds,
                    'correct_texts' => $correctTexts,
                    'original_correct_texts' => $originalCorrectTexts,
                    'options' => $distractorMap,
                    'text_responses' => [],
                    'stats' => ['correct' => 0, 'wrong' => 0]
                ];
            }

            // 3. Strict Grading
            $answers = \Illuminate\Support\Facades\DB::table('student_answers')
                ->join('assessment_questions', 'student_answers.question_id', '=', 'assessment_questions.id')
                ->join('assessment_categories', 'assessment_questions.category_id', '=', 'assessment_categories.id')
                ->where('assessment_categories.assessment_id', $assessment->id)->where('assessment_questions.type', '!=', 'instruction')
                ->select('student_answers.user_id', 'student_answers.question_id', 'student_answers.selected_options')->get();

            $userScoresMap = [];
            foreach ($answers as $ans) {
                if (!isset($qMap[$ans->question_id]))
                    continue;
                $qInfo = &$qMap[$ans->question_id];
                $type = $qInfo['q']->type;
                $isCaseSens = $qInfo['q']->is_case_sensitive;
                $catId = $qInfo['q']->category_id;

                $selected = json_decode($ans->selected_options, true) ?? [];
                if (!is_array($selected))
                    $selected = [$selected];
                $isCorrect = false;

                if ($type === 'checkbox') {
                    $selectedStr = array_map('strval', $selected);
                    $correctStr = $qInfo['correct_option_ids'];
                    if (count($selectedStr) === count($correctStr) && empty(array_diff($selectedStr, $correctStr)) && empty(array_diff($correctStr, $selectedStr)))
                        $isCorrect = true;
                    foreach ($selectedStr as $sId) {
                        if (isset($qInfo['options'][$sId]))
                            $qInfo['options'][$sId]->count++;
                    }
                } elseif ($type === 'text') {
                    $ansText = $selected[0] ?? '';
                    $ansTextClean = trim((string) $ansText);
                    $displayStr = $ansTextClean === '' ? '(Blank)' : $ansTextClean;
                    $ansTextCmp = $isCaseSens ? $ansTextClean : strtolower($ansTextClean);
                    if (in_array($ansTextCmp, $qInfo['correct_texts']))
                        $isCorrect = true;

                    if (!isset($qInfo['text_responses'][$ansTextCmp])) {
                        $qInfo['text_responses'][$ansTextCmp] = ['display' => $displayStr, 'count' => 0, 'is_correct' => $isCorrect];
                    }
                    $qInfo['text_responses'][$ansTextCmp]['count']++;
                } else {
                    $selectedStr = array_map('strval', $selected);
                    foreach ($selectedStr as $sId) {
                        if (in_array($sId, $qInfo['correct_option_ids']))
                            $isCorrect = true;
                        if (isset($qInfo['options'][$sId]))
                            $qInfo['options'][$sId]->count++;
                    }
                }

                if (!isset($userScoresMap[$ans->user_id]))
                    $userScoresMap[$ans->user_id] = 0;
                if ($isCorrect) {
                    $userScoresMap[$ans->user_id]++;
                    $qInfo['stats']['correct']++;
                    $catScoresMap[$catId]['correct_answers']++;
                } else {
                    $qInfo['stats']['wrong']++;
                }
                $catScoresMap[$catId]['total_answers']++;
            }

            // 4. Calculate KPIs
            $scoresArray = [];
            $totalPercentageSum = 0;
            $proficiencyLevels = ['Highly Proficient (90-100%)' => 0, 'Proficient (75-89%)' => 0, 'Nearly Proficient (50-74%)' => 0, 'Low Proficient (25-49%)' => 0, 'Not Proficient (0-24%)' => 0];

            foreach ($userScoresMap as $userId => $rawScore) {
                $pct = $totalQuestions > 0 ? round(($rawScore / $totalQuestions) * 100, 2) : 0;
                $scoresArray[] = $pct;
                $totalPercentageSum += $pct;
                if ($pct >= 90)
                    $proficiencyLevels['Highly Proficient (90-100%)']++;
                elseif ($pct >= 75)
                    $proficiencyLevels['Proficient (75-89%)']++;
                elseif ($pct >= 50)
                    $proficiencyLevels['Nearly Proficient (50-74%)']++;
                elseif ($pct >= 25)
                    $proficiencyLevels['Low Proficient (25-49%)']++;
                else
                    $proficiencyLevels['Not Proficient (0-24%)']++;
            }

            $scoresCount = count($scoresArray);
            $overallMPS = $scoresCount > 0 ? round($totalPercentageSum / $scoresCount, 2) : 0;

            // Hex colors for PDF compatibility
            $overallMasteryLevel = 'Not Proficient';
            $masteryColor = '#dc2626'; // Red
            if ($overallMPS >= 90) {
                $overallMasteryLevel = 'Highly Proficient';
                $masteryColor = '#16a34a';
            } // Green
            elseif ($overallMPS >= 75) {
                $overallMasteryLevel = 'Proficient';
                $masteryColor = '#2563eb';
            } // Blue
            elseif ($overallMPS >= 50) {
                $overallMasteryLevel = 'Nearly Proficient';
                $masteryColor = '#d97706';
            } // Amber
            elseif ($overallMPS >= 25) {
                $overallMasteryLevel = 'Low Proficient';
                $masteryColor = '#ea580c';
            } // Orange

            $proficientCount = $proficiencyLevels['Highly Proficient (90-100%)'] + $proficiencyLevels['Proficient (75-89%)'];
            $proficiencyRate = $scoresCount > 0 ? round(($proficientCount / $scoresCount) * 100, 1) : 0;

            // 5. Competencies & Item Analysis
            $competencies = collect([]);
            foreach ($catScoresMap as $catId => $data) {
                $totalAns = $data['total_answers'];
                $correctAns = $data['correct_answers'];
                $mps = $totalAns > 0 ? round(($correctAns / $totalAns) * 100, 2) : 0;
                $competencies->push((object) ['title' => $data['title'], 'mps' => $mps]);
            }
            $mostMastered = $competencies->isNotEmpty() ? $competencies->sortByDesc('mps')->first() : null;
            $leastMastered = $competencies->isNotEmpty() ? $competencies->sortBy('mps')->first() : null;

            $itemAnalysis = collect([]);
            $allDistractors = collect([]);
            foreach ($qMap as $qId => $data) {
                $total = $data['stats']['correct'] + $data['stats']['wrong'];
                $diffIndex = $total > 0 ? round(($data['stats']['correct'] / $total) * 100) : 0;
                $distractorStats = [];

                if ($data['q']->type === 'text') {
                    foreach ($data['text_responses'] as $resp) {
                        $pct = $total > 0 ? round(($resp['count'] / $total) * 100) : 0;
                        $distractorStats[] = (object) ['text' => $resp['display'], 'pct' => $pct, 'is_correct' => $resp['is_correct']];
                        if (!$resp['is_correct'] && $pct > 0)
                            $allDistractors->push((object) ['question_text' => $data['q']->question_text, 'category_name' => $data['q']->category_title, 'distractor_text' => $resp['display'], 'pct' => $pct]);
                    }
                    foreach ($data['original_correct_texts'] as $correctText) {
                        $cmpKey = $data['q']->is_case_sensitive ? trim($correctText) : strtolower(trim($correctText));
                        if (!isset($data['text_responses'][$cmpKey]))
                            $distractorStats[] = (object) ['text' => $correctText, 'pct' => 0, 'is_correct' => true];
                    }
                    usort($distractorStats, fn($a, $b) => $b->pct <=> $a->pct);
                } else {
                    foreach ($data['options'] as $opt) {
                        $pct = $total > 0 ? round(($opt->count / $total) * 100) : 0;
                        $distractorStats[] = (object) ['text' => $opt->text, 'pct' => $pct, 'is_correct' => $opt->is_correct];
                        if (!$opt->is_correct && $pct > 0)
                            $allDistractors->push((object) ['question_text' => $data['q']->question_text, 'category_name' => $data['q']->category_title, 'distractor_text' => $opt->text, 'pct' => $pct]);
                    }
                }

                $itemAnalysis->push((object) [
                    'id' => $qId,
                    'question_text' => $data['q']->question_text,
                    'category_name' => $data['q']->category_title,
                    'correct_count' => $data['stats']['correct'],
                    'wrong_count' => $data['stats']['wrong'],
                    'difficulty_index' => $diffIndex,
                    'distractor_stats' => $distractorStats
                ]);
            }

            $topMisconceptions = $allDistractors->sortByDesc('pct')->take(3)->values();

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Export Error: ' . $e->getMessage());
            $totalStudents = $completedCount = $completionRate = $totalQuestions = $overallMPS = $proficiencyRate = 0;
            $overallMasteryLevel = 'N/A';
            $masteryColor = '#666666';
            $avgTimeFormat = 'N/A';
            $mostMastered = $leastMastered = null;
            $notTakenStudents = collect([]);
            $proficiencyLevels = ['Highly Proficient (90-100%)' => 0, 'Proficient (75-89%)' => 0, 'Nearly Proficient (50-74%)' => 0, 'Low Proficient (25-49%)' => 0, 'Not Proficient (0-24%)' => 0];
            $competencies = $itemAnalysis = $topMisconceptions = collect([]);
        }

        $isPrint = $request->input('action') === 'print';

        $data = compact(
            'assessment',
            'totalQuestions',
            'totalStudents',
            'completedCount',
            'completionRate',
            'notTakenStudents',
            'overallMPS',
            'overallMasteryLevel',
            'masteryColor',
            'proficiencyRate',
            'avgTimeFormat',
            'proficiencyLevels',
            'competencies',
            'mostMastered',
            'leastMastered',
            'itemAnalysis',
            'topMisconceptions',
            'isPrint'
        );
        $data['showOverview'] = $request->has('check_overview');
        $data['showCategory'] = $request->has('check_category');
        $data['showItemAnalysis'] = $request->has('check_item_analysis');

        if ($isPrint)
            return view('dashboard.partials.admin.assessments-report', $data);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.partials.admin.assessments-report', $data);
        return $pdf->download('Assessment_Report_' . \Illuminate\Support\Str::slug($assessment->title) . '_' . now()->format('Y_m_d') . '.pdf');
    }
}