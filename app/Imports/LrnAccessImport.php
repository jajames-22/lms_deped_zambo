<?php

namespace App\Imports;

use App\Models\AssessmentAccess;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LrnAccessImport implements ToCollection, WithHeadingRow
{
    public $importedCount = 0;
    public $skippedCount = 0;
    public $duplicates = [];

    protected $assessmentId;
    protected $strategy;
    protected $checkOnly;

    public function __construct($assessmentId, $strategy = 'skip', $checkOnly = false)
    {
        $this->assessmentId = $assessmentId;
        $this->strategy     = $strategy;
        $this->checkOnly    = $checkOnly;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $lrn = trim($row['lrn'] ?? '');

            if (empty($lrn)) {
                if (!$this->checkOnly) $this->skippedCount++;
                continue;
            }

            $existing = AssessmentAccess::where('assessment_id', $this->assessmentId)
                ->where('lrn', $lrn)
                ->first();

            if ($existing) {
                // Store the duplicate LRN for the conflict modal
                $this->duplicates[] = ['lrn' => $lrn];

                if ($this->checkOnly) continue;

                if ($this->strategy === 'skip') {
                    $this->skippedCount++;
                    continue;
                }

                // 'update' strategy: reset status back to offline
                try {
                    $existing->update(['status' => 'offline']);
                    $this->importedCount++;
                } catch (\Exception $e) {
                    Log::error('LRN Access Update Failed: ' . $e->getMessage());
                    $this->skippedCount++;
                }
                continue;
            }

            if ($this->checkOnly) continue;

            try {
                AssessmentAccess::create([
                    'assessment_id' => $this->assessmentId,
                    'lrn'           => $lrn,
                    'status'        => 'offline',
                ]);
                $this->importedCount++;
            } catch (\Exception $e) {
                Log::error('LRN Access Import Failed: ' . $e->getMessage());
                $this->skippedCount++;
            }
        }
    }
}