<?php

namespace App\Imports;

use App\Models\Enrollment;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmailMaterialsAccessImport implements ToModel, WithHeadingRow
{
    protected $materialId;

    public function __construct($materialId)
    {
        $this->materialId = $materialId;
    }

    public function model(array $row)
    {
        // 1. Get the email from the row (WithHeadingRow expects the header to be 'email')
        $email = $row['email'] ?? null;

        if (!$email) {
            return null;
        }

        // 2. Find the user associated with this email
        $user = User::where('email', $email)->first();

        // If the user doesn't exist in your database, skip this row
        if (!$user) {
            return null;
        }

        // 3. Check if the user is already enrolled in this specific material
        $exists = Enrollment::where('materials_id', $this->materialId)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            return null;
        }

        // 4. Enroll the student
        return new Enrollment([
            'materials_id' => $this->materialId,
            'user_id'      => $user->id,
            'status'       => 'enrolled',
        ]);
    }
}