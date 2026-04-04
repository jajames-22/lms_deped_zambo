<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\School;
use App\Models\Material;
use App\Models\Tag;
use App\Models\ExplorePageSection;


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

    public function explore()
    {
        // 1. Keep your existing logic for the Hero Banner and Popular rankings
        // Fetch all materials the admin has explicitly marked as featured
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
                
                // Decode the JSON array of tags (fallback to array if it was the old string format)
                $tagsArray = json_decode($section->tag_name, true);
                if (!is_array($tagsArray)) $tagsArray = [$section->tag_name];

                $section->materials = Material::with('instructor')
                    ->whereHas('tags', function($q) use ($tagsArray) {
                        $q->whereIn('name', $tagsArray); // <-- CHANGED to whereIn
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

    public function incrementDownload(Material $material) {
        $material->increment('downloads');
        return response()->json(['success' => true]);
    }
}