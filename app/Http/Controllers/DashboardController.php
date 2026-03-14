<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Quadrant;
use App\Models\Assessment;
use App\Models\School;

class DashboardController extends Controller
{

    public function loadSchoolsPartial()
    {
        // Eager load district and quadrant to prevent errors
        $schools = School::with('district.quadrant')->get();

        return view('dashboard.partials.admin.schools', compact('schools'));
    }

    public function loadSchoolCreatePartial()
    {
        try {
            // Fetch data
            $quadrants = \App\Models\Quadrant::orderBy('name', 'asc')->get();

            // Ensure the view path is exactly correct
            return view('dashboard.partials.admin.school-create', [
                'quadrants' => $quadrants
            ]);
        } catch (\Exception $e) {
            // This will log the actual error so you can see it in laravel.log
            \Log::error("Failed to load school create partial: " . $e->getMessage());
            return response()->html("<b>Error:</b> " . $e->getMessage(), 500);
        }
    }

    public function getDistricts($quadrantId)
    {
        // Make sure the model name is correct (District)
        return \App\Models\District::where('quadrant_id', $quadrantId)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function storeSchool(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'school_id' => 'required|unique:schools,school_id',
            'level' => 'required|in:elementary,highschool,seniorHighschool,integrated',
            'district_id' => 'required|exists:districts,id',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');

            // 1. Generate a unique file name (prevents overwriting if two schools upload 'logo.png')
            $filename = time() . '_' . $file->getClientOriginalName();

            // 2. Force the file to move directly into public/storage/schools/
            $file->move(public_path('storage/schools'), $filename);

            // 3. Save the path to the database exactly how your Blade file expects it
            $validated['logo'] = 'schools/' . $filename;
        }

        \App\Models\School::create($validated);

        // After saving, we return a success response
        return response()->json(['success' => 'School registered successfully!']);
    }


    public function index()
    {
        $role = Auth::user()->role;

        if ($role === 'admin' || $role === 'superadmin') {
            return view('dashboard.admin');
        } elseif ($role === 'teacher') {
            return view('dashboard.teacher');
        }

        // Default to student
        return view('dashboard.student');
    }

    public function loadHomePartial()
    {
        $role = Auth::user()->role;

        if ($role === 'admin' || $role === 'superadmin') {
            return view('dashboard.partials.admin.home');
        } elseif ($role === 'teacher') {
            return view('dashboard.partials.teacher.home');
        }

        return view('dashboard.partials.student.home');
    }



    public function loadEnrolledPartial()
    {
        return view('dashboard.partials.student.enrolled');
    }

    public function loadProfilePartial()
    {
        return view('dashboard.partials.shared.profile');
    }

    public function loadAssignmentsPartial()
    {
        return view('dashboard.partials.shared.assignments');
    }

    public function loadMaterialsPartial()
    {
        if (Auth::user()->role === 'admin') {
            return view('dashboard.partials.admin.materials');
        } else if (Auth::user()->role === 'teacher') {
            return view('dashboard.partials.teacher.materials');
        }
    }

    public function loadTeachersPartial()
    {
        // Make sure we only grab users with the role of 'teacher'
        // Eager load the school and district relationships to prevent crashing
        $teachers = \App\Models\User::where('role', 'teacher')
            ->with('school.district')
            ->get();

        return view('dashboard.partials.admin.teachers', compact('teachers'));
    }

    public function loadAssessmentPartial()
    {
        // 2. FETCH THE ASSESSMENTS FROM THE DATABASE
        // We use 'with("categories")' so we know if it's a "Draft" or "Live" test
        $assessments = Assessment::with('categories')->orderBy('created_at', 'desc')->get();

        // 3. PASS THE DATA TO THE VIEW USING compact()
        return view('dashboard.partials.admin.assessments', compact('assessments'));
    }

    public function loadStudentsPartial()
    {
        // Fetch the students from your database. 
        // (Adjust this query depending on if your model is Student or User)
        $students = \App\Models\User::where('role', 'student')->get(); 
        
        // Pass the variable to the view using compact()
        return view('dashboard.partials.admin.students', compact('students'));
    }
    /**
     * Loads the 'Statistics' partial
     */
    public function loadCertificatesPartial()
    {
        // if (Auth::user()->role === 'student') {
        //     abort(403, 'Unauthorized access.');
        // }

        return view('dashboard.partials.student.certificates');
    }

    /**
     * Loads the 'Settings' partial
     */
    public function loadSettingsPartial()
    {
        // Everyone shares the same settings layout
        return view('dashboard.partials.shared.settings');
    }

    public function editSchoolPartial($id)
    {
        $school = \App\Models\School::with('district')->findOrFail($id);
        $quadrants = \App\Models\Quadrant::orderBy('name', 'asc')->get();
        
        // We need to fetch the districts for the quadrant this school is already in
        // so the dropdown isn't empty when the form first loads
        $districts = \App\Models\District::where('quadrant_id', $school->district->quadrant_id ?? null)
                        ->orderBy('name', 'asc')->get();

        return view('dashboard.partials.admin.school-edit', compact('school', 'quadrants', 'districts'));
    }

    public function updateSchool(Request $request, $id)
    {
        $school = \App\Models\School::findOrFail($id);

        $validated = $request->validate([
            // The unique rule ignores THIS school's ID so you don't get a "School ID already taken" error when saving
            'school_id' => 'required|string|max:255|unique:schools,school_id,' . $school->id, 
            'name' => 'required|string|max:255',
            'level' => 'required|in:elementary,highschool,seniorhighschool,integrated',
            'district_id' => 'required|exists:districts,id',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/schools'), $filename);
            $validated['logo'] = 'schools/' . $filename;
            
            // Optional: You could delete the old image file here if you want to save server space
        }

        $school->update($validated);

        return response()->json(['success' => 'School updated successfully!']);
    }

    public function destroySchool($id)
{
    $school = \App\Models\School::findOrFail($id);

    // Delete the logo file from the public/storage folder if it exists
    if ($school->logo) {
        $logoPath = public_path('storage/' . $school->logo);
        if (file_exists($logoPath)) {
            unlink($logoPath); // This deletes the physical file
        }
    }

    // Delete the database record
    $school->delete();

    return response()->json(['success' => 'School deleted successfully!']);
}

}