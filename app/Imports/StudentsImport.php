<?php

namespace App\Imports;

use App\Models\User;
use App\Models\School; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class StudentsImport implements ToCollection, WithHeadingRow
{
    public $importedCount = 0;
    public $skippedCount = 0;
    public $updatedCount = 0; 
    public $duplicates = []; // <-- NEW: Array to hold found duplicates

    protected $strategy;
    protected $checkOnly;

    // Accept both the strategy and the check_only flag
    public function __construct($strategy = 'skip', $checkOnly = false)
    {
        $this->strategy = $strategy;
        $this->checkOnly = $checkOnly;
    }

    public function collection(Collection $rows)
    {
        $defaultSchool = School::first(); 

        foreach ($rows as $row) {
            // 1. Skip if required names are missing
            if (empty(trim($row['first_name'] ?? '')) || empty(trim($row['last_name'] ?? ''))) {
                if (!$this->checkOnly) $this->skippedCount++;
                continue;
            }

            // 2. LRN Check & Conflict Resolution
            $lrn = trim($row['lrn'] ?? '');
            $existingUser = !empty($lrn) ? User::where('lrn', $lrn)->first() : null;

if ($existingUser) {
    // ✅ FIX: Store full incoming row data so the modal can show both sides
    $this->duplicates[] = [
        'lrn'         => $lrn,
        'first_name'  => trim($row['first_name'] ?? ''),
        'last_name'   => trim($row['last_name'] ?? ''),
        'grade_level' => trim($row['grade_level'] ?? ''),
        'section'     => trim($row['section'] ?? ''),
        'gender'      => trim($row['gender'] ?? ''),
    ];

    // If we are just pre-checking, stop processing this row and move to the next
    if ($this->checkOnly) continue;

    // Actual Processing: If user chose to skip duplicates
    if ($this->strategy === 'skip') {
        $this->skippedCount++;
        continue; 
    }

    // Actual Processing: If user chose to UPDATE existing records
    $this->updateExistingStudent($existingUser, $row, $defaultSchool);
    continue;
}
            // If we are only checking for duplicates, skip the actual creation logic
            if ($this->checkOnly) continue;

            // --- Logic below is for NEW students only ---

            // 3. Username generation
            $username = trim($row['username'] ?? '');
            if (empty($username)) {
                $username = strtolower(trim($row['first_name'])) . '.' . strtolower(trim($row['last_name'])) . rand(10, 999);
                $username = str_replace(' ', '', $username); 
            }
            if (User::where('username', $username)->exists()) {
                $username = $username . Str::random(4); 
            }

            // 4. Email Check
            $email = trim($row['email'] ?? '');
            if (!empty($email) && User::where('email', $email)->exists()) {
                $email = null; 
            }

            // 5. School ID Logic
            $mappedSchoolId = $defaultSchool ? $defaultSchool->id : null;
            $providedSchoolId = trim($row['school_id'] ?? '');
            
            if (!empty($providedSchoolId)) {
                $school = School::where('school_id', $providedSchoolId)->orWhere('id', $providedSchoolId)->first();
                if ($school) {
                    $mappedSchoolId = $school->id; 
                }
            }

            // 6. Status Validation
            $status = !empty($row['status']) ? strtolower(trim($row['status'])) : 'pending';
            if (!in_array($status, ['pending', 'verified', 'suspended'])) {
                $status = 'pending';
            }

            // 7. Insert the User
            try {
                User::create([
                    'lrn'         => $lrn ?: null,
                    'first_name'  => trim($row['first_name']),
                    'middle_name' => trim($row['middle_name'] ?? '') ?: null,
                    'last_name'   => trim($row['last_name']),
                    'suffix'      => trim($row['suffix'] ?? '') ?: null,
                    'username'    => $username, 
                    'email'       => $email ?: null, 
                    'password'    => Hash::make(!empty($row['password']) ? trim($row['password']) : 'Student123!'), 
                    'role'        => 'student',
                    'status'      => $status,
                    'grade_level' => trim($row['grade_level'] ?? '') ?: null,
                    'school_id'   => $mappedSchoolId,
                ]);
                $this->importedCount++;
            } catch (\Exception $e) {
                Log::error('Student Import Row Failed: ' . $e->getMessage());
                $this->skippedCount++;
            }
        }   
    }

    private function updateExistingStudent($existingUser, $row, $defaultSchool)
    {
        $email = trim($row['email'] ?? '');
        if (!empty($email) && User::where('email', $email)->where('id', '!=', $existingUser->id)->exists()) {
            $email = $existingUser->email; 
        } elseif (empty($email)) {
            $email = $existingUser->email; 
        }

        $mappedSchoolId = $existingUser->school_id;
        $providedSchoolId = trim($row['school_id'] ?? '');
        if (!empty($providedSchoolId)) {
            $school = School::where('school_id', $providedSchoolId)->orWhere('id', $providedSchoolId)->first();
            if ($school) {
                $mappedSchoolId = $school->id;
            }
        }

        $status = !empty($row['status']) ? strtolower(trim($row['status'])) : $existingUser->status;
        if (!in_array($status, ['pending', 'verified', 'suspended'])) {
            $status = $existingUser->status;
        }

        try {
            $existingUser->update([
                'first_name'  => trim($row['first_name']),
                'middle_name' => trim($row['middle_name'] ?? '') ?: null,
                'last_name'   => trim($row['last_name']),
                'suffix'      => trim($row['suffix'] ?? '') ?: null,
                'email'       => $email,
                'grade_level' => trim($row['grade_level'] ?? '') ?: $existingUser->grade_level,
                'school_id'   => $mappedSchoolId,
                'status'      => $status,
            ]);

            if (!empty(trim($row['password'] ?? ''))) {
                $existingUser->update(['password' => Hash::make(trim($row['password']))]);
            }

            $this->updatedCount++;
        } catch (\Exception $e) {
            Log::error('Student Update Failed: ' . $e->getMessage());
            $this->skippedCount++;
        }
    }
}