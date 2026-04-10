<?php

namespace App\Imports;

use App\Models\User;
use App\Models\School; 
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Skip empty rows (requires at least first name, last name, and username)
        if (empty($row['first_name']) || empty($row['last_name']) || empty($row['username'])) {
            return null;
        }

        // Check if username already exists to prevent duplicate errors
        if (User::where('username', $row['username'])->exists()) {
            return null; // Skip this row if username is taken
        }

        // Check if LRN already exists to prevent duplicate errors
        if (!empty($row['lrn']) && User::where('lrn', $row['lrn'])->exists()) {
            return null; // Skip this row if LRN already exists
        }

        // Map the Excel's DepEd school_id to the Database ID
        $mappedSchoolId = null;
        if (!empty($row['school_id'])) {
            $school = School::where('school_id', $row['school_id'])->first();
            if ($school) {
                $mappedSchoolId = $school->id; 
            }
        }

        // 👈 NEW: Safely grab the status and default to 'pending' if left blank or invalid
        $status = !empty($row['status']) ? strtolower(trim($row['status'])) : 'pending';
        if (!in_array($status, ['pending', 'verified', 'suspended'])) {
            $status = 'pending';
        }

        return new User([
            'lrn'         => $row['lrn'] ?? null,
            'first_name'  => $row['first_name'],
            'middle_name' => $row['middle_name'] ?? null,
            'last_name'   => $row['last_name'],
            'suffix'      => $row['suffix'] ?? null,
            'username'    => $row['username'], 
            'email'       => $row['email'] ?? null, 
            'password'    => Hash::make($row['password'] ?? 'Student123!'), 
            'role'        => 'student',
            'status'      => $status, // 👈 SAVES THE STATUS
            'grade_level' => $row['grade_level'] ?? null,
            'school_id'   => $mappedSchoolId,
        ]);
    }
}