<?php

namespace App\Imports;

use App\Models\MaterialAccess; // 👈 Changed from Enrollment
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
        // Fetch the email from the row (Requires header in CSV to be 'email')
        $email = $row['email'] ?? null;
        
        if (!$email) {
            return null;
        }

        // 👈 Changed: Check the MaterialAccess table, not Enrollment
        $exists = MaterialAccess::where('material_id', $this->materialId)
            ->where('email', $email)
            ->exists();

        if ($exists) {
            return null;
        }

        // Check if they already have an account in the system
        $user = User::where('email', $email)->first();

        // 👈 Changed: Save to MaterialAccess instead of Enrollment
        return new MaterialAccess([
            'material_id' => $this->materialId,
            'email' => $email,
            'status' => $user ? 'enrolled' : 'pending',
        ]);
    }
}