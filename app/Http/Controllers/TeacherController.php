<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\School;

class TeacherController extends Controller
{
    public function createTeacherPartial()
    {
        // Fetch schools for the dropdown
        $schools = School::orderBy('name', 'asc')->get();
        return view('dashboard.partials.admin.teacher-create', compact('schools'));
    }

    public function storeTeacher(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            // 👈 CHANGED: user_id to employee_id
            'employee_id' => 'required|string|max:255|unique:users,employee_id',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'school_id' => 'required|exists:schools,id',
            'status' => 'required|in:pending,verified,suspended',
        ]);

        $validated['role'] = 'teacher';
        $validated['grade_level'] = null; 
        $validated['password'] = Hash::make($validated['password']); 

        User::create($validated);

        return response()->json(['success' => 'Teacher created successfully!']);
    }

    public function editTeacherPartial($id)
    {
        // Load the teacher and ensure they are actually a teacher
        $teacher = User::where('role', 'teacher')->findOrFail($id);
        
        // Load schools for the dropdown
        $schools = School::orderBy('name', 'asc')->get();

        return view('dashboard.partials.admin.teacher-edit', compact('teacher', 'schools'));
    }

    public function updateTeacher(Request $request, $id)
    {
        $teacher = User::where('role', 'teacher')->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            // 👈 CHANGED: user_id to employee_id and ignored current teacher ID
            'employee_id' => 'required|string|max:255|unique:users,employee_id,' . $teacher->id,
            'email' => 'required|email|max:255|unique:users,email,' . $teacher->id,
            'password' => 'nullable|string|min:6',
            'school_id' => 'required|exists:schools,id',
            'status' => 'required|in:verified,pending,suspended',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $teacher->update($validated);

        return response()->json(['success' => 'Teacher updated successfully!']);
    }

    public function destroyTeacher($id)
    {
        $teacher = User::where('role', 'teacher')->findOrFail($id);
        $teacher->delete();

        return response()->json(['success' => 'Teacher deleted successfully!']);
    }
}