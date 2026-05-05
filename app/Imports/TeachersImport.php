<?php

namespace App\Imports;

use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TeachersImport implements ToCollection, WithHeadingRow
{
    public $importedCount = 0;
    public $skippedCount = 0;
    public $updatedCount = 0;
    public $duplicates = [];

    protected $strategy;
    protected $checkOnly;

    public function __construct($strategy = 'skip', $checkOnly = false)
    {
        $this->strategy = $strategy;
        $this->checkOnly = $checkOnly;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // 1. Skip if required fields are missing
            if (
                empty(trim($row['first_name'] ?? '')) ||
                empty(trim($row['last_name'] ?? '')) ||
                empty(trim($row['username'] ?? '')) ||
                empty(trim($row['employee_id'] ?? ''))
            ) {
                if (!$this->checkOnly) $this->skippedCount++;
                continue;
            }

            // 2. Employee ID duplicate check
            $employeeId = trim($row['employee_id']);
            $existingUser = User::where('employee_id', $employeeId)->first();

            if ($existingUser) {
                // Store full incoming row data for the conflict modal
                $this->duplicates[] = [
                    'employee_id' => $employeeId,
                    'first_name'  => trim($row['first_name'] ?? ''),
                    'last_name'   => trim($row['last_name'] ?? ''),
                    'grade_level' => trim($row['grade_level'] ?? ''),
                    'section'     => trim($row['section'] ?? ''),
                    'gender'      => trim($row['gender'] ?? ''),
                ];

                if ($this->checkOnly) continue;

                if ($this->strategy === 'skip') {
                    $this->skippedCount++;
                    continue;
                }

                $this->updateExistingTeacher($existingUser, $row);
                continue;
            }

            if ($this->checkOnly) continue;

            // 3. Username conflict guard
            $username = trim($row['username']);
            if (User::where('username', $username)->exists()) {
                $username = $username . Str::random(4);
            }

            // 4. Email conflict guard
            $email = trim($row['email'] ?? '');
            if (!empty($email) && User::where('email', $email)->exists()) {
                $email = null;
            }

            // 5. School ID mapping
            $mappedSchoolId = null;
            $providedSchoolId = trim($row['school_id'] ?? '');
            if (!empty($providedSchoolId)) {
                $school = School::where('school_id', $providedSchoolId)->orWhere('id', $providedSchoolId)->first();
                if ($school) $mappedSchoolId = $school->id;
            }

            // 6. Status validation
            $status = !empty($row['status']) ? strtolower(trim($row['status'])) : 'pending';
            if (!in_array($status, ['pending', 'verified', 'suspended'])) {
                $status = 'pending';
            }

            // 7. Insert
            try {
                User::create([
                    'employee_id' => $employeeId,
                    'first_name'  => trim($row['first_name']),
                    'middle_name' => trim($row['middle_name'] ?? '') ?: null,
                    'last_name'   => trim($row['last_name']),
                    'suffix'      => trim($row['suffix'] ?? '') ?: null,
                    'username'    => $username,
                    'email'       => $email ?: null,
                    'password'    => Hash::make(!empty($row['password']) ? trim($row['password']) : 'Teacher123!'),
                    'role'        => 'teacher',
                    'status'      => $status,
                    'school_id'   => $mappedSchoolId,
                ]);
                $this->importedCount++;
            } catch (\Exception $e) {
                Log::error('Teacher Import Row Failed: ' . $e->getMessage());
                $this->skippedCount++;
            }
        }
    }

    private function updateExistingTeacher($existingUser, $row)
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
            if ($school) $mappedSchoolId = $school->id;
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
                'school_id'   => $mappedSchoolId,
                'status'      => $status,
            ]);

            if (!empty(trim($row['password'] ?? ''))) {
                $existingUser->update(['password' => Hash::make(trim($row['password']))]);
            }

            $this->updatedCount++;
        } catch (\Exception $e) {
            Log::error('Teacher Update Failed: ' . $e->getMessage());
            $this->skippedCount++;
        }
    }
}