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
            // Track sort orders so imported items append neatly instead of scattering to 0
            $categorySortOrder = (DB::table('assessment_categories')->where('assessment_id', $this->assessmentId)->max('sort_order') ?? 0) + 1;
            $questionSortOrders = [];

            foreach ($rows as $row) {

                // --- FLEXIBLE QUESTION TEXT ---
                $questionText = $row['question'] ?? $row['content'] ?? null;
                if (empty($questionText)) continue;

                // --- CATEGORY ---
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
                    $categoryData['sort_order'] = $categorySortOrder++; // Assigned sort order
                    $categoryData['created_at'] = now();
                    $categoryId = DB::table('assessment_categories')->insertGetId($categoryData);
                } else {
                    DB::table('assessment_categories')->where('id', $category->id)->update($categoryData);
                    $categoryId = $category->id;
                }

                // --- SMART TYPE HANDLING ---
                $rawType = strtolower(trim($row['type'] ?? 'mcq'));

                $questionType = match (true) {
                    in_array($rawType, ['mcq', 'multiple choice']) => 'mcq',
                    in_array($rawType, ['true_false', 'true/false']) => 'true_false',
                    in_array($rawType, ['checkbox', 'multiple']) => 'checkbox',
                    in_array($rawType, ['text', 'essay', 'short answer']) => 'text',
                    in_array($rawType, ['content', 'instruction']) => 'instruction',
                    default => 'mcq',
                };

                // --- MEDIA & CASE SENSITIVITY ---
                $mediaUrl = $row['image_url'] ?? $row['media_url'] ?? null;
                $isCaseSensitive = filter_var($row['is_case_sensitive'] ?? false, FILTER_VALIDATE_BOOLEAN);

                // Track question sort order per category
                if (!isset($questionSortOrders[$categoryId])) {
                    $questionSortOrders[$categoryId] = (DB::table('assessment_questions')->where('category_id', $categoryId)->max('sort_order') ?? 0) + 1;
                }

                // --- UPSERT QUESTION ---
                $existingQuestion = DB::table('assessment_questions')
                    ->where('category_id', $categoryId)
                    ->where('question_text', $questionText)
                    ->first();

                $questionData = [
                    'category_id' => $categoryId,
                    'type' => $questionType,
                    'question_text' => $questionText,
                    'media_url' => $mediaUrl,
                    'is_case_sensitive' => $isCaseSensitive, // Safely imported case sensitivity
                    'updated_at' => now(),
                ];

                if ($existingQuestion) {
                    DB::table('assessment_questions')
                        ->where('id', $existingQuestion->id)
                        ->update($questionData);

                    $questionId = $existingQuestion->id;
                    DB::table('assessment_options')->where('question_id', $questionId)->delete(); // Clear old to refresh
                } else {
                    $questionData['sort_order'] = $questionSortOrders[$categoryId]++; // Assigned sort order
                    $questionData['created_at'] = now();
                    $questionId = DB::table('assessment_questions')->insertGetId($questionData);
                }

                // --- SKIP OPTIONS FOR INSTRUCTION ---
                if ($questionType === 'instruction') {
                    continue;
                }

                // --- TRUE/FALSE ---
                if ($questionType === 'true_false') {
                    $correct = strtolower(trim($row['correct_answer'] ?? ''));

                    DB::table('assessment_options')->insert([
                        [
                            'question_id' => $questionId,
                            'option_text' => 'True',
                            'is_correct' => in_array($correct, ['true', 'option 1', '1']),
                            'created_at' => now(),
                            'updated_at' => now()
                        ],
                        [
                            'question_id' => $questionId,
                            'option_text' => 'False',
                            'is_correct' => in_array($correct, ['false', 'option 2', '2']),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    ]);

                    continue;
                }

                // --- FLEXIBLE CORRECT ANSWER PARSING (for MCQ, Checkbox, Text) ---
                $rawCorrect = strtolower(trim($row['correct_answer'] ?? ''));
                $correctArray = array_map('trim', explode(',', $rawCorrect));

                // --- OPTIONS LOOP ---
                for ($i = 1; $i <= 4; $i++) {
                    $col = 'option_' . $i;
                    
                    // Safety check to bypass empty Excel cells
                    if (!isset($row[$col]) || trim($row[$col]) === '') continue;

                    $optStr = 'option ' . $i;
                    $optNum = (string)$i;

                    $isCorrect = false;

                    // FIX: Treat 'text' type like 'checkbox' so multiple inputs can be valid simultaneously
                    if (in_array($questionType, ['checkbox', 'text'])) {
                        if (in_array($optStr, $correctArray) || in_array($optNum, $correctArray)) {
                            $isCorrect = true;
                        }
                    } else {
                        // Standard Single MCQ
                        if ($rawCorrect === $optStr || $rawCorrect === $optNum) {
                            $isCorrect = true;
                        }
                    }

                    DB::table('assessment_options')->insert([
                        'question_id' => $questionId,
                        'option_text' => trim($row[$col]),
                        'is_correct' => $isCorrect,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}