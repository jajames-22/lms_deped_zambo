<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\School;
use App\Models\Material;
use App\Models\Tag;


class StudentController extends Controller
{
    /**
     * Load the main student directory table
     */
    public function explore()
    {
        $user = auth()->user();
        // Fetch the user's school name, defaulting to 'Your School' if not found
        $userSchoolName = $user->school->name ?? 'Your School';

        // 1. Featured Material
        $featuredMaterial = Material::where('is_public', true)
            ->where('status', 'published')
            ->orderBy('views', 'desc')
            ->first();

        // 2. Logic and Numbers (Filtered by tags)
        $logicMaterials = Material::where('is_public', true)
            ->where('status', 'published')
            ->whereHas('tags', function($q) {
                $q->whereIn('name', ['Mathematics', 'Programming', 'Calculus', 'Algebra']);
            })->get();

        // 3. Popular Materials (Highest views)
        $popularMaterials = Material::where('is_public', true)
            ->where('status', 'published')
            ->orderBy('views', 'desc')
            ->take(10)
            ->get();

        // 4. From Student's School: Materials created by instructors at the SAME school
        $schoolMaterials = Material::where('is_public', true)
            ->where('status', 'published')
            ->whereHas('instructor', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            })->get();

        return view('dashboard.partials.student.explore', compact(
            'featuredMaterial', 
            'logicMaterials', 
            'popularMaterials', 
            'schoolMaterials',
            'userSchoolName'
        ));
    }


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
            
            // 👈 CHANGED: 'user_id' is now 'lrn'
            'lrn' => 'required|string|max:255|unique:users,lrn', 
            
            'email' => 'required|email|max:255|unique:users,email',
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
            
            // 👈 CHANGED: 'user_id' is now 'lrn'
            'lrn' => 'required|string|max:255|unique:users,lrn,' . $student->id,
            
            'email' => 'required|email|max:255|unique:users,email,' . $student->id,
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
}