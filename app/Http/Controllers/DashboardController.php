<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Assessment;

class DashboardController extends Controller
{
    /**
     * Loads the main dashboard shell (sidebar + topbar) based on role
     */
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

    /**
     * Loads the 'Home' partial for the content area
     */
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

    /*Student Loader*/
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

    public function loadSchoolsPartial()
    {
        if (Auth::user()->role === 'student') {
            return abort(403, 'Unauthorized access.');
        }
        return view('dashboard.partials.admin.schools');
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