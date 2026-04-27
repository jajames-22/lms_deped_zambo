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

    public function collection(Collection $rows)
    {
        // Get a default school as a fallback to prevent database crashes
        $defaultSchool = School::first(); 

        foreach ($rows as $row) {
            // 1. Skip if required names are missing
            if (empty(trim($row['first_name'] ?? '')) || empty(trim($row['last_name'] ?? ''))) {
                $this->skippedCount++;
                continue;
            }

            // 2. LRN Check - Skip if LRN is taken to prevent SQL duplicate errors
            $lrn = trim($row['lrn'] ?? '');
            if (!empty($lrn) && User::where('lrn', $lrn)->exists()) {
                $this->skippedCount++;
                continue; 
            }

            // 3. Username generation & uniqueness
            $username = trim($row['username'] ?? '');
            if (empty($username)) {
                $username = strtolower(trim($row['first_name'])) . '.' . strtolower(trim($row['last_name'])) . rand(10, 999);
                $username = str_replace(' ', '', $username); 
            }
            if (User::where('username', $username)->exists()) {
                $username = $username . Str::random(4); 
            }

            // 4. Email Check - Nullify if email is taken to prevent SQL duplicate errors
            $email = trim($row['email'] ?? '');
            if (!empty($email) && User::where('email', $email)->exists()) {
                $email = null; 
            }

            // 5. School ID Logic - Fallback to default if the CSV provides a dummy ID
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

            // 7. Insert the User safely
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
}