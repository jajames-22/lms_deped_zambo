<?php

namespace App\Services;

use App\Models\AssessmentCategory;
use App\Models\AssessmentQuestion;
use Illuminate\Support\Collection;

class AssessmentService
{
    /**
     * Compute Mean Percentage Score (MPS).
     * Formula: (Student Score ÷ Total Possible Score) × 100
     */
    public static function computeMPS(float $studentScore, float $totalPossibleScore): float
    {
        if ($totalPossibleScore <= 0) {
            return 0;
        }

        return round(($studentScore / $totalPossibleScore) * 100, 2);
    }

    /**
     * Get Proficiency Level based on MPS standard NAT scale.
     * 90% - 100% = Highly Proficient
     * 75% - 89% = Proficient
     * 50% - 74% = Nearly Proficient
     * 25% - 49% = Low Proficient
     * 0% - 24% = Not Proficient
     */
    public static function getProficiencyLevel(float $mps): string
    {
        if ($mps >= 90) {
            return 'Highly Proficient';
        }
        if ($mps >= 75) {
            return 'Proficient';
        }
        if ($mps >= 50) {
            return 'Nearly Proficient';
        }
        if ($mps >= 25) {
            return 'Low Proficient';
        }

        return 'Not Proficient';
    }

    /**
     * Builds grading metadata map for an assessment.
     */
    public static function getAssessmentGradingMap(int $assessmentId): array
    {
        $categories = AssessmentCategory::where('assessment_id', $assessmentId)->orderBy('sort_order')->get();
        $categoryIds = $categories->pluck('id');
        
        $questions = AssessmentQuestion::with('options')
            ->whereIn('category_id', $categoryIds)
            ->where('type', '!=', 'instruction')
            ->orderBy('sort_order')
            ->get();

        $totalQuestions = $questions->count();
        $qMap = [];

        foreach ($questions as $q) {
            $correctOptions = $q->options->where('is_correct', true);
            $correctOptionIds = $correctOptions->pluck('id')->map(fn($id) => (string)$id)->toArray();
            $correctTexts = $correctOptions->pluck('option_text')->map(function($t) use ($q) {
                return $q->is_case_sensitive ? trim($t) : strtolower(trim($t));
            })->toArray();

            $qMap[$q->id] = [
                'category_id' => $q->category_id,
                'type' => $q->type,
                'is_case_sensitive' => $q->is_case_sensitive,
                'correct_option_ids' => $correctOptionIds,
                'correct_texts' => $correctTexts
            ];
        }

        return [$totalQuestions, $qMap, $categories];
    }

    /**
     * Grades a collection of student answers against the question grading map.
     */
    public static function computeStudentScore(Collection $studentAnswersByKey, array $qMap): int
    {
        $score = 0;
        foreach ($qMap as $qId => $qInfo) {
            $ans = $studentAnswersByKey->get($qId);
            if (!$ans) continue;

            $selected = json_decode($ans->selected_options, true) ?? [];
            if (!is_array($selected)) $selected = [$selected];
            $type = $qInfo['type'];
            $isCaseSens = $qInfo['is_case_sensitive'];
            $isCorrect = false;

            if ($type === 'checkbox') {
                $selectedStr = array_map('strval', $selected);
                $correctStr = $qInfo['correct_option_ids'];
                if (count($selectedStr) === count($correctStr) && empty(array_diff($selectedStr, $correctStr)) && empty(array_diff($correctStr, $selectedStr))) {
                    $isCorrect = true;
                }
            } elseif ($type === 'text') {
                $ansText = $ans->answer_text ?? ($selected[0] ?? '');
                $ansTextClean = trim((string)$ansText);
                if ($ansTextClean !== '') {
                    $ansTextCmp = $isCaseSens ? $ansTextClean : strtolower($ansTextClean);
                    if (in_array($ansTextCmp, $qInfo['correct_texts'])) {
                        $isCorrect = true;
                    }
                }
            } else {
                $selectedStr = array_map('strval', $selected);
                foreach ($selectedStr as $sId) {
                    if (in_array($sId, $qInfo['correct_option_ids'])) {
                        $isCorrect = true;
                        break;
                    }
                }
            }

            if ($isCorrect) $score++;
        }
        return $score;
    }
}
