<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\SchoolsImport;
use App\Imports\QuadrantsImport;
use App\Imports\DistrictsImport;
use Maatwebsite\Excel\Facades\Excel;

class SchoolController extends Controller
{
    /**
     * Download the CSV template for importing based on type.
     */
    public function downloadTemplate(Request $request)
    {
        $type = $request->query('type', 'schools');

        if ($type === 'schools') {
            $filename = 'School_Import_Template.csv';
            $columns = ['school_id', 'official_name', 'level', 'address', 'quadrant', 'district'];
            $sampleRow = ['305412', 'Sample Elementary School', 'Elementary', 'Barangay 1', 'East Coast', 'District 1'];
        } elseif ($type === 'quadrants') {
            $filename = 'Quadrant_Import_Template.csv';
            $columns = ['quadrant_name'];
            $sampleRow = ['East Coast'];
        } elseif ($type === 'districts') {
            $filename = 'District_Import_Template.csv';
            $columns = ['district_name', 'quadrant_name'];
            $sampleRow = ['District 1', 'East Coast'];
        } else {
            abort(404, 'Invalid import type');
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($columns, $sampleRow) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $sampleRow);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Handle the Excel/CSV upload and import based on type.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
            'type' => 'required|in:schools,quadrants,districts'
        ]);

        $type = $request->type;

        try {
            if ($type === 'schools') {
                $import = new SchoolsImport();
                Excel::import($import, $request->file('file'));
                
                $message = "Imported Successfully\n\n";
                $message .= "Created:\n";
                $message .= "- {$import->importedSchools} Schools\n";
                $message .= "- {$import->importedQuadrants} Quadrants\n";
                $message .= "- {$import->importedDistricts} Districts\n\n";
                $message .= "Skipped:\n";
                $message .= "- {$import->skippedDuplicateSchools} Duplicate Schools\n";
                $message .= "- {$import->skippedDuplicateQuadrants} Duplicate Quadrants\n";
                $message .= "- {$import->skippedDuplicateDistricts} Duplicate Districts\n\n";
                $message .= "Errors:\n";
                $message .= "- {$import->invalidRows} Invalid Rows\n";

                return response()->json(['message' => nl2br(e($message))]);
            } elseif ($type === 'quadrants') {
                $import = new QuadrantsImport();
                Excel::import($import, $request->file('file'));
                
                $message = "Imported Successfully\n\n";
                $message .= "Created:\n";
                $message .= "- {$import->importedQuadrants} Quadrants\n\n";
                $message .= "Skipped:\n";
                $message .= "- {$import->skippedDuplicateQuadrants} Duplicate Quadrants\n\n";
                $message .= "Errors:\n";
                $message .= "- {$import->invalidRows} Invalid Rows\n";

                return response()->json(['message' => nl2br(e($message))]);
            } elseif ($type === 'districts') {
                $import = new DistrictsImport();
                Excel::import($import, $request->file('file'));
                
                $message = "Imported Successfully\n\n";
                $message .= "Created:\n";
                $message .= "- {$import->importedDistricts} Districts\n";
                $message .= "- {$import->importedQuadrants} Quadrants\n\n";
                $message .= "Skipped:\n";
                $message .= "- {$import->skippedDuplicateDistricts} Duplicate Districts\n";
                $message .= "- {$import->skippedDuplicateQuadrants} Duplicate Quadrants\n\n";
                $message .= "Errors:\n";
                $message .= "- {$import->invalidRows} Invalid Rows\n";

                return response()->json(['message' => nl2br(e($message))]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }
}
