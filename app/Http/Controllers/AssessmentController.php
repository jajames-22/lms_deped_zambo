<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

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
                'updated_at' => now()
            ]);

            $existingCategories = DB::table('assessment_categories')->where('assessment_id', $id)->pluck('id');

            if ($existingCategories->isNotEmpty()) {
                // ADD THIS: Find existing questions to delete their options first
                $existingQuestions = DB::table('assessment_questions')->whereIn('category_id', $existingCategories)->pluck('id');

                if ($existingQuestions->isNotEmpty()) {
                    DB::table('assessment_options')->whereIn('question_id', $existingQuestions)->delete();
                }

                // Now safely delete questions and categories
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
                    $questionId = DB::table('assessment_questions')->insertGetId([
                        'category_id' => $categoryId,
                        'question_text' => $q['text'] ?? '',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    foreach ($q['options'] as $opt) {
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
            DB::table('assessments')
                ->where('id', $id)
                ->update([
                    // If a field is null, fall back to an empty string to prevent DB constraint crashes
                    'title' => $request->title ?? 'Untitled Assessment',
                    'year_level' => $request->year_level ?? '',
                    'description' => $request->description ?? '',
                    // Guarantee valid JSON is always passed to the column
                    'draft_json' => json_encode($request->categories ?? []),
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true
            ]);

        } catch (Exception $e) {
            // Log the exact error to storage/logs/laravel.log
            Log::error('Autosave Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() // Send error to the frontend
            ], 500);
        }
    }
}