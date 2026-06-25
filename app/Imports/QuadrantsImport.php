<?php

namespace App\Imports;

use App\Models\Quadrant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class QuadrantsImport implements ToCollection, WithHeadingRow
{
    public $importedQuadrants = 0;
    public $skippedDuplicateQuadrants = 0;
    public $invalidRows = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $quadrantName = trim($row['quadrant_name'] ?? '');

            if (empty($quadrantName)) {
                $this->invalidRows++;
                continue;
            }

            try {
                DB::transaction(function () use ($quadrantName) {
                    $quadrant = Quadrant::where('name', $quadrantName)->first();
                    if (!$quadrant) {
                        Quadrant::create(['name' => $quadrantName]);
                        $this->importedQuadrants++;
                    } else {
                        $this->skippedDuplicateQuadrants++;
                    }
                });
            } catch (\Exception $e) {
                Log::error('Quadrants Import Row Failed: ' . $e->getMessage());
                $this->invalidRows++;
            }
        }
    }
}
