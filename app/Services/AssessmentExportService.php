<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentAccess;
use App\Models\AssessmentCategory;
use App\Models\AssessmentSession;
use App\Models\StudentAnswer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class AssessmentExportService
{
    public function exportSummaryAsExcel(Assessment $assessment, ?string $search = null): Spreadsheet
    {
        $accesses = $this->loadFilteredAccesses($assessment->id, $search);
        [$totalQuestions, $qMap, $categories] = AssessmentService::getAssessmentGradingMap($assessment->id);

        $studentIds = $accesses->pluck('student.id')->filter()->unique()->toArray();
        $allSessions = AssessmentSession::where('assessment_id', $assessment->id)->whereIn('user_id', $studentIds)->get()->groupBy('user_id');
        $allAnswers = StudentAnswer::where('assessment_id', $assessment->id)->whereIn('user_id', $studentIds)->get()->groupBy('user_id');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setTitle($assessment->title . ' - Summary Report');

        // Sheet 1: Assessment Summary
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Assessment Summary');

        $this->buildStudentPerformanceSheet($sheet1, $assessment, $accesses, $qMap, $totalQuestions, $categories, $allSessions, $allAnswers);

        // Sheet 2: Proficiency Levels Guide
        $this->addProficiencyGuideSheet($spreadsheet);

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    public function exportDetailedAsExcel(Assessment $assessment, ?string $search = null): Spreadsheet
    {
        $accesses = $this->loadFilteredAccesses($assessment->id, $search);
        [$totalQuestions, $qMap, $categories] = AssessmentService::getAssessmentGradingMap($assessment->id);

        $studentIds = $accesses->pluck('student.id')->filter()->unique()->toArray();
        $allSessions = AssessmentSession::where('assessment_id', $assessment->id)->whereIn('user_id', $studentIds)->get()->groupBy('user_id');
        $allAnswers = StudentAnswer::where('assessment_id', $assessment->id)->whereIn('user_id', $studentIds)->get()->groupBy('user_id');

        // Ensure categories load their questions and options ordered
        $categories = AssessmentCategory::with(['questions' => fn($q) => $q->orderBy('sort_order'), 'questions.options'])
            ->where('assessment_id', $assessment->id)
            ->orderBy('sort_order')
            ->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setTitle($assessment->title . ' - Detailed Report');

        // Sheet 1: Assessment Detailed Report
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Assessment Detailed Report');

        $this->buildStudentPerformanceSheet($sheet1, $assessment, $accesses, $qMap, $totalQuestions, $categories, $allSessions, $allAnswers);

        $questionsList = collect();
        foreach ($categories as $cat) {
            foreach ($cat->questions as $question) {
                if ($question->type !== 'instruction') {
                    $questionsList->push([
                        'question' => $question,
                        'category' => $cat->title,
                    ]);
                }
            }
        }

        // Sheet 2: Student Answers
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Student Answers');

        $headers2 = ['Student Name', 'LRN'];
        foreach ($questionsList as $i => $qItem) {
            $headers2[] = 'Q' . ($i + 1) . ' Answer';
            $headers2[] = 'Q' . ($i + 1) . ' Result';
        }
        $this->styleSheetHeader($sheet2, $headers2);

        $sheet2Row = 2;
        foreach ($accesses as $access) {
            $student = $access->student;
            $studentId = $student ? $student->id : null;
            $studentName = $student ? trim($student->first_name . ' ' . $student->last_name) : 'Unregistered Student';
            $studentAnswersByKey = $studentId ? ($allAnswers->get($studentId) ?? collect())->keyBy('question_id') : collect();

            $dataRow = [$studentName, (string)$access->lrn];

            foreach ($questionsList as $qItem) {
                $question = $qItem['question'];
                if ($access->status === 'finished' && $studentId) {
                    $studentAnswer = $studentAnswersByKey->get($question->id);
                    [$ansText, $resultText] = $this->resolveQuestionAnswer($question, $studentAnswer);
                } else {
                    $ansText = 'N/A';
                    $resultText = 'N/A';
                }
                $dataRow[] = $ansText;
                $dataRow[] = $resultText;
            }

            $sheet2->fromArray($dataRow, null, 'A' . $sheet2Row);
            $sheet2->setCellValueExplicit('B' . $sheet2Row, (string)$access->lrn, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            // Color result cells
            $colOffset = 3; // Answer starts at col C (index 3)
            foreach ($questionsList as $i => $qItem) {
                $resultColIndex = $colOffset + ($i * 2) + 1; // result col (1-based)
                $resultCol = Coordinate::stringFromColumnIndex($resultColIndex);
                $resultCell = $sheet2->getCell($resultCol . $sheet2Row);
                $val = $resultCell->getValue();
                if ($val === 'Correct') {
                    $resultCell->getStyle()->getFont()->getColor()->setARGB('FF16a34a');
                } elseif ($val === 'Incorrect') {
                    $resultCell->getStyle()->getFont()->getColor()->setARGB('FFdc2626');
                } elseif ($val === 'Pending') {
                    $resultCell->getStyle()->getFont()->getColor()->setARGB('FFd97706');
                }
            }

            $sheet2Row++;
        }
        $this->autoWidth($sheet2, count($headers2));

        // Sheet 3: Question Metadata
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Question Metadata');
        $this->styleSheetHeader($sheet3, ['#', 'Category', 'Question', 'Correct Answer']);

        $sheet3Row = 2;
        foreach ($questionsList as $i => $qItem) {
            $question = $qItem['question'];
            $categoryTitle = $qItem['category'];

            $correctOptions = $question->options->where('is_correct', true);
            if ($question->type === 'text' && $correctOptions->isEmpty()) {
                $correctText = 'Manual instructor grading';
            } else {
                $correctText = $correctOptions->pluck('option_text')->map(fn($o) => strip_tags($o))->implode(' / ');
            }

            $sheet3->fromArray([
                $i + 1,
                $categoryTitle,
                strip_tags($question->question_text),
                $correctText ?: 'Manual grading',
            ], null, 'A' . $sheet3Row);
            $sheet3Row++;
        }
        $this->autoWidth($sheet3, 4);
        $sheet3->getColumnDimension('C')->setWidth(60);

        // Sheet 4: Proficiency Levels Guide
        $this->addProficiencyGuideSheet($spreadsheet);

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    private function loadFilteredAccesses(int $assessmentId, ?string $search = null)
    {
        $query = AssessmentAccess::with(['student.school'])->where('assessment_id', $assessmentId);
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('lrn', 'like', "%{$search}%")
                  ->orWhereHas('student', function($sq) use ($search) {
                      $sq->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
                  });
            });
        }
        return $query->latest()->get();
    }

    private function addProficiencyGuideSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Proficiency Levels Guide');

        $headers = ['MPS Range', 'Proficiency Level'];
        $this->styleSheetHeader($sheet, $headers);

        $guideData = [
            ['90% - 100%', 'Highly Proficient'],
            ['75% - 89%', 'Proficient'],
            ['50% - 74%', 'Nearly Proficient'],
            ['25% - 49%', 'Low Proficient'],
            ['0% - 24%', 'Not Proficient'],
        ];

        $row = 2;
        foreach ($guideData as $data) {
            $sheet->fromArray($data, null, 'A' . $row);
            $row++;
        }

        $row += 2;
        $sheet->setCellValue('A' . $row, 'MPS Formula');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
        $row++;

        $sheet->setCellValue('A' . $row, 'MPS = (Student Score ÷ Total Possible Score) × 100');
        $row += 2;

        $sheet->setCellValue('A' . $row, 'Example:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue('A' . $row, 'Student Score = 42');
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Items = 60');
        $row += 2;

        $sheet->setCellValue('A' . $row, 'MPS = (42 ÷ 60) × 100');
        $row++;
        $sheet->setCellValue('A' . $row, 'MPS = 70%');
        $row += 2;

        $sheet->setCellValue('A' . $row, 'Proficiency Level = Nearly Proficient');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);

        $this->autoWidth($sheet, 2);
    }

    private function buildStudentPerformanceSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, Assessment $assessment, $accesses, array $qMap, int $totalQuestions, $categories, $allSessions, $allAnswers): void
    {
        $headers = [
            'Student Name', 'LRN', 'School', 'Grade Level', 'Date Started', 'Date Submitted'
        ];
        foreach ($categories as $index => $cat) {
            $catNum = $index + 1;
            $headers[] = 'Category ' . $catNum;
            $headers[] = 'Score';
        }
        $headers = array_merge($headers, [
            'Total Score', 'Total Items', 'MPS', 'Proficiency Level'
        ]);
        $this->styleSheetHeader($sheet, $headers);

        $row = 2;
        foreach ($accesses as $access) {
            $student = $access->student;
            $studentId = $student ? $student->id : null;
            $schoolName = ($student && $student->school) ? $student->school->name : 'Independent / Unassigned';
            $studentName = $student ? trim($student->first_name . ' ' . $student->last_name) : 'Unregistered Student';
            $gradeLevel = $student ? ($student->grade_level ?? $assessment->year_level ?? 'N/A') : ($assessment->year_level ?? 'N/A');

            $studentSessions = $studentId ? ($allSessions->get($studentId) ?? collect()) : collect();
            $studentAnswersByKey = $studentId ? ($allAnswers->get($studentId) ?? collect())->keyBy('question_id') : collect();

            if ($access->status === 'finished' && $studentId) {
                $totalScore = AssessmentService::computeStudentScore($studentAnswersByKey, $qMap);
                $totalItems = $totalQuestions;
                $mps = AssessmentService::computeMPS($totalScore, $totalItems);
                $mpsDisplay = $mps . '%';
                $proficiency = AssessmentService::getProficiencyLevel($mps);
                $dateStarted = $studentSessions->isNotEmpty() && $studentSessions->min('created_at')
                    ? Carbon::parse($studentSessions->min('created_at'))->format('Y-m-d H:i:s')
                    : 'N/A';
                $dateSubmitted = $studentSessions->isNotEmpty() && $studentSessions->max('updated_at')
                    ? Carbon::parse($studentSessions->max('updated_at'))->format('Y-m-d H:i:s')
                    : 'N/A';
                $scoreDisplay = $totalScore;
            } else {
                $scoreDisplay = 'N/A';
                $totalItems = $totalQuestions;
                $mpsDisplay = 'N/A';
                $proficiency = 'N/A';
                $dateStarted = $studentSessions->isNotEmpty() && $studentSessions->min('created_at')
                    ? Carbon::parse($studentSessions->min('created_at'))->format('Y-m-d H:i:s')
                    : 'Not Started';
                $dateSubmitted = 'N/A';
            }

            $rowData = [
                $studentName, $access->lrn, $schoolName, $gradeLevel, $dateStarted, $dateSubmitted
            ];

            foreach ($categories as $cat) {
                if ($access->status === 'finished' && $studentId) {
                    $catQMap = array_filter($qMap, fn($q) => ($q['category_id'] ?? null) === $cat->id);
                    $catScore = AssessmentService::computeStudentScore($studentAnswersByKey, $catQMap);
                } else {
                    $catScore = 'N/A';
                }
                $rowData[] = $cat->title;
                $rowData[] = $catScore;
            }

            $rowData = array_merge($rowData, [
                $scoreDisplay, $totalItems, $mpsDisplay, $proficiency
            ]);

            $sheet->fromArray($rowData, null, 'A' . $row);
            $sheet->setCellValueExplicit('B' . $row, (string)$access->lrn, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $row++;
        }
        $this->autoWidth($sheet, count($headers));
    }

    private function resolveQuestionAnswer($question, ?object $studentAnswer): array
    {
        if (!$studentAnswer) {
            return ['Not Answered', 'Not Answered'];
        }

        $correctOptions = $question->options->where('is_correct', true);
        $correctOptionIds = $correctOptions->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $correctTexts = $correctOptions->pluck('option_text')->map(function($t) use ($question) {
            return $question->is_case_sensitive ? trim($t) : strtolower(trim($t));
        })->toArray();

        $selected = json_decode($studentAnswer->selected_options, true) ?? [];
        if (!is_array($selected)) $selected = [$selected];

        $isCorrect = false;
        $isPending = false;

        if ($question->type === 'checkbox') {
            $selectedStr = array_map('strval', $selected);
            $selectedOptsText = $question->options->whereIn('id', $selectedStr)->pluck('option_text')->map(fn($o) => strip_tags($o))->implode(', ');
            $studentAnswerText = $selectedOptsText ?: ($studentAnswer->answer_text ?: 'No selections');

            if (count($selectedStr) === count($correctOptionIds) && empty(array_diff($selectedStr, $correctOptionIds)) && empty(array_diff($correctOptionIds, $selectedStr))) {
                $isCorrect = true;
            }
        } elseif ($question->type === 'text') {
            $rawText = $studentAnswer->answer_text ?? ($selected[0] ?? '');
            $cleanText = trim((string)$rawText);
            $studentAnswerText = $cleanText !== '' ? $cleanText : 'No answer provided';

            if ($correctOptions->isNotEmpty()) {
                $cmpText = $question->is_case_sensitive ? $cleanText : strtolower($cleanText);
                if ($cleanText !== '' && in_array($cmpText, $correctTexts)) {
                    $isCorrect = true;
                }
            } else {
                $isPending = true;
            }
        } else {
            $selectedStr = array_map('strval', $selected);
            $selectedOptsText = $question->options->whereIn('id', $selectedStr)->pluck('option_text')->map(fn($o) => strip_tags($o))->implode(', ');
            $studentAnswerText = $selectedOptsText ?: ($studentAnswer->answer_text ?: 'No selections');

            foreach ($selectedStr as $sId) {
                if (in_array($sId, $correctOptionIds)) {
                    $isCorrect = true;
                    break;
                }
            }
        }

        $resultText = $isPending ? 'Pending' : ($isCorrect ? 'Correct' : 'Incorrect');
        return [$studentAnswerText ?: 'Answered', $resultText];
    }

    private function styleSheetHeader(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $headers): void
    {
        $sheet->fromArray($headers, null, 'A1');

        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $range = 'A1:' . $lastCol . '1';

        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFa52a2a'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(20);
    }

    private function autoWidth(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $colCount): void
    {
        for ($i = 1; $i <= $colCount; $i++) {
            $col = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
