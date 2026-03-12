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

class AssessmentController extends Controller
{
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
        return view('dashboard.partials.admin.assessments-create', compact('assessment'));
    }

    // Inside AssessmentController.php

    public function builder($id)
    {
        $assessment = DB::table('assessments')->where('id', $id)->first();

        if (!$assessment) {
            abort(404, 'Assessment not found');
        }

        $categories = DB::table('assessment_categories')
            ->where('assessment_id', $id)
            ->get()
            ->map(function ($category) {
                // Ensure type and media_url are fetched here
                $category->questions = DB::table('assessment_questions')
                    ->where('category_id', $category->id)
                    ->get()
                    ->map(function ($question) {
                    $question->options = DB::table('assessment_options')
                        ->where('question_id', $question->id)
                        ->get();
                    return $question;
                });
                return $category;
            });

        return view('dashboard.partials.admin.assessments-create', compact('assessment', 'categories'));
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

            $existingCategories = DB::table('assessment_categories')->where('assessment_id', $id)->pluck('id');

            if ($existingCategories->isNotEmpty()) {
                $existingQuestions = DB::table('assessment_questions')->whereIn('category_id', $existingCategories)->pluck('id');

                if ($existingQuestions->isNotEmpty()) {
                    DB::table('assessment_options')->whereIn('question_id', $existingQuestions)->delete();
                }

                DB::table('assessment_questions')->whereIn('category_id', $existingCategories)->delete();
                DB::table('assessment_categories')->where('assessment_id', $id)->delete();
            }

            foreach ($request->categories as $cat) {
                $categoryId = DB::table('assessment_categories')->insertGetId([
                    'assessment_id' => $id,
                    'title' => $cat['title'] ?? 'New Section',
                    'time_limit' => $cat['time_limit'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($cat['questions'] as $q) {
                    // NEW: Save the type and media_url
                    $questionId = DB::table('assessment_questions')->insertGetId([
                        'category_id' => $categoryId,
                        'type' => $q['type'] ?? 'mcq',
                        'question_text' => $q['text'] ?? '',
                        'media_url' => $q['media_url'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'is_case_sensitive' => $q['is_case_sensitive'] ?? false,
                    ]);

                    // NEW: Fallback to empty array if options don't exist (like for Instructions)
                    $options = $q['options'] ?? [];
                    foreach ($options as $opt) {
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

            // NEW: Explicitly detect the exact media type
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
            // ADD THIS: Manual cascade delete for all child records
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
            // This prevents the official title/description from being permanently overwritten!
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
                    foreach ($categories as $cat) {
                        $categoryId = DB::table('assessment_categories')->insertGetId([
                            'assessment_id' => $id,
                            'title' => $cat['title'] ?? 'New Section',
                            'time_limit' => (int) ($cat['time_limit'] ?? 0),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        foreach ($cat['questions'] ?? [] as $q) {
                            $questionId = DB::table('assessment_questions')->insertGetId([
                                'category_id' => $categoryId,
                                'type' => $q['type'] ?? 'mcq',
                                'question_text' => $q['text'] ?? '',
                                'media_url' => $q['media_url'] ?? null,
                                'created_at' => now(),
                                'updated_at' => now(),
                                'is_case_sensitive' => $q['is_case_sensitive'] ?? false,
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
}