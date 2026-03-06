<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class AssessmentController extends Controller
{
    // --- STEP 1: LOAD SETUP PAGE ---
    public function create()
    {
        // Path: resources/views/dashboard/partials/admin/assessments/create.blade.php
        return view('dashboard.partials.admin.assessments.create');
    }

    // --- STEP 1: SAVE SETUP & REDIRECT ---
    public function storeSetup(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'year_level' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $accessKey = strtoupper(Str::random(6)); // Generate 6-digit key

        // Insert using DB Facade
        $assessmentId = DB::table('assessments')->insertGetId([
            'title' => $request->title,
            'year_level' => $request->year_level,
            'description' => $request->description,
            'access_key' => $accessKey,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Return JSON with redirect URL so your AJAX can handle the transition
        return response()->json([
            'success' => true,
            'redirect_url' => route('dashboard.assessments.builder', ['id' => $assessmentId])
        ]);
    }

    // --- STEP 2: LOAD BUILDER PAGE ---
    public function builder($id)
    {
        $assessment = DB::table('assessments')->where('id', $id)->first();

        if (!$assessment) {
            abort(404, 'Assessment not found');
        }

        // Path: resources/views/dashboard/partials/admin/assessments/builder.blade.php
        return view('dashboard.partials.admin.assessments.builder', compact('assessment'));
    }

    // --- STEP 2: SAVE CATEGORIES & QUESTIONS ---
    public function storeQuestions(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            foreach ($request->categories as $cat) {
                // Insert Category
                $categoryId = DB::table('categories')->insertGetId([
                    'assessment_id' => $id,
                    'title' => $cat['title'],
                    'time_limit' => $cat['time_limit'], // Timer in minutes
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Insert Questions for this Category
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
                DB::table('questions')->insert($questionsToInsert);
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}