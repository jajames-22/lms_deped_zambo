<?php

namespace App\Services;

use App\Models\Material;
use App\Models\MaterialAccess;
use App\Models\Enrollment;
use App\Models\QuizAnswer;
use App\Models\ExamAnswer;
use App\Models\QuizOption;
use App\Models\ExamOption;
use App\Models\LessonContent;
use App\Models\Exam;
use App\Models\Lesson;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

class MaterialExportService
{
    private array $quizTypes = ['mcq', 'checkbox', 'true_false', 'text'];

    // ─────────────────────────────────────────────────────────────
    //  PUBLIC ENTRY POINTS
    // ─────────────────────────────────────────────────────────────

    public function exportSummaryAsCsv(Material $material): array
    {
        [$accesses, $enrollments, $hasQuizzes, $hasExams] = $this->loadBaseData($material);

        $headers = ['Name', 'Email', 'Status', 'Progress (%)'];
        if ($hasQuizzes) $headers[] = 'Quiz Score';
        if ($hasExams)   $headers[] = 'Exam Score';
        if ($hasQuizzes || $hasExams) $headers[] = 'Final Grade (%)';

        $rows = [];
        foreach ($accesses as $access) {
            $student    = $access->student;
            $enrollment = $student ? $enrollments->get($student->id) : null;
            $progress   = $this->getProgress($enrollment, $material->id);

            $row = [
                $student ? trim($student->first_name . ' ' . $student->last_name) : 'N/A',
                $access->email,
                $access->status,
                $progress,
            ];

            if ($hasQuizzes) $row[] = $student ? $this->getQuizScore($student->id, $material->id) : 'N/A';
            if ($hasExams)   $row[] = $student ? $this->getExamScore($student->id, $material->id) : 'N/A';
            if ($hasQuizzes || $hasExams) $row[] = $student ? $this->getFinalGrade($student->id, $material, $hasQuizzes, $hasExams) : 'N/A';

            $rows[] = $row;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    public function exportDetailedAsExcel(Material $material): Spreadsheet
    {
        [$accesses, $enrollments, $hasQuizzes, $hasExams] = $this->loadBaseData($material);

        // Build ordered question list once
        $questions = $this->buildQuestionList($material, $hasQuizzes, $hasExams);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setTitle($material->title . ' - Detailed Report');

        // ── Sheet 1: Student Summary ──────────────────────────────
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Student Summary');
        $headers = ['Name', 'Email', 'Status', 'Progress (%)'];
        if ($hasQuizzes) $headers[] = 'Quiz Score';
        if ($hasExams)   $headers[] = 'Exam Score';
        if ($hasQuizzes || $hasExams) $headers[] = 'Final Grade (%)';
        $this->styleSheetHeader($sheet1, $headers);

        $row = 2;
        foreach ($accesses as $access) {
            $student    = $access->student;
            $enrollment = $student ? $enrollments->get($student->id) : null;

            $rowData = [
                $student ? trim($student->first_name . ' ' . $student->last_name) : 'N/A',
                $access->email,
                $access->status,
                $this->getProgress($enrollment, $material->id),
            ];
            if ($hasQuizzes) $rowData[] = $student ? $this->getQuizScore($student->id, $material->id) : 'N/A';
            if ($hasExams)   $rowData[] = $student ? $this->getExamScore($student->id, $material->id) : 'N/A';
            if ($hasQuizzes || $hasExams) $rowData[] = $student ? $this->getFinalGrade($student->id, $material, $hasQuizzes, $hasExams) : 'N/A';

            $sheet1->fromArray($rowData, null, 'A' . $row);
            $row++;
        }
        $this->autoWidth($sheet1, 6);

        // ── Sheet 2: Student Answers ──────────────────────────────
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Student Answers');

        // Build headers: Name, Email, Q1 Answer, Q1 Result, Q2 Answer, Q2 Result …
        $answerHeaders = ['Name', 'Email'];
        foreach ($questions as $i => $q) {
            $answerHeaders[] = 'Q' . ($i + 1) . ' Answer';
            $answerHeaders[] = 'Q' . ($i + 1) . ' Result';
        }
        $this->styleSheetHeader($sheet2, $answerHeaders);

        // Pre-load ALL quiz + exam answers in two bulk queries
        $studentIds = $accesses->filter(fn($a) => $a->student)->map(fn($a) => $a->student->id)->unique()->toArray();
        $quizAnswersAll  = $this->bulkLoadQuizAnswers($studentIds);
        $examAnswersAll  = $this->bulkLoadExamAnswers($studentIds);
        $quizOptionsCache = [];
        $examOptionsCache = [];

        $row = 2;
        foreach ($accesses as $access) {
            $student = $access->student;
            $dataRow = [
                $student ? trim($student->first_name . ' ' . $student->last_name) : 'N/A',
                $access->email,
            ];

            foreach ($questions as $q) {
                if ($student) {
                    if ($q['is_exam']) {
                        $ans = $examAnswersAll[$student->id][$q['id']] ?? null;
                        [$ansText, $result] = $this->resolveExamAnswer($ans, $q, $examOptionsCache);
                    } else {
                        $ans = $quizAnswersAll[$student->id][$q['id']] ?? null;
                        [$ansText, $result] = $this->resolveQuizAnswer($ans, $q, $quizOptionsCache);
                    }
                } else {
                    $ansText = 'N/A';
                    $result  = 'N/A';
                }
                $dataRow[] = $ansText;
                $dataRow[] = $result;
            }

            $sheet2->fromArray($dataRow, null, 'A' . $row);

            // Colour result cells
            $colOffset = 3; // Answer starts at col C (index 3)
            foreach ($questions as $i => $q) {
                $resultColIndex = $colOffset + ($i * 2) + 1; // result col (1-based)
                $resultCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($resultColIndex);
                $resultCell = $sheet2->getCell($resultCol . $row);
                $val = $resultCell->getValue();
                if ($val === 'Correct') {
                    $resultCell->getStyle()->getFont()->getColor()->setARGB('FF16a34a');
                } elseif ($val === 'Incorrect') {
                    $resultCell->getStyle()->getFont()->getColor()->setARGB('FFdc2626');
                }
            }
            $row++;
        }
        $this->autoWidth($sheet2, count($answerHeaders));

        // ── Sheet 3: Question Metadata ────────────────────────────
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Question Metadata');
        $this->styleSheetHeader($sheet3, ['#', 'Category', 'Question', 'Correct Answer']);

        $row = 2;
        foreach ($questions as $i => $q) {
            // Resolve correct answer text
            if ($q['is_exam']) {
                $correctText = ExamOption::where('exam_id', $q['id'])->where('is_correct', true)
                    ->pluck('option_text')->map(fn($o) => strip_tags($o))->implode(' / ');
            } else {
                $correctText = QuizOption::where('quiz_id', $q['id'])->where('is_correct', true)
                    ->pluck('option_text')->map(fn($o) => strip_tags($o))->implode(' / ');
            }

            $sheet3->fromArray([
                $i + 1,
                $q['category'],
                $q['question_text'],
                $correctText ?: 'Manual grading',
            ], null, 'A' . $row);
            $row++;
        }
        $this->autoWidth($sheet3, 4);
        $sheet3->getColumnDimension('C')->setWidth(60);

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    // ─────────────────────────────────────────────────────────────
    //  DATA LOADERS
    // ─────────────────────────────────────────────────────────────

    private function loadBaseData(Material $material): array
    {
        $accesses = MaterialAccess::with('student')->where('material_id', $material->id)->get();
        $enrollments = Enrollment::where('material_id', $material->id)->get()->keyBy('user_id');

        $hasExams = DB::table('exams')->where('material_id', $material->id)->exists();
        $hasQuizzes = DB::table('lesson_contents')
            ->join('lessons', 'lessons.id', '=', 'lesson_contents.lesson_id')
            ->where('lessons.material_id', $material->id)
            ->whereIn('lesson_contents.type', $this->quizTypes)
            ->exists();

        return [$accesses, $enrollments, $hasQuizzes, $hasExams];
    }

    private function buildQuestionList(Material $material, bool $hasQuizzes, bool $hasExams): array
    {
        $questions = [];

        if ($hasQuizzes) {
            $lessons = Lesson::where('material_id', $material->id)->orderBy('sort_order')->get();
            foreach ($lessons as $lesson) {
                $quizItems = LessonContent::where('lesson_id', $lesson->id)
                    ->whereIn('type', $this->quizTypes)
                    ->orderBy('sort_order')
                    ->get();
                foreach ($quizItems as $q) {
                    $questions[] = [
                        'id'            => $q->id,
                        'is_exam'       => false,
                        'type'          => $q->type,
                        'category'      => $lesson->title,
                        'question_text' => strip_tags($q->question_text),
                    ];
                }
            }
        }

        if ($hasExams) {
            $exams = Exam::where('material_id', $material->id)->orderBy('sort_order')->get();
            foreach ($exams as $e) {
                $questions[] = [
                    'id'            => $e->id,
                    'is_exam'       => true,
                    'type'          => $e->type,
                    'category'      => 'Final Exam',
                    'question_text' => strip_tags($e->question_text),
                ];
            }
        }

        return $questions;
    }

    private function bulkLoadQuizAnswers(array $studentIds): array
    {
        // [student_id][lesson_content_id] => answer
        $map = [];
        $rows = QuizAnswer::whereIn('user_id', $studentIds)->get();
        foreach ($rows as $r) {
            $map[$r->user_id][$r->lesson_content_id] = $r;
        }
        return $map;
    }

    private function bulkLoadExamAnswers(array $studentIds): array
    {
        $map = [];
        $rows = ExamAnswer::whereIn('user_id', $studentIds)->get();
        foreach ($rows as $r) {
            $map[$r->user_id][$r->exam_id] = $r;
        }
        return $map;
    }

    // ─────────────────────────────────────────────────────────────
    //  SCORING HELPERS
    // ─────────────────────────────────────────────────────────────

    private function getQuizScore(int $userId, int $materialId): string
    {
        $lessonIds = Lesson::where('material_id', $materialId)->pluck('id');
        $questionIds = LessonContent::whereIn('lesson_id', $lessonIds)
            ->whereIn('type', $this->quizTypes)->pluck('id');

        $total   = $questionIds->count();
        $correct = QuizAnswer::whereIn('lesson_content_id', $questionIds)
            ->where('user_id', $userId)->where('is_correct', true)->count();

        return $total > 0 ? "{$correct} out of {$total}" : 'N/A';
    }

    private function getExamScore(int $userId, int $materialId): string
    {
        $examIds = Exam::where('material_id', $materialId)->pluck('id');
        $total   = $examIds->count();
        $correct = ExamAnswer::whereIn('exam_id', $examIds)
            ->where('user_id', $userId)->where('is_correct', true)->count();

        return $total > 0 ? "{$correct} out of {$total}" : 'N/A';
    }

    private function getFinalGrade(int $userId, Material $material, bool $hasQuizzes, bool $hasExams): string
    {
        if (!$hasQuizzes && !$hasExams) return 'N/A';
        
        $quizWeight = $material->quiz_weight ?? 40;
        $examWeight = $material->exam_weight ?? 60;

        $qPct = 100;
        if ($hasQuizzes) {
            $lessonIds = Lesson::where('material_id', $material->id)->pluck('id');
            $questionIds = LessonContent::whereIn('lesson_id', $lessonIds)
                ->whereIn('type', $this->quizTypes)->pluck('id');
            $qTotal   = $questionIds->count();
            $qCorrect = QuizAnswer::whereIn('lesson_content_id', $questionIds)
                ->where('user_id', $userId)->where('is_correct', true)->count();
            $qPct = $qTotal > 0 ? ($qCorrect / $qTotal) * 100 : 100;
        }

        $ePct = 100;
        if ($hasExams) {
            $examIds = Exam::where('material_id', $material->id)->pluck('id');
            $eTotal   = $examIds->count();
            $eCorrect = ExamAnswer::whereIn('exam_id', $examIds)
                ->where('user_id', $userId)->where('is_correct', true)->count();
            $ePct = $eTotal > 0 ? ($eCorrect / $eTotal) * 100 : 100;
        }

        $finalPercentage = ($qPct * ($quizWeight / 100)) + ($ePct * ($examWeight / 100));
        return (string) round($finalPercentage);
    }

    private function getProgress(?Enrollment $enrollment, int $materialId): int
    {
        if (!$enrollment) return 0;
        if (in_array($enrollment->status, ['completed', 'read']) || !is_null($enrollment->completed_at)) return 100;

        $data = is_string($enrollment->progress_data) ? json_decode($enrollment->progress_data) : $enrollment->progress_data;
        if (!$data) return 0;
        
        $highestUnlocked = isset($data->highest_unlocked) ? (int) $data->highest_unlocked : 0;
        $currentContent = isset($data->content) ? (int) $data->content : 0;
        $currentLesson = isset($data->lesson) ? (int) $data->lesson : 0;

        $timeline = collect();
        $lessons = Lesson::where('material_id', $materialId)->get();
        foreach ($lessons as $lesson) {
            $count = LessonContent::where('lesson_id', $lesson->id)->count();
            $timeline->push((object)[
                'items_count' => $count,
                'timestamp' => $lesson->created_at ? \Carbon\Carbon::parse($lesson->created_at)->timestamp : 0
            ]);
        }
        if (Exam::where('material_id', $materialId)->exists()) {
            $examCount = Exam::where('material_id', $materialId)->count();
            $firstExam = Exam::where('material_id', $materialId)->first();
            $timeline->push((object)[
                'items_count' => $examCount,
                'timestamp' => $firstExam && $firstExam->created_at ? \Carbon\Carbon::parse($firstExam->created_at)->timestamp : 0
            ]);
        }
        $timeline = $timeline->sortBy('timestamp')->values();
        $totalContents = $timeline->sum('items_count');

        if ($totalContents === 0) return 0;

        $contentsPassed = 0;
        for ($i = 0; $i < $highestUnlocked; $i++) {
            if (isset($timeline[$i])) {
                $contentsPassed += $timeline[$i]->items_count;
            }
        }

        if ($currentLesson === $highestUnlocked) {
            $contentsPassed += $currentContent;
        }

        $percentage = round(($contentsPassed / $totalContents) * 100);
        return $percentage > 100 ? 100 : $percentage;
    }

    // ─────────────────────────────────────────────────────────────
    //  ANSWER RESOLVERS
    // ─────────────────────────────────────────────────────────────

    private function resolveQuizAnswer(?object $ans, array $question, array &$cache): array
    {
        if (!$ans) return ['Not Answered', 'Not Answered'];

        $text = $ans->text_answer;
        if ($text && $question['type'] === 'checkbox') {
            $selectedIds = array_filter(array_map('trim', explode(',', $text)));
            $opts = QuizOption::whereIn('id', $selectedIds)->pluck('option_text')->map(fn($o) => strip_tags($o))->implode(', ');
            $text = $opts ?: $text;
        } elseif (!$text && $ans->quiz_option_id) {
            if (!isset($cache[$ans->quiz_option_id])) {
                $opt = QuizOption::find($ans->quiz_option_id);
                $cache[$ans->quiz_option_id] = $opt ? strip_tags($opt->option_text) : 'Selected Option';
            }
            $text = $cache[$ans->quiz_option_id];
        }

        $result = $ans->is_correct ? 'Correct' : 'Incorrect';
        return [$text ?: 'Answered', $result];
    }

    private function resolveExamAnswer(?object $ans, array $question, array &$cache): array
    {
        if (!$ans) return ['Not Answered', 'Not Answered'];

        $text = $ans->text_answer;
        if ($text && $question['type'] === 'checkbox') {
            $selectedIds = array_filter(array_map('trim', explode(',', $text)));
            $opts = ExamOption::whereIn('id', $selectedIds)->pluck('option_text')->map(fn($o) => strip_tags($o))->implode(', ');
            $text = $opts ?: $text;
        } elseif (!$text && $ans->exam_option_id) {
            if (!isset($cache[$ans->exam_option_id])) {
                $opt = ExamOption::find($ans->exam_option_id);
                $cache[$ans->exam_option_id] = $opt ? strip_tags($opt->option_text) : 'Selected Option';
            }
            $text = $cache[$ans->exam_option_id];
        }

        $result = $ans->is_correct ? 'Correct' : 'Incorrect';
        return [$text ?: 'Answered', $result];
    }

    // ─────────────────────────────────────────────────────────────
    //  SPREADSHEET HELPERS
    // ─────────────────────────────────────────────────────────────

    private function styleSheetHeader(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $headers): void
    {
        $sheet->fromArray($headers, null, 'A1');

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $range   = 'A1:' . $lastCol . '1';

        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFa52a2a'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(20);
    }

    private function autoWidth(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $colCount): void
    {
        for ($i = 1; $i <= $colCount; $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
