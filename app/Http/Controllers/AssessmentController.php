<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
            ->get();

        return view('dashboard.partials.admin.assessment', compact('assessments'));
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
        // Path corrected: removed .assessments.
        return view('dashboard.partials.admin.assessment-create', compact('assessment'));
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

                // Fetch questions for this category
                $category->questions = DB::table('assessment_questions')
                    ->where('category_id', $category->id)
                    ->get()
                    ->map(function ($question) {

                    // NEW: Fetch options for each question so the JS can render them
                    $question->options = DB::table('assessment_options')
                        ->where('question_id', $question->id)
                        ->get();

                    return $question;
                });

                return $category;
            });

        // Path corrected: removed .assessments.
        return view('dashboard.partials.admin.assessment-create', compact('assessment', 'categories'));
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

                    // NEW: Loop through dynamic options
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
            DB::table('assessments')->where('id', $id)->delete();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}