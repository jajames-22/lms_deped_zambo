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
     * Load the "Add New Teacher/CID" form
     */
    public function createTeacherPartial()
    {
        // Fetch schools for the dropdown
        $schools = School::orderBy('name', 'asc')->get();
        return view('dashboard.partials.admin.teacher-create', compact('schools'));
    }

    /**
     * Securely save a new personnel to the database
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
            'role' => 'required|in:teacher,cid', // 👈 NEW: Accept Teacher or CID role
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return response()->json(['success' => 'Personnel account created successfully!']);
    }

    /**
     * Load the "Edit Personnel" form
     */
    public function editTeacherPartial($id)
    {
        // 👈 NEW: Allow pulling both teacher and cid accounts
        $teacher = User::whereIn('role', ['teacher', 'cid'])->findOrFail($id);
        $schools = School::orderBy('name', 'asc')->get();

        return view('dashboard.partials.admin.teacher-edit', compact('teacher', 'schools'));
    }

    /**
     * Securely update an existing personnel's details
     */
    public function updateTeacher(Request $request, $id)
    {
        // 👈 NEW: Allow updating both teacher and cid accounts
        $teacher = User::whereIn('role', ['teacher', 'cid'])->findOrFail($id);

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
            'role' => 'required|in:teacher,cid', // 👈 NEW: Allow role updates
        ]);

        // Check if the admin wants to reset the password
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            // If left blank, remove it so it doesn't overwrite the current password
            unset($validated['password']);
        }

        $teacher->update($validated);

        return response()->json(['success' => 'Personnel updated successfully!']);
    }

    /**
     * Permanently remove a single personnel account
     */
    public function destroyTeacher($id)
    {
        try {
            // 👈 NEW: Allow deleting both teacher and cid accounts
            $teacher = User::whereIn('role', ['teacher', 'cid'])->findOrFail($id);
            $teacher->delete();

            return response()->json([
                'success' => true, 
                'message' => 'Personnel account deleted successfully!'
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
     * Bulk destroy personnel accounts
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id' // Ensure all IDs actually exist
        ]);

        try {
            // 👈 NEW: Enforce role check for both roles
            User::whereIn('id', $request->ids)->whereIn('role', ['teacher', 'cid'])->delete();

            return response()->json([
                'success' => true,
                'message' => 'The selected personnel have been successfully removed.'
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete: One or more selected personnel are linked to existing courses or materials. Please clear their data first.'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the CSV template for importing personnel.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Teacher_Import_Template.csv"',
        ];

        // 👈 REVERTED: Removed the 'role' column to strictly force Teacher imports only
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
            'status' 
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');

            // 1. Add the headers
            fputcsv($file, $columns);

            // 2. Add a sample row to guide the user
            fputcsv($file, [
                '1234567', 'Juan', 'Pedro', 'Dela Cruz', '', 
                'juan_teacher', 'juan@deped.gov.ph', 'Teacher123!', 
                '123456', 'verified' 
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Handle the Excel/CSV upload and import personnel.
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
                'message' => 'Personnel imported successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed. Check your file format. Error: ' . $e->getMessage()
            ], 500);
        }
    }
public function report(Request $request)
    {
        // Start building the query (FIXED: role changed to teacher)
        $query = \App\Models\User::where('role', 'teacher')->with(['school', 'school.district']);
        $titleStatus = '';

        // Filter based on the Modal Checkboxes
        if ($request->has('status_type') && $request->status_type === 'all') {
            // Do nothing, fetch all records
        } elseif ($request->has('statuses') && is_array($request->statuses)) {
            // Filter by the array of selected statuses (e.g., ['verified', 'pending'])
            $query->whereIn('status', $request->statuses);
            
            // Dynamically create the report title
            $formattedStatuses = array_map('ucfirst', $request->statuses);
            $titleStatus = implode(' & ', $formattedStatuses) . ' ';
        }

        // Execute the query
        $teachers = $query->orderBy('last_name', 'asc')->get();

        $data = [
            'title' => $titleStatus . 'Teacher Directory Report', // FIXED Name
            'type' => 'teachers',                                 // FIXED Type
            'records' => $teachers,
            'isPrint' => $request->action === 'print'
        ];

        // Output Print View
        if ($request->action === 'print') {
            return view('dashboard.partials.shared.list-report', $data);
        }

        // Output PDF Download
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.partials.shared.list-report', $data)->setPaper('a4', 'landscape');
        return $pdf->download('Teacher_Directory_' . now()->format('Y_m_d') . '.pdf');
    }
}