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
        } catch (\Exception $e) {
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
        $request->validate([
            'media_file' => 'required|file|mimes:jpeg,png,jpg,gif,mp3,wav,mp4,webm|max:51200',
        ]);

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $path = $file->store('assessment_media', 'public');

            // Explicitly detect the exact media type
            $ext = strtolower($file->getClientOriginalExtension());
            $type = 'image';
            if (in_array($ext, ['mp4', 'webm']))
                $type = 'video';
            if (in_array($ext, ['mp3', 'wav', 'ogg']))
                $type = 'audio';

            return response()->json([
                'success' => true,
                'media_url' => asset('storage/' . $path),
                'media_type' => $type // Send the exact type to Javascript!
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No media uploaded.'], 400);
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

    public function toggleResults(Request $request, \App\Models\Assessment $assessment)
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


    public function manage(\App\Models\Assessment $assessment)
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

    public function toggleStatus(Request $request, \App\Models\Assessment $assessment)
    {
        try {
            $newStatus = $assessment->status === 'published' ? 'draft' : 'published';
            
            \Illuminate\Support\Facades\DB::table('assessments')
                ->where('id', $assessment->id)
                ->update(['status' => $newStatus, 'updated_at' => now()]);

            return response()->json([
                'success' => true, 
                'new_status' => $newStatus,
                'message' => 'Assessment is now ' . ($newStatus === 'published' ? 'Live' : 'in Draft Mode')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }
}