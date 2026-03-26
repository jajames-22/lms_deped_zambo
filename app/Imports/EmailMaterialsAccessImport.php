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
        // Fetch the email from the row
        $email = $row['email'] ?? null;
        if (!$email)
            return null;

        // Check if this exact email is already in the list for this material
        $exists = Enrollment::where('materials_id', $this->materialId)
            ->where('email', $email)
            ->exists();

        if ($exists)
            return null;

        // Check if they already have an account in the system
        $user = User::where('email', $email)->first();

        // Save them. If they have an account -> 'enrolled'. If not -> 'pending'.
        return new Enrollment([
            'materials_id' => $this->materialId,
            'user_id' => $user ? $user->id : null,
            'email' => $email,
            'status' => $user ? 'enrolled' : 'pending',
        ]);
    }
}