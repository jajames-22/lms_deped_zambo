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

                // CRITICAL: Exams cannot have "Lesson Content" reading blocks. Force it to be a short text question.
                if ($sectionType === 'exam' && $itemType === 'content') {
                    $itemType = 'text'; 
                }
                
                // 5. Extract Media
                $mediaUrl = null;
                if (!empty($row['media_url']) && trim($row['media_url']) !== '') {
                    $mediaUrl = trim($row['media_url']);
                } elseif (!empty($row['image_url']) && trim($row['image_url']) !== '') {
                    $mediaUrl = trim($row['image_url']);
                }

                // --------------------------------------------------------
                // BRANCH A: EXAM IMPORT LOGIC
                // --------------------------------------------------------
                if ($sectionType === 'exam') {
                    $examId = DB::table('exams')->insertGetId([
                        'material_id' => $this->materialId,
                        'type' => $itemType,
                        'question_text' => $contentText,
                        'media_url' => $mediaUrl,
                        'is_case_sensitive' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if ($itemType !== 'content') {
                        if ($itemType === 'true_false') {
                            $correctAnswer = strtolower(trim($row['correct_answer'] ?? ''));
                            DB::table('exam_options')->insert([
                                ['exam_id' => $examId, 'option_text' => 'True', 'is_correct' => ($correctAnswer === 'true' || $correctAnswer === 'option 1'), 'created_at' => now(), 'updated_at' => now()],
                                ['exam_id' => $examId, 'option_text' => 'False', 'is_correct' => ($correctAnswer === 'false' || $correctAnswer === 'option 2'), 'created_at' => now(), 'updated_at' => now()]
                            ]);
                        } else {
                            for ($i = 1; $i <= 4; $i++) {
                                $optionColumnName = 'option_' . $i;
                                if (!isset($row[$optionColumnName]) || trim($row[$optionColumnName]) === '') continue;

                                $isCorrect = (strtolower(trim($row['correct_answer'] ?? '')) === 'option ' . $i);

                                DB::table('exam_options')->insert([
                                    'exam_id' => $examId,
                                    'option_text' => trim($row[$optionColumnName]),
                                    'is_correct' => $isCorrect,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                } 
                // --------------------------------------------------------
                // BRANCH B: STANDARD LESSON IMPORT LOGIC
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

                    // FIX: Replaced outdated 'quizzes' table with 'lesson_contents'
                    $quizId = DB::table('lesson_contents')->insertGetId([
                        'lesson_id' => $lessonId,
                        'type' => $itemType,
                        'question_text' => $contentText,
                        'media_url' => $mediaUrl,
                        'is_case_sensitive' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if ($itemType !== 'content') {
                        if ($itemType === 'true_false') {
                            $correctAnswer = strtolower(trim($row['correct_answer'] ?? ''));
                            DB::table('quiz_options')->insert([
                                ['quiz_id' => $quizId, 'option_text' => 'True', 'is_correct' => ($correctAnswer === 'true' || $correctAnswer === 'option 1'), 'created_at' => now(), 'updated_at' => now()],
                                ['quiz_id' => $quizId, 'option_text' => 'False', 'is_correct' => ($correctAnswer === 'false' || $correctAnswer === 'option 2'), 'created_at' => now(), 'updated_at' => now()]
                            ]);
                        } else {
                            for ($i = 1; $i <= 4; $i++) {
                                $optionColumnName = 'option_' . $i;
                                if (!isset($row[$optionColumnName]) || trim($row[$optionColumnName]) === '') continue;

                                $isCorrect = (strtolower(trim($row['correct_answer'] ?? '')) === 'option ' . $i);

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
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e; 
        }
    }
}