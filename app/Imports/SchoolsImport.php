<?php

namespace App\Imports;

use App\Models\School;
use App\Models\Quadrant;
use App\Models\District;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class SchoolsImport implements ToCollection, WithHeadingRow
{
    public $importedSchools = 0;
    public $importedQuadrants = 0;
    public $importedDistricts = 0;
    
    public $skippedDuplicateSchools = 0;
    public $skippedDuplicateQuadrants = 0;
    public $skippedDuplicateDistricts = 0;
    
    public $invalidRows = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $schoolId = trim($row['school_id'] ?? '');
            $officialName = trim($row['official_name'] ?? '');
            $level = strtolower(trim($row['level'] ?? ''));
            $address = trim($row['address'] ?? '');
            $quadrantName = trim($row['quadrant'] ?? '');
            $districtName = trim($row['district'] ?? '');

            if (empty($schoolId) || empty($officialName) || empty($level) || empty($quadrantName) || empty($districtName)) {
                $this->invalidRows++;
                continue;
            }

            // Map level values
            if (!in_array($level, ['elementary', 'highschool', 'seniorhighschool', 'integrated'])) {
                // Try to infer
                if (str_contains($level, 'senior')) $level = 'seniorhighschool';
                elseif (str_contains($level, 'high')) $level = 'highschool';
                elseif (str_contains($level, 'integrated')) $level = 'integrated';
                else $level = 'elementary'; // default fallback
            }

            try {
                DB::transaction(function () use ($schoolId, $officialName, $level, $address, $quadrantName, $districtName) {
                    // Find or create quadrant
                    $quadrant = Quadrant::where('name', $quadrantName)->first();
                    if (!$quadrant) {
                        $quadrant = Quadrant::create(['name' => $quadrantName]);
                        $this->importedQuadrants++;
                    } else {
                        $this->skippedDuplicateQuadrants++;
                    }

                    // Find or create district
                    $district = District::where('name', $districtName)
                        ->where('quadrant_id', $quadrant->id)
                        ->first();
                    
                    if (!$district) {
                        $district = District::create([
                            'name' => $districtName,
                            'quadrant_id' => $quadrant->id
                        ]);
                        $this->importedDistricts++;
                    } else {
                        $this->skippedDuplicateDistricts++;
                    }

                    // Check school duplicate
                    $school = School::where('school_id', $schoolId)->orWhere('name', $officialName)->first();
                    
                    if (!$school) {
                        School::create([
                            'school_id' => $schoolId,
                            'name' => $officialName,
                            'level' => $level,
                            'address' => $address,
                            'district_id' => $district->id
                        ]);
                        $this->importedSchools++;
                    } else {
                        $this->skippedDuplicateSchools++;
                    }
                });
            } catch (\Exception $e) {
                Log::error('Schools Import Row Failed: ' . $e->getMessage());
                $this->invalidRows++;
            }
        }
    }
}
