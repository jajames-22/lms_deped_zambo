<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class AssessmentController extends Controller
{
    public function create()
    {
        return view('dashboard.partials.admin.assessments.create');
    }

    public function storeSetup(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'year_level' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $accessKey = strtoupper(Str::random(6)); 

        $assessmentId = DB::table('assessments')->insertGetId([
            'title' => $request->title,
            'year_level' => $request->year_level,
            'description' => $request->description,
            'access_key' => $accessKey,
            'status' => 'draft', // Initializes as draft by default
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'redirect_url' => route('dashboard.assessments.builder', ['id' => $assessmentId])
        ]);
    }

    public function builder($id)
    {
        $assessment = DB::table('assessments')->where('id', $id)->first();

        if (!$assessment) {
            abort(404, 'Assessment not found');
        }

        return view('dashboard.partials.admin.assessments.builder', compact('assessment'));
    }

    public function storeQuestions(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Update the overall assessment status (draft vs published)
            DB::table('assessments')->where('id', $id)->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);

            // CLEAR EXISTING categories and questions for this assessment
            // This prevents duplication if a user clicks "Save Draft" multiple times.
            $existingCategories = DB::table('assessment_categories')->where('assessment_id', $id)->pluck('id');
            if ($existingCategories->isNotEmpty()) {
                DB::table('assessment_questions')->whereIn('category_id', $existingCategories)->delete();
                DB::table('assessment_categories')->where('assessment_id', $id)->delete();
            }

            foreach ($request->categories as $cat) {
                $categoryId = DB::table('assessment_categories')->insertGetId([
                    'assessment_id' => $id,
                    'title' => $cat['title'],
                    'time_limit' => $cat['time_limit'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $questionsToInsert = [];
                foreach ($cat['questions'] as $q) {
                    $questionsToInsert[] = [
                        'category_id' => $categoryId,
                        'question_text' => $q['text'],
                        'option_a' => $q['optA'],
                        'option_b' => $q['optB'],
                        'option_c' => $q['optC'],
                        'option_d' => $q['optD'],
                        'option_e' => $q['optE'] ?? '',
                        'option_f' => $q['optF'] ?? '',
                        'correct_answer' => $q['correct'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                if (!empty($questionsToInsert)) {
                    DB::table('assessment_questions')->insert($questionsToInsert);
                }
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}