<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\School;


class TeacherController extends Controller
{
    /**
     * Load the "Add New Teacher" form
     */
    public function createTeacherPartial()
    {
        // Fetch schools for the dropdown
        $schools = School::orderBy('name', 'asc')->get();
        return view('dashboard.partials.admin.teacher-create', compact('schools'));
    }

    /**
     * Securely save a new teacher to the database
     */
    public function storeTeacher(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            
            // Required Account Credentials
            'username' => 'required|string|max:255|unique:users,username',
            'employee_id' => 'required|string|max:255|unique:users,employee_id',
            'password' => 'required|string|min:6',
            
            // Optional Email
            'email' => 'nullable|email|max:255|unique:users,email',
            
            'school_id' => 'required|exists:schools,id',
            'status' => 'required|in:pending,verified,suspended',
        ]);

        $validated['role'] = 'teacher';
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return response()->json(['success' => 'Teacher account created successfully!']);
    }

    /**
     * Load the "Edit Teacher" form
     */
    public function editTeacherPartial($id)
    {
        // Ensure we are only pulling a teacher account
        $teacher = User::where('role', 'teacher')->findOrFail($id);
        $schools = School::orderBy('name', 'asc')->get();

        return view('dashboard.partials.admin.teacher-edit', compact('teacher', 'schools'));
    }

    /**
     * Securely update an existing teacher's details
     */
    public function updateTeacher(Request $request, $id)
    {
        $teacher = User::where('role', 'teacher')->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            
            // Validate unique fields while ignoring the current user's ID
            'username' => 'required|string|max:255|unique:users,username,' . $teacher->id,
            'employee_id' => 'required|string|max:255|unique:users,employee_id,' . $teacher->id,
            
            // Optional Email
            'email' => 'nullable|email|max:255|unique:users,email,' . $teacher->id,
            
            // Password remains optional on update
            'password' => 'nullable|string|min:6',
            
            'school_id' => 'required|exists:schools,id',
            'status' => 'required|in:verified,pending,suspended',
        ]);

        // Check if the admin wants to reset the password
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            // If left blank, remove it so it doesn't overwrite the current password
            unset($validated['password']);
        }

        $teacher->update($validated);

        return response()->json(['success' => 'Teacher updated successfully!']);
    }

    /**
     * Permanently remove a single teacher account
     */
    public function destroyTeacher($id)
    {
        try {
            $teacher = User::where('role', 'teacher')->findOrFail($id);
            $teacher->delete();

            return response()->json([
                'success' => true, 
                'message' => 'Teacher account deleted successfully!'
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            // Catches foreign key constraint violations (e.g., they have uploaded materials)
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete: This educator is linked to existing records (like courses or materials). Please reassign or remove those records first.'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk destroy teacher accounts
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id' // Ensure all IDs actually exist
        ]);

        try {
            // Enforce role check just in case malicious IDs were passed
            User::whereIn('id', $request->ids)->where('role', 'teacher')->delete();

            return response()->json([
                'success' => true,
                'message' => 'The selected educators have been successfully removed.'
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete: One or more selected educators are linked to existing courses or materials. Please clear their data first.'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the CSV template for importing teachers.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Teacher_Import_Template.csv"',
        ];

        // 👈 NEW: Added 'status' to the columns
        $columns = [
            'employee_id', 
            'first_name', 
            'middle_name', 
            'last_name', 
            'suffix', 
            'username', 
            'email', 
            'password', 
            'school_id',
            'status' // <--- Added
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            
            // 1. Add the headers
            fputcsv($file, $columns);
            
            // 2. Add a sample row to guide the user
            fputcsv($file, [
                '1234567', 'Juan', 'Pedro', 'Dela Cruz', '', 
                'juan_teacher', 'juan@deped.gov.ph', 'Teacher123!', 
                '123456', 'verified' // <--- Added
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Handle the Excel/CSV upload and import teachers.
     */
    public function import(\Illuminate\Http\Request $request)
    {
        // Notice we include 'txt' to prevent the CSV plain-text detection bug
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt|max:5120'
        ]);

        try {
            // Process the file using the Maatwebsite Excel package
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\TeachersImport, $request->file('file'));

            return response()->json([
                'success' => true, 
                'message' => 'Educators imported successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Import failed. Check your file format. Error: ' . $e->getMessage()
            ], 500);
        }
    }
}