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
        // Security check: Prevent unauthorized roles from loading this form
        if (Auth::user()->role === 'student' || Auth::user()->role === 'teacher') {
            abort(403, 'Unauthorized access.');
        }

        // Fetch all quadrants, sorted alphabetically, to populate the dropdown
        $quadrants = Quadrant::orderBy('name', 'asc')->get();

        // Return the view and pass the $quadrants variable to it
        return view('dashboard.partials.admin.school-create', compact('quadrants'));
    }

    public function storeSchool(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|in:elementary,highschool,seniorHighschool,integrated',
            'district_id' => 'required|exists:districts,id',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            // Store logo in storage/app/public/schools
            $path = $request->file('logo')->store('schools', 'public');
            $validated['logo'] = $path;
        }

        \App\Models\School::create($validated);

        // After saving, we return a success response
        return response()->json(['success' => 'School registered successfully!']);
    }

    public function getDistricts($quadrantId)
    {
        // Fetch districts belonging to the selected quadrant
        $districts = \App\Models\District::where('quadrant_id', $quadrantId)
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($districts);
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
        if (Auth::user()->role === 'admin') {
            return view('dashboard.partials.admin.teachers');
        }
        return abort(403, 'Unauthorized access.');
    }

    public function loadAssessmentPartial()
    {
        // 2. FETCH THE ASSESSMENTS FROM THE DATABASE
        // We use 'with("categories")' so we know if it's a "Draft" or "Live" test
        $assessments = Assessment::with('categories')->orderBy('created_at', 'desc')->get();

        // 3. PASS THE DATA TO THE VIEW USING compact()
        return view('dashboard.partials.admin.assessment', compact('assessments'));
    }

    public function loadStudentsPartial()
    {
        if (Auth::user()->role === 'admin') {
            return view('dashboard.partials.admin.students');
        }
        return abort(403, 'Unauthorized access.');
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
}