<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Exception;

class LessonImport implements ToCollection, WithHeadingRow
{
    public $materialId;

    public function __construct($materialId)
    {
        $this->materialId = $materialId;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                // 1. Extract Question/Content Text
                $contentText = $row['content_text'] ?? $row['question'] ?? null;
                
                // Skip blank rows
                if (empty($contentText)) {
                    continue; 
                }

                // 2. Identify Section Title (Ultra-forgiving keys)
                $sectionTitle = $row['section_title'] ?? $row['lesson_title'] ?? $row['category'] ?? 'Imported Section';
                
                // 3. SMART SECTION MATCHING (Lesson vs Exam)
                $rawSectionType = strtolower(trim($row['section_type'] ?? $row['sectiontype'] ?? $row['section'] ?? ''));
                $rawItemType = strtolower(trim($row['type'] ?? $row['item_type'] ?? ''));
                $rawSectionTitle = strtolower(trim($sectionTitle));

                $sectionType = 'lesson'; // Default

                // If "exam" is typed in the section_type column, the type column, or the Title itself
                if (str_contains($rawSectionType, 'exam') || str_contains($rawItemType, 'exam') || str_contains($rawSectionTitle, 'exam')) {
                    $sectionType = 'exam';
                }

                // 4. Create or Update the Section Container
                $lesson = DB::table('lessons')
                    ->where('material_id', $this->materialId)
                    ->where('title', $sectionTitle)
                    ->first();

                if (!$lesson) {
                    $lessonId = DB::table('lessons')->insertGetId([
                        'material_id' => $this->materialId,
                        'section_type' => $sectionType, // Save as Red Exam or Blue Lesson
                        'title' => $sectionTitle,
                        'time_limit' => $row['time_limit'] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $lessonId = $lesson->id;
                    // FORCE OVERRIDE: If the Excel explicitly says 'exam', upgrade the existing section to an exam
                    if ($sectionType === 'exam' && $lesson->section_type !== 'exam') {
                        DB::table('lessons')->where('id', $lessonId)->update(['section_type' => 'exam']);
                    } else {
                        $sectionType = $lesson->section_type; 
                    }
                }

                // 5. SMART ITEM MATCHING (Content vs MCQ vs True/False)
                $itemType = 'content';
                if (in_array($rawItemType, ['mcq', 'true_false', 'checkbox', 'text'])) {
                    $itemType = $rawItemType;
                } elseif (str_contains($rawItemType, 'quiz') || str_contains($rawItemType, 'exam')) {
                    // Fallback: If they accidentally typed "quiz" or "exam" as the question type, assume it's multiple choice
                    $itemType = 'mcq'; 
                }

                // CRITICAL: Exams cannot have "Lesson Content" reading blocks. Force it to be a short text question.
                if ($sectionType === 'exam' && $itemType === 'content') {
                    $itemType = 'text'; 
                }
                
                // 6. Extract Media
                $mediaUrl = null;
                if (!empty($row['media_url']) && trim($row['media_url']) !== '') {
                    $mediaUrl = trim($row['media_url']);
                } elseif (!empty($row['image_url']) && trim($row['image_url']) !== '') {
                    $mediaUrl = trim($row['image_url']);
                }

                // 7. Insert the Question/Content Block
                $quizId = DB::table('quizzes')->insertGetId([
                    'lesson_id' => $lessonId,
                    'type' => $itemType,
                    'question_text' => $contentText,
                    'media_url' => $mediaUrl,
                    'is_case_sensitive' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 8. Process Quiz Options (Skip if it's just reading material)
                if ($itemType !== 'content') {
                    if ($itemType === 'true_false') {
                        $correctAnswer = strtolower(trim($row['correct_answer'] ?? ''));
                        DB::table('quiz_options')->insert([
                            ['quiz_id' => $quizId, 'option_text' => 'True', 'is_correct' => ($correctAnswer === 'true' || $correctAnswer === 'option 1'), 'created_at' => now(), 'updated_at' => now()],
                            ['quiz_id' => $quizId, 'option_text' => 'False', 'is_correct' => ($correctAnswer === 'false' || $correctAnswer === 'option 2'), 'created_at' => now(), 'updated_at' => now()]
                        ]);
                    } else {
                        // Standard Options (MCQ, Checkbox)
                        for ($i = 1; $i <= 4; $i++) {
                            $optionColumnName = 'option_' . $i;
                            if (!isset($row[$optionColumnName]) || trim($row[$optionColumnName]) === '') continue;

                            $correctAnswerTarget = strtolower(trim($row['correct_answer'] ?? ''));
                            $isCorrect = ($correctAnswerTarget === 'option ' . $i);

                            DB::table('quiz_options')->insert([
                                'quiz_id' => $quizId,
                                'option_text' => trim($row[$optionColumnName]),
                                'is_correct' => $isCorrect,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
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