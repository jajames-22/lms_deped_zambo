<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Exception;

class ExamImport implements ToCollection, WithHeadingRow
{
    public $assessmentId;

    public function __construct($assessmentId)
    {
        $this->assessmentId = $assessmentId;
    }

    public function collection(Collection $rows)
    {
        // Use a transaction matching your controller's logic
        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                // Skip rows where the question is blank
                if (empty($row['question'])) {
                    continue;
                }

                // 1. Process Category (Maps to `assessment_categories`)
                $categoryTitle = $row['category'] ?? 'Imported Section';

                $category = DB::table('assessment_categories')
                    ->where('assessment_id', $this->assessmentId)
                    ->where('title', $categoryTitle)
                    ->first();

                if (!$category) {
                    $categoryId = DB::table('assessment_categories')->insertGetId([
                        'assessment_id' => $this->assessmentId,
                        'title' => $categoryTitle,
                        'time_limit' => $row['time_limit'] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $categoryId = $category->id;
                }

                // 2. Process Question (Maps to `assessment_questions`)
                $questionId = DB::table('assessment_questions')->insertGetId([
                    'category_id' => $categoryId,
                    'type' => strtolower(trim($row['type'] ?? 'mcq')),
                    'question_text' => $row['question'],
                    'media_url' => null, // Null by default for Excel uploads
                    'is_case_sensitive' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 3. Process Options (Maps to `assessment_options`)
                for ($i = 1; $i <= 4; $i++) {
                    $optionColumnName = 'option_' . $i;

                    // Skip if this specific option column is empty (e.g., for True/False)
                    if (!isset($row[$optionColumnName]) || trim($row[$optionColumnName]) === '') {
                        continue;
                    }

                    $correctAnswerTarget = strtolower(trim($row['correct_answer'] ?? ''));
                    $isCorrect = ($correctAnswerTarget === 'option ' . $i);

                    DB::table('assessment_options')->insert([
                        'question_id' => $questionId,
                        'option_text' => trim($row[$optionColumnName]),
                        'is_correct' => $isCorrect,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e; // Rethrow so your AssessmentController can catch it and return the error message
        }
    }
}