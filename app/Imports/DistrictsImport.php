<?php

namespace App\Imports;

use App\Models\District;
use App\Models\Quadrant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class DistrictsImport implements ToCollection, WithHeadingRow
{
    public $importedDistricts = 0;
    public $importedQuadrants = 0;
    public $skippedDuplicateDistricts = 0;
    public $skippedDuplicateQuadrants = 0;
    public $invalidRows = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $districtName = trim($row['district_name'] ?? '');
            $quadrantName = trim($row['quadrant_name'] ?? '');

            if (empty($districtName) || empty($quadrantName)) {
                $this->invalidRows++;
                continue;
            }

            try {
                DB::transaction(function () use ($districtName, $quadrantName) {
                    $quadrant = Quadrant::where('name', $quadrantName)->first();
                    if (!$quadrant) {
                        $quadrant = Quadrant::create(['name' => $quadrantName]);
                        $this->importedQuadrants++;
                    } else {
                        $this->skippedDuplicateQuadrants++;
                    }

                    $district = District::where('name', $districtName)
                        ->where('quadrant_id', $quadrant->id)
                        ->first();

                    if (!$district) {
                        District::create([
                            'name' => $districtName,
                            'quadrant_id' => $quadrant->id
                        ]);
                        $this->importedDistricts++;
                    } else {
                        $this->skippedDuplicateDistricts++;
                    }
                });
            } catch (\Exception $e) {
                Log::error('Districts Import Row Failed: ' . $e->getMessage());
                $this->invalidRows++;
            }
        }
    }
}
