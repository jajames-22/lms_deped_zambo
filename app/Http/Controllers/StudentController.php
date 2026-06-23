<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\School;
use App\Models\Material;
use App\Models\Tag;
use App\Models\ExplorePageSection;
use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;



class StudentController extends Controller
{
    /**
     * Load the main student directory table
     */
    public function loadStudentsPartial()
    {
        // Fetch only students and eager-load their school and district to prevent N+1 performance issues
        $students = User::where('role', 'student')
            ->with('school.district')
            ->orderBy('last_name', 'asc')
            ->get();

        return view('dashboard.partials.admin.students', compact('students'));
    }

    /**
     * Load the "Add New Student" form
     */
    public function createStudentPartial()
    {
        // Fetch all schools for the assignment dropdown
        $schools = School::orderBy('name', 'asc')->get();
        return view('dashboard.partials.admin.student-create', compact('schools'));
    }

    /**
     * Securely save a new student to the database
     */
    public function storeStudent(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'username' => 'required|string|max:255|unique:users,username',
            'lrn' => 'nullable|string|max:255|unique:users,lrn',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'school_id' => 'required|exists:schools,id',
            'grade_level' => 'required|string|max:50',
            'status' => 'required|in:pending,verified,suspended',
        ]);

        // Force strict database conditions for security
        $validated['role'] = 'student';
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return response()->json(['success' => 'Student account created successfully!']);
    }

    /**
     * Load the "Edit Student" form
     */
    public function editStudentPartial($id)
    {
        // Ensure we are only pulling a student account
        $student = User::where('role', 'student')->findOrFail($id);
        $schools = School::orderBy('name', 'asc')->get();

        return view('dashboard.partials.admin.student-edit', compact('student', 'schools'));
    }

    /**
     * Securely update an existing student's details
     */
    public function updateStudent(Request $request, $id)
    {
        $student = User::where('role', 'student')->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'username' => 'required|string|max:255|unique:users,username,' . $student->id,
            'lrn' => 'nullable|string|max:255|unique:users,lrn,' . $student->id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $student->id,
            'password' => 'nullable|string|min:6',
            'school_id' => 'required|exists:schools,id',
            'grade_level' => 'required|string|max:50',
            'status' => 'required|in:pending,verified,suspended',
        ]);

        // Check if the admin wants to reset the password
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            // If left blank, remove it from the array so it doesn't overwrite the current password
            unset($validated['password']);
        }

        $student->update($validated);

        return response()->json(['success' => 'Student details updated successfully!']);
    }

    /**
     * Permanently remove a student account
     */
    public function destroyStudent($id)
    {
        $student = User::where('role', 'student')->findOrFail($id);
        $student->delete();

        return response()->json(['success' => 'Student account deleted successfully!']);
    }

    public function explore()
    {
        // 1. Keep your existing logic for the Hero Banner and Popular rankings
        $featuredMaterials = Material::with('instructor')
            ->where('is_featured', true)
            ->where('status', 'published')
            ->where('is_public', true)
            ->latest()
            ->get();

        $popularMaterials = Material::with('instructor')->where('status', 'published')->where('is_public', true)->orderBy('views', 'desc')->take(10)->get();

        // 2. Fetch Dynamic Sections and their associated materials
        $dynamicSections = ExplorePageSection::where('is_active', true)
            ->orderBy('order', 'asc')
            ->get()
            ->map(function ($section) {
                $tagsArray = json_decode($section->tag_name, true);
                if (!is_array($tagsArray))
                    $tagsArray = [$section->tag_name];

                $section->materials = Material::with('instructor')
                    ->whereHas('tags', function ($q) use ($tagsArray) {
                        $q->whereIn('name', $tagsArray);
                    })
                    ->where('status', 'published')
                    ->where('is_public', true)
                    ->inRandomOrder()
                    ->take(10)
                    ->get();

                return $section;
            });

        // 3. Keep School-specific logic
        $schoolMaterials = Material::whereHas('instructor', function ($query) {
            $query->where('school_id', auth()->user()->school_id);
        })
            ->where('status', 'published')
            ->where('is_public', true)
            ->inRandomOrder()
            ->take(6)
            ->get();

        return view('dashboard.partials.student.explore', compact(
            'featuredMaterials',
            'popularMaterials',
            'dynamicSections',
            'schoolMaterials'
        ));
    }

    public function viewByTagJson($tag)
    {
        $decodedTags = json_decode(urldecode($tag), true);
        $searchTags = is_array($decodedTags) ? $decodedTags : [trim(urldecode($tag))];

        $materials = \App\Models\Material::with('instructor')
            ->where('status', 'published')
            ->where('is_public', true)
            ->whereHas('tags', function ($query) use ($searchTags) {
                $query->whereIn('name', $searchTags);
                foreach ($searchTags as $t) {
                    if (is_numeric($t)) {
                        $query->orWhere('tags.id', $t);
                    }
                }
            })
            ->latest()
            ->get();

        return response()->json($materials);
    }

    public function incrementDownload(Material $material)
    {
        $material->increment('downloads');
        return response()->json(['success' => true]);
    }
    public function import(Request $request)
    {
        $request->validate([
            // Added 'txt' because Laravel often reads CSVs as text/plain
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
            'strategy' => 'nullable|in:skip,update',
            'check_only' => 'nullable|boolean'
        ]);

        $checkOnly = filter_var($request->check_only, FILTER_VALIDATE_BOOLEAN);
        $strategy = $request->strategy ?? 'skip';

        try {
            $import = new StudentsImport($strategy, $checkOnly);
            Excel::import($import, $request->file('file'));

            // If it was just a pre-check, return the found duplicates to the frontend
            if ($checkOnly) {
                // --- FIX: Format the duplicates to match the Javascript UI ---
                $formattedDuplicates = collect($import->duplicates)->map(function ($dup) {
                    if (isset($dup['existing']) && isset($dup['incoming'])) {
                        return $dup;
                    }

                    $lrn = $dup['lrn'] ?? null;
                    $existingUser = $lrn ? User::where('lrn', $lrn)->first() : null;

                    // Format Names
                    $existingName = $existingUser ? trim(($existingUser->first_name ?? '') . ' ' . ($existingUser->last_name ?? '')) : 'N/A';
                    $incomingName = trim(($dup['first_name'] ?? '') . ' ' . ($dup['last_name'] ?? '')) ?: 'N/A';

                    return [
                        'lrn' => $lrn ?? 'Unknown',
                        'name' => $incomingName,

                        // Populate the existing database record
                        'existing' => [
                            'name' => $existingName,
                            'grade' => $existingUser->grade_level ?? 'N/A',
                            'section' => $existingUser->section ?? 'N/A',
                            'gender' => $existingUser->gender ?? 'N/A',
                        ],

                        // Populate the new spreadsheet record
                        'incoming' => [
                            'name' => $incomingName,
                            'grade' => $dup['grade_level'] ?? $dup['grade'] ?? 'N/A',
                            'section' => $dup['section'] ?? 'N/A',
                            'gender' => $dup['gender'] ?? 'N/A',
                        ]
                    ];
                })->values()->toArray();
                // -------------------------------------------------------------

                return response()->json([
                    'has_duplicates' => count($formattedDuplicates) > 0,
                    'duplicates' => $formattedDuplicates
                ]);
            }

            // Standard import message builder
            $message = "Successfully imported {$import->importedCount} new students.";
            if ($strategy === 'update' && $import->updatedCount > 0) {
                $message .= " Updated {$import->updatedCount} existing students.";
            }
            if ($import->skippedCount > 0) {
                $message .= " Skipped {$import->skippedCount} invalid or duplicate rows.";
            }

            return response()->json(['message' => $message]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Student_Import_Template.csv"',
        ];

        $columns = [
            'lrn',
            'first_name',
            'middle_name',
            'last_name',
            'suffix',
            'username',
            'email',
            'password',
            'grade_level',
            'school_id',
            'status',
            'INSTRUCTIONS_(READ_ME)' // 👈 Added a dedicated instructions column
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');

            // 1. Add the headers (Row 1)
            fputcsv($file, $columns);

            // 2. Add the sample row with the instructions (Row 2)
            fputcsv($file, [
                '123456789012',         // A2: LRN 
                'Juan',                 // B2: First Name
                'Pedro',                // C2: Middle Name
                'Dela Cruz',            // D2: Last Name
                'Jr.',                  // E2: Suffix
                '',                     // F2: Username (Left blank so backend auto-generates it)
                '',                     // G2: Email (Optional)
                '',                     // H2: Password (Left blank so backend defaults to Student123!)
                'Grade 10',             // I2: Grade Level
                '',                     // J2: School ID (Left blank so backend uses default school)
                'pending',              // K2: Status

                // 👈 L2: The Instruction Cell
                'INSTRUCTIONS: Only first_name and last_name are strictly required. You can safely leave username, password, email, and school_id BLANK to use system defaults. IMPORTANT: Delete this entire sample row before importing.'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function bulkDestroy(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        \App\Models\User::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'The selected students have been successfully removed.'
        ]);
    }

    public function report(Request $request)
    {
        // Start building the query
        $query = \App\Models\User::where('role', 'student')->with(['school', 'school.district']);
        $titleStatus = '';

        // Filter based on the Modal Checkboxes
        if ($request->has('status_type') && $request->status_type === 'all') {
            // Do nothing, fetch all records
        } elseif ($request->has('statuses') && is_array($request->statuses)) {
            $query->whereIn('status', $request->statuses);

            $formattedStatuses = array_map('ucfirst', $request->statuses);
            $titleStatus = implode(' & ', $formattedStatuses) . ' ';
        }

        // Execute the query
        $students = $query->orderBy('last_name', 'asc')->get();

        $data = [
            'title' => $titleStatus . 'Student Directory Report',
            'type' => 'students',
            'records' => $students,
            'isPrint' => $request->action === 'print'
        ];

        // Output Print View
        if ($request->action === 'print') {
            return view('dashboard.partials.shared.list-report', $data);
        }

        // Output PDF Download
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.partials.shared.list-report', $data)->setPaper('a4', 'landscape');
        return $pdf->download('Student_Directory_' . now()->format('Y_m_d') . '.pdf');
    }
}