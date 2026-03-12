<?php

namespace App\Imports;

use App\Models\AssessmentAccess;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LrnAccessImport implements ToModel, WithHeadingRow
{
    protected $assessmentId;

    public function __construct($assessmentId)
    {
        $this->assessmentId = $assessmentId;
    }

    public function model(array $row)
    {
        // Use 'lrn' (lowercase) because WithHeadingRow converts headers to slug format
        $lrn = $row['lrn'] ?? null;

        if (!$lrn) {
            return null;
        }

        // Optional: Skip if already exists for this assessment
        $exists = AssessmentAccess::where('assessment_id', $this->assessmentId)
            ->where('lrn', $lrn)
            ->exists();

        if ($exists) {
            return null;
        }

        return new AssessmentAccess([
            'assessment_id' => $this->assessmentId,
            'lrn'           => $lrn,
            'status'        => 'offline',
        ]);
    }
}