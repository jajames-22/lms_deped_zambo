<?php

namespace App\Imports;

use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TeachersImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Skip empty rows (requires at least first name, last name, username, and employee_id)
        if (empty($row['first_name']) || empty($row['last_name']) || empty($row['username']) || empty($row['employee_id'])) {
            return null;
        }

        // Check if username already exists to prevent duplicate errors
        if (User::where('username', $row['username'])->exists()) {
            return null; 
        }

        // Check if Employee ID already exists to prevent duplicate errors
        if (User::where('employee_id', $row['employee_id'])->exists()) {
            return null; 
        }

        // Map the Excel's DepEd school_id to the Database ID
        $mappedSchoolId = null;
        if (!empty($row['school_id'])) {
            $school = School::where('school_id', $row['school_id'])->first();
            if ($school) {
                $mappedSchoolId = $school->id; 
            }
        }

        return new User([
            'employee_id' => $row['employee_id'],
            'first_name'  => $row['first_name'],
            'middle_name' => $row['middle_name'] ?? null,
            'last_name'   => $row['last_name'],
            'suffix'      => $row['suffix'] ?? null,
            'username'    => $row['username'], 
            'email'       => $row['email'] ?? null, 
            'password'    => Hash::make($row['password'] ?? 'Teacher123!'), // Default password if blank
            'role'        => 'teacher',
            'status'      => 'verified', 
            'school_id'   => $mappedSchoolId,
        ]);
    }
}