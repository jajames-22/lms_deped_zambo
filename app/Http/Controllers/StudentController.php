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
            'file' => 'required|file|max:10240' // Allow up to 10MB
        ]);

        try {
            $import = new StudentsImport();
            Excel::import($import, $request->file('file'));

            // 🛑 Generate detailed feedback for the user
            $message = "Successfully imported {$import->importedCount} students.";
            if ($import->skippedCount > 0) {
                $message .= " Skipped {$import->skippedCount} rows (missing data, duplicate LRN, or database error).";
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed. Error: ' . $e->getMessage()
            ], 500);
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
            'status' 
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            fputcsv($file, [
                '123456789012',
                'Juan',
                'Pedro',
                'Dela Cruz',
                'Jr.',
                'juan_delacruz',
                'juan@deped.gov.ph',
                'SecretPass!',
                'Grade 10',
                '123456',
                'verified' 
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