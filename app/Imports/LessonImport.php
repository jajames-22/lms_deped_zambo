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
                
                if (empty($contentText)) continue; 

                // 2. Identify Section Title
                $sectionTitle = $row['section_title'] ?? $row['lesson_title'] ?? $row['category'] ?? 'Imported Section';
                
                // 3. SMART SECTION MATCHING
                $rawSectionType = strtolower(trim($row['section_type'] ?? $row['sectiontype'] ?? $row['section'] ?? ''));
                $rawItemType = strtolower(trim($row['type'] ?? $row['item_type'] ?? ''));
                $rawSectionTitle = strtolower(trim($sectionTitle));

                $sectionType = 'lesson'; 
                if (str_contains($rawSectionType, 'exam') || str_contains($rawItemType, 'exam') || str_contains($rawSectionTitle, 'exam')) {
                    $sectionType = 'exam';
                }

                // 4. SMART ITEM MATCHING
                $itemType = 'content';
                if (in_array($rawItemType, ['mcq', 'true_false', 'checkbox', 'text'])) {
                    $itemType = $rawItemType;
                } elseif (str_contains($rawItemType, 'quiz') || str_contains($rawItemType, 'exam')) {
                    $itemType = 'mcq'; 
                }

                if ($sectionType === 'exam' && $itemType === 'content') {
                    $itemType = 'text'; 
                }
                
                // 5. Extract Media
                $mediaUrl = null;
                if (!empty($row['media_url'])) {
                    $mediaUrl = trim($row['media_url']);
                } elseif (!empty($row['image_url'])) {
                    $mediaUrl = trim($row['image_url']);
                }

                // --------------------------------------------------------
                // BRANCH A: EXAM IMPORT LOGIC (Upsert based on Question Text)
                // --------------------------------------------------------
                if ($sectionType === 'exam') {
                    // Check if this question already exists in this material's exam
                    $existingExam = DB::table('exams')
                        ->where('material_id', $this->materialId)
                        ->where('question_text', $contentText)
                        ->first();

                    $examData = [
                        'material_id' => $this->materialId,
                        'type' => $itemType,
                        'question_text' => $contentText,
                        'media_url' => $mediaUrl,
                        'is_case_sensitive' => false,
                        'updated_at' => now(),
                    ];

                    if ($existingExam) {
                        DB::table('exams')->where('id', $existingExam->id)->update($examData);
                        $examId = $existingExam->id;
                        // Clear old options to refresh them from the new file
                        DB::table('exam_options')->where('exam_id', $examId)->delete();
                    } else {
                        $examData['created_at'] = now();
                        $examId = DB::table('exams')->insertGetId($examData);
                    }

                    if ($itemType !== 'content') {
                        $this->insertExamOptions($examId, $itemType, $row);
                    }
                } 
                // --------------------------------------------------------
                // BRANCH B: STANDARD LESSON IMPORT LOGIC (Upsert based on Title & Question Text)
                // --------------------------------------------------------
                else {
                    $lesson = DB::table('lessons')
                        ->where('material_id', $this->materialId)
                        ->where('title', $sectionTitle)
                        ->first();

                    if (!$lesson) {
                        $lessonId = DB::table('lessons')->insertGetId([
                            'material_id' => $this->materialId,
                            'section_type' => 'lesson',
                            'title' => $sectionTitle,
                            'time_limit' => $row['time_limit'] ?? 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $lessonId = $lesson->id;
                    }

                    // Check if this content/quiz already exists in this lesson
                    $existingContent = DB::table('lesson_contents')
                        ->where('lesson_id', $lessonId)
                        ->where('question_text', $contentText)
                        ->first();

                    $contentData = [
                        'lesson_id' => $lessonId,
                        'type' => $itemType,
                        'question_text' => $contentText,
                        'media_url' => $mediaUrl,
                        'is_case_sensitive' => false,
                        'updated_at' => now(),
                    ];

                    if ($existingContent) {
                        DB::table('lesson_contents')->where('id', $existingContent->id)->update($contentData);
                        $quizId = $existingContent->id;
                        // Clear old options to refresh
                        DB::table('quiz_options')->where('quiz_id', $quizId)->delete();
                    } else {
                        $contentData['created_at'] = now();
                        $quizId = DB::table('lesson_contents')->insertGetId($contentData);
                    }

                    if ($itemType !== 'content') {
                        $this->insertQuizOptions($quizId, $itemType, $row);
                    }
                }
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e; 
        }
    }

    /**
     * Helper to handle Exam Options
     */
    private function insertExamOptions($examId, $itemType, $row)
    {
        if ($itemType === 'true_false') {
            $correctAnswer = strtolower(trim($row['correct_answer'] ?? ''));
            DB::table('exam_options')->insert([
                ['exam_id' => $examId, 'option_text' => 'True', 'is_correct' => ($correctAnswer === 'true' || $correctAnswer === 'option 1'), 'created_at' => now(), 'updated_at' => now()],
                ['exam_id' => $examId, 'option_text' => 'False', 'is_correct' => ($correctAnswer === 'false' || $correctAnswer === 'option 2'), 'created_at' => now(), 'updated_at' => now()]
            ]);
        } else {
            for ($i = 1; $i <= 4; $i++) {
                $col = 'option_' . $i;
                if (!isset($row[$col]) || trim($row[$col]) === '') continue;
                DB::table('exam_options')->insert([
                    'exam_id' => $examId,
                    'option_text' => trim($row[$col]),
                    'is_correct' => (strtolower(trim($row['correct_answer'] ?? '')) === 'option ' . $i),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Helper to handle Quiz Options
     */
    private function insertQuizOptions($quizId, $itemType, $row)
    {
        if ($itemType === 'true_false') {
            $correctAnswer = strtolower(trim($row['correct_answer'] ?? ''));
            DB::table('quiz_options')->insert([
                ['quiz_id' => $quizId, 'option_text' => 'True', 'is_correct' => ($correctAnswer === 'true' || $correctAnswer === 'option 1'), 'created_at' => now(), 'updated_at' => now()],
                ['quiz_id' => $quizId, 'option_text' => 'False', 'is_correct' => ($correctAnswer === 'false' || $correctAnswer === 'option 2'), 'created_at' => now(), 'updated_at' => now()]
            ]);
        } else {
            for ($i = 1; $i <= 4; $i++) {
                $col = 'option_' . $i;
                if (!isset($row[$col]) || trim($row[$col]) === '') continue;
                DB::table('quiz_options')->insert([
                    'quiz_id' => $quizId,
                    'option_text' => trim($row[$col]),
                    'is_correct' => (strtolower(trim($row['correct_answer'] ?? '')) === 'option ' . $i),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }
}