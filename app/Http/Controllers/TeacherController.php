<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function createTeacherPartial()
    {
        // Fetch schools for the dropdown
        $schools = \App\Models\School::orderBy('name', 'asc')->get();
        return view('dashboard.partials.admin.teacher-create', compact('schools'));
    }

    public function storeTeacher(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'user_id' => 'required|string|max:255|unique:users,user_id',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'school_id' => 'required|exists:schools,id',
            'status' => 'required|in:pending,verified,suspended', // <-- ADD THIS LINE
        ]);

        $validated['role'] = 'teacher';
        $validated['grade_level'] = null; 
        $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']); 

        \App\Models\User::create($validated);

        return response()->json(['success' => 'Teacher created successfully!']);
    }

    public function editTeacherPartial($id)
    {
        // Load the teacher and ensure they are actually a teacher
        $teacher = \App\Models\User::where('role', 'teacher')->findOrFail($id);
        
        // Load schools for the dropdown
        $schools = \App\Models\School::orderBy('name', 'asc')->get();

        return view('dashboard.partials.admin.teacher-edit', compact('teacher', 'schools'));
    }

    public function updateTeacher(\Illuminate\Http\Request $request, $id)
    {
        $teacher = \App\Models\User::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'user_id' => 'required|string|max:255|unique:users,user_id,' . $teacher->id,
            'email' => 'required|email|max:255|unique:users,email,' . $teacher->id,
            'password' => 'nullable|string|min:6',
            'school_id' => 'required|exists:schools,id',
            'status' => 'required|in:verified,pending,suspended', // <-- ADD THIS LINE
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $teacher->update($validated);

        return response()->json(['success' => 'Teacher updated successfully!']);
    }

    public function destroyTeacher($id)
    {
        // Find the user, ensuring they are a teacher so we don't accidentally delete an admin!
        $teacher = \App\Models\User::where('role', 'teacher')->findOrFail($id);

        // Delete the database record
        $teacher->delete();

        return response()->json(['success' => 'Teacher deleted successfully!']);
    }
}
