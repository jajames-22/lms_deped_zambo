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
        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                // Skip rows where the question text is missing
                if (empty($row['question'])) {
                    continue;
                }

                // 1. UPSERT CATEGORY
                $categoryTitle = $row['category'] ?? 'Imported Section';

                $category = DB::table('assessment_categories')
                    ->where('assessment_id', $this->assessmentId)
                    ->where('title', $categoryTitle)
                    ->first();

                $categoryData = [
                    'assessment_id' => $this->assessmentId,
                    'title' => $categoryTitle,
                    'time_limit' => $row['time_limit'] ?? 0,
                    'updated_at' => now(),
                ];

                if (!$category) {
                    $categoryData['created_at'] = now();
                    $categoryId = DB::table('assessment_categories')->insertGetId($categoryData);
                } else {
                    DB::table('assessment_categories')->where('id', $category->id)->update($categoryData);
                    $categoryId = $category->id;
                }

                // 2. UPSERT QUESTION
                
                $questionType = strtolower(trim($row['type'] ?? 'mcq'));
                $mediaUrl = !empty($row['image_url']) ? trim($row['image_url']) : (!empty($row['media_url']) ? trim($row['media_url']) : null);

                // Check if this specific question already exists in this category
                $existingQuestion = DB::table('assessment_questions')
                    ->where('category_id', $categoryId)
                    ->where('question_text', $row['question'])
                    ->first();

                $questionData = [
                    'category_id' => $categoryId,
                    'type' => $questionType,
                    'question_text' => $row['question'],
                    'media_url' => $mediaUrl,
                    'is_case_sensitive' => false,
                    'updated_at' => now(),
                ];

                if ($existingQuestion) {
                    // Update question metadata and refresh options
                    DB::table('assessment_questions')->where('id', $existingQuestion->id)->update($questionData);
                    $questionId = $existingQuestion->id;
                    DB::table('assessment_options')->where('question_id', $questionId)->delete();
                } else {
                    // Insert brand new question
                    $questionData['created_at'] = now();
                    $questionId = DB::table('assessment_questions')->insertGetId($questionData);
                }

                // 3. REFRESH OPTIONS
                if ($questionType === 'true_false') {
                    $correctAnswer = strtolower(trim($row['correct_answer'] ?? ''));

                    DB::table('assessment_options')->insert([
                        ['question_id' => $questionId, 'option_text' => 'True', 'is_correct' => ($correctAnswer === 'true' || $correctAnswer === 'option 1'), 'created_at' => now(), 'updated_at' => now()],
                        ['question_id' => $questionId, 'option_text' => 'False', 'is_correct' => ($correctAnswer === 'false' || $correctAnswer === 'option 2'), 'created_at' => now(), 'updated_at' => now()]
                    ]);
                } else {
                    for ($i = 1; $i <= 4; $i++) {
                        $col = 'option_' . $i;
                        if (!isset($row[$col]) || trim($row[$col]) === '')
                            continue;

                        DB::table('assessment_options')->insert([
                            'question_id' => $questionId,
                            'option_text' => trim($row[$col]),
                            'is_correct' => (strtolower(trim($row['correct_answer'] ?? '')) === 'option ' . $i),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}