<?php

namespace App\Imports;

use App\Models\MaterialAccess;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmailMaterialsAccessImport implements ToCollection, WithHeadingRow
{
    public $importedCount = 0;
    public $skippedCount = 0;
    public $updatedCount = 0;
    public $duplicates = [];
    public $newEmails = []; // Tracks newly added emails for notifications

    protected $materialId;
    protected $strategy;
    protected $checkOnly;

    public function __construct($materialId, $strategy = 'skip', $checkOnly = false)
    {
        $this->materialId = $materialId;
        $this->strategy   = $strategy;
        $this->checkOnly  = $checkOnly;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $email = trim($row['email'] ?? '');

            if (empty($email)) {
                if (!$this->checkOnly) $this->skippedCount++;
                continue;
            }

            $existing = MaterialAccess::where('material_id', $this->materialId)
                ->where('email', $email)
                ->first();

            if ($existing) {
                // Store the duplicate for the conflict modal
                $this->duplicates[] = [
                    'email'  => $email,
                    'status' => $existing->status,
                ];

                if ($this->checkOnly) continue;

                if ($this->strategy === 'skip') {
                    $this->skippedCount++;
                    continue;
                }

                // 'update' strategy: re-activate access by resetting to pending
                try {
                    $user = User::where('email', $email)->first();
                    $existing->update([
                        'status' => $user ? 'enrolled' : 'pending',
                    ]);
                    $this->updatedCount++;
                } catch (\Exception $e) {
                    Log::error('MaterialAccess Update Failed: ' . $e->getMessage());
                    $this->skippedCount++;
                }
                continue;
            }

            if ($this->checkOnly) continue;

            try {
                $user = User::where('email', $email)->first();
                MaterialAccess::create([
                    'material_id' => $this->materialId,
                    'email'       => $email,
                    'status'      => $user ? 'enrolled' : 'pending',
                ]);
                $this->newEmails[] = $email; // Track for notification
                $this->importedCount++;
            } catch (\Exception $e) {
                Log::error('MaterialAccess Import Failed: ' . $e->getMessage());
                $this->skippedCount++;
            }
        }
    }
}