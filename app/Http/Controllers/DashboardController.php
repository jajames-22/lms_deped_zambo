<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Quadrant;
use App\Models\Assessment;
use App\Models\School;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\User;
use App\Models\Material;
use Illuminate\Support\Facades\Storage;

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

    public function report(Request $request)
    {
        $schools = \App\Models\School::with('district')->orderBy('name', 'asc')->get();

        $data = [
            'title' => 'School Directory Report',
            'type' => 'schools',
            'records' => $schools,
            'isPrint' => $request->action === 'print'
        ];

        if ($request->action === 'print') {
            return view('dashboard.partials.shared.list-report', $data);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.partials.shared.list-report', $data);
        return $pdf->download('School_Directory_' . now()->format('Y_m_d') . '.pdf');
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

        if ($role === 'admin') {
            return view('dashboard.admin');
        } elseif ($role === 'teacher') {
            return view('dashboard.teacher');
        } elseif ($role === 'cid') {
            return view('dashboard.cid');
        }

        // Default to student
        return view('dashboard.student');
    }

    public function loadHomePartial()
    {
        $role = Auth::user()->role;

        // 1. ADMIN & CID LOGIC
        if ($role === 'admin') {

            // --- A. BASE PLATFORM METRICS ---
            $totalStudents = User::where('role', 'student')->count();
            $totalTeachers = User::where('role', 'teacher')->count();
            $totalSchools = School::count();

            $totalMaterials = Material::count();
            $totalAssessments = Assessment::count();

            // --- B. ENGAGEMENT & ADOPTION (CHART DATA) ---
            $dailyActiveUsers = User::where('updated_at', '>=', now()->subDay())->count();
            $weeklyActiveUsers = User::where('updated_at', '>=', now()->subDays(7))->count();

            // 7-Day Activity Trend for Line Chart
            $activityDates = [];
            $activityTrend = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $activityDates[] = now()->subDays($i)->format('M d');
                $activityTrend[] = User::whereDate('updated_at', $date)->count();
            }

            // Top 5 Schools for Bar Chart
            $topSchoolsRaw = User::where('role', 'student')
                ->whereNotNull('school_id')
                ->selectRaw('school_id, count(*) as count')
                ->groupBy('school_id')
                ->orderBy('count', 'desc')
                ->take(5)
                ->get();

            $topSchoolLabels = [];
            $topSchoolData = [];
            if ($topSchoolsRaw->isNotEmpty()) {
                $schoolIds = $topSchoolsRaw->pluck('school_id');
                $schoolsMapping = School::whereIn('id', $schoolIds)->get()->keyBy('id');

                foreach ($topSchoolsRaw as $ts) {
                    if (isset($schoolsMapping[$ts->school_id])) {
                        $topSchoolLabels[] = \Illuminate\Support\Str::limit($schoolsMapping[$ts->school_id]->name, 15);
                        $topSchoolData[] = $ts->count;
                    }
                }
            }

            // Top 5 Materials by Views for Doughnut Chart
            $topMaterialsRaw = Material::orderBy('views', 'desc')->take(5)->get();
            $topMaterialsLabels = [];
            $topMaterialsData = [];
            foreach ($topMaterialsRaw as $mat) {
                $topMaterialsLabels[] = \Illuminate\Support\Str::limit($mat->title, 15);
                $topMaterialsData[] = $mat->views;
            }

            // --- C. SYSTEM HEALTH & STORAGE ---
            $storagePath = storage_path();
            $freeSpace = function_exists('disk_free_space') ? @disk_free_space($storagePath) : 0;
            $totalSpace = function_exists('disk_total_space') ? @disk_total_space($storagePath) : 1;
            $usedSpace = $totalSpace - $freeSpace;
            $storagePercentage = round(($usedSpace / $totalSpace) * 100);
            $usedGb = round($usedSpace / 1073741824, 1);
            $totalGb = round($totalSpace / 1073741824, 1);

            // --- D. LEARNING OUTCOMES ---
            $certificatesIssued = \App\Models\Enrollment::where('status', 'completed')->count();
            $totalEnrollments = \App\Models\Enrollment::count();
            $completionRate = $totalEnrollments > 0 ? round(($certificatesIssued / $totalEnrollments) * 100) : 0;

            $totalExamAnswers = \App\Models\ExamAnswer::count();
            $correctExamAnswers = \App\Models\ExamAnswer::where('is_correct', true)->count();
            $avgLearnerSuccessRate = $totalExamAnswers > 0 ? round(($correctExamAnswers / $totalExamAnswers) * 100, 1) : 0;

            // --- E. ACTIONABLE ALERTS ---
            $pendingTeachersCount = User::where('role', 'teacher')->where('status', 'pending')->count();
            $pendingStudentsCount = User::where('role', 'student')->where('status', 'pending')->count();
            $unassignedUsersCount = User::whereNull('school_id')->whereIn('role', ['teacher', 'student'])->count();

            return view('dashboard.partials.admin.home', compact(
                'totalStudents',
                'totalTeachers',
                'totalSchools',
                'totalMaterials',
                'totalAssessments',
                'dailyActiveUsers',
                'weeklyActiveUsers',
                'activityDates',
                'activityTrend',
                'topSchoolLabels',
                'topSchoolData',
                'topMaterialsLabels',
                'topMaterialsData',
                'storagePercentage',
                'usedGb',
                'totalGb',
                'avgLearnerSuccessRate',
                'completionRate',
                'certificatesIssued',
                'pendingTeachersCount',
                'pendingStudentsCount',
                'unassignedUsersCount'
            ));
        } elseif ($role === 'cid') {
            // --- A. TOP METRICS ---
            $pendingMaterials = \App\Models\Material::where('status', 'pending')->count();
            $publishedMaterials = \App\Models\Material::where('status', 'published')->count();
            $activeTeachers = \App\Models\User::where('role', 'teacher')->where('status', 'verified')->count();

            $totalExamAnswers = \App\Models\ExamAnswer::count();
            $correctExamAnswers = \App\Models\ExamAnswer::where('is_correct', true)->count();
            $averageScore = $totalExamAnswers > 0 ? round(($correctExamAnswers / $totalExamAnswers) * 100, 1) : 0;

            // --- B. RECENT EVALUATIONS ---
            $recentEvaluations = \App\Models\Material::with('instructor')
                ->whereIn('status', ['published', 'draft', 'pending'])
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->get();

            // --- C. CHART DATA: MASTERY TREND (Last 6 Months) ---
            $masteryLabels = [];
            $masteryData = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $masteryLabels[] = $month->format('M');

                $monthAnswers = \App\Models\ExamAnswer::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year);

                $tot = $monthAnswers->count();
                $cor = (clone $monthAnswers)->where('is_correct', true)->count();
                $masteryData[] = $tot > 0 ? round(($cor / $tot) * 100) : 0;
            }

            // --- D. CHART DATA: MATERIAL TYPES (By Status) ---
            $materialTypeLabels = ['Published', 'Pending Review', 'Drafts'];
            $materialTypeData = [
                $publishedMaterials,
                $pendingMaterials,
                \App\Models\Material::where('status', 'draft')->count()
            ];

            // --- E. CHART DATA: TOP SCHOOLS (By Enrollment Volume) ---
            $topSchoolsRaw = \App\Models\User::where('role', 'student')
                ->whereNotNull('school_id')
                ->selectRaw('school_id, count(*) as count')
                ->groupBy('school_id')
                ->orderBy('count', 'desc')
                ->take(3)
                ->get();

            $topSchoolLabels = [];
            $topSchoolData = [];
            if ($topSchoolsRaw->isNotEmpty()) {
                $schoolIds = $topSchoolsRaw->pluck('school_id');
                $schoolsMapping = \App\Models\School::whereIn('id', $schoolIds)->get()->keyBy('id');

                foreach ($topSchoolsRaw as $ts) {
                    if (isset($schoolsMapping[$ts->school_id])) {
                        $topSchoolLabels[] = \Illuminate\Support\Str::limit($schoolsMapping[$ts->school_id]->name, 15);
                        $topSchoolData[] = $ts->count;
                    }
                }
            }

            return view('dashboard.partials.cid.home', compact(
                'pendingMaterials',
                'publishedMaterials',
                'activeTeachers',
                'averageScore',
                'recentEvaluations',
                'masteryLabels',
                'masteryData',
                'materialTypeLabels',
                'materialTypeData',
                'topSchoolLabels',
                'topSchoolData'
            ));
        }

        // 3. DYNAMIC TEACHER LOGIC (Module Creator Focus)
        elseif ($role === 'teacher') {
            $teacherId = Auth::id();

            // --- A. CORE MODULE METRICS ---
            $myMaterialsCount = \App\Models\Material::where('instructor_id', $teacherId)->count();
            $totalViews = \App\Models\Material::where('instructor_id', $teacherId)->sum('views');

            // Most Popular Module
            $topModule = \App\Models\Material::where('instructor_id', $teacherId)
                ->orderBy('views', 'desc')
                ->first();

            // Total unique students across all the teacher's modules
            $totalLearners = \App\Models\Enrollment::whereIn('material_id', function ($query) use ($teacherId) {
                $query->select('id')->from('materials')->where('instructor_id', $teacherId);
            })->distinct('user_id')->count();

            // --- B. PERFORMANCE & EXAM PASSING RATE ---
            // Find all exam IDs belonging to this teacher's materials
            $teacherExamIds = \App\Models\Exam::whereIn('material_id', function ($query) use ($teacherId) {
                $query->select('id')->from('materials')->where('instructor_id', $teacherId);
            })->pluck('id');

            $totalTeacherExamAnswers = \App\Models\ExamAnswer::whereIn('exam_id', $teacherExamIds)->count();
            $correctTeacherExamAnswers = \App\Models\ExamAnswer::whereIn('exam_id', $teacherExamIds)
                ->where('is_correct', true)->count();

            $examPassingRate = $totalTeacherExamAnswers > 0
                ? round(($correctTeacherExamAnswers / $totalTeacherExamAnswers) * 100, 1)
                : 0;

            // --- C. MODULE COMPLETION RATE ---
            $completedMyModules = \App\Models\Enrollment::where('status', 'completed')
                ->whereIn('material_id', function ($q) use ($teacherId) {
                    $q->select('id')->from('materials')->where('instructor_id', $teacherId);
                })->count();

            $totalMyEnrollments = \App\Models\Enrollment::whereIn('material_id', function ($q) use ($teacherId) {
                $q->select('id')->from('materials')->where('instructor_id', $teacherId);
            })->count();

            $moduleCompletionRate = $totalMyEnrollments > 0
                ? round(($completedMyModules / $totalMyEnrollments) * 100)
                : 0;

            // --- D. CHART DATA: MODULE ENGAGEMENT TREND ---
            // Tracks *New Enrollments* into your modules over the last 7 days
            $activityDates = [];
            $activityTrend = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $activityDates[] = now()->subDays($i)->format('M d');
                $activityTrend[] = \App\Models\Enrollment::whereDate('created_at', $date)
                    ->whereIn('material_id', function ($q) use ($teacherId) {
                        $q->select('id')->from('materials')->where('instructor_id', $teacherId);
                    })->count();
            }

            $topMaterialsRaw = \App\Models\Material::where('instructor_id', $teacherId)
                ->orderBy('views', 'desc')->take(5)->get();
            $topMaterialsLabels = $topMaterialsRaw->pluck('title')->map(fn($t) => \Illuminate\Support\Str::limit($t, 15))->toArray();
            $topMaterialsData = $topMaterialsRaw->pluck('views')->toArray();

            // --- E. LISTS: MOST ACTIVE STUDENTS & PENDING INVITES ---
            // Pending Invites with explicit Module Context
            $pendingInvitesQuery = \App\Models\MaterialAccess::with('material')
                ->where('status', 'pending')
                ->whereIn('material_id', function ($q) use ($teacherId) {
                    $q->select('id')->from('materials')->where('instructor_id', $teacherId);
                });

            $pendingInvitesCount = $pendingInvitesQuery->count();
            $pendingInvitesList = $pendingInvitesQuery->latest()->take(5)->get();

            // Most Active/Recent Students interacting with your modules
            $activeStudentsList = \App\Models\Enrollment::with(['user', 'material'])
                ->whereIn('material_id', function ($q) use ($teacherId) {
                    $q->select('id')->from('materials')->where('instructor_id', $teacherId);
                })
                ->orderBy('updated_at', 'desc')
                ->take(10)->get()->unique('user_id')->take(5); // Get top 5 unique recent users

            return view('dashboard.partials.teacher.home', compact(
                'myMaterialsCount',
                'totalViews',
                'topModule',
                'totalLearners',
                'examPassingRate',
                'moduleCompletionRate',
                'completedMyModules',
                'activityDates',
                'activityTrend',
                'topMaterialsLabels',
                'topMaterialsData',
                'pendingInvitesCount',
                'pendingInvitesList',
                'activeStudentsList'
            ));
        }

        // 3. STUDENT LOGIC
        else {
            $studentId = Auth::id();

            // --- A. AT-A-GLANCE STATISTICS ---
            $totalEnrollments = \App\Models\Enrollment::where('user_id', $studentId)->count();

            $completedModulesCount = \App\Models\Enrollment::where('user_id', $studentId)
                ->where('status', 'completed')
                ->count();

            $activeModulesCount = \App\Models\Enrollment::where('user_id', $studentId)
                ->where(function ($q) {
                    $q->where('status', 'in_progress')->orWhereNull('status');
                })->count();

            // Calculate personal Average Exam Score
            $totalAnswers = \App\Models\ExamAnswer::where('user_id', $studentId)->count();
            $correctAnswers = \App\Models\ExamAnswer::where('user_id', $studentId)
                ->where('is_correct', true)->count();

            $averageExamScore = $totalAnswers > 0
                ? round(($correctAnswers / $totalAnswers) * 100, 1)
                : 0;

            // Overall Progress for the Welcome Banner
            $overallProgress = $totalEnrollments > 0
                ? round(($completedModulesCount / $totalEnrollments) * 100)
                : 0;

            // --- B. CONTINUE LEARNING ---
            // Fetch up to 2 modules the student recently interacted with but hasn't finished
            $continueLearning = \App\Models\Enrollment::with('material')
                ->where('user_id', $studentId)
                ->where('status', '!=', 'completed')
                ->orderBy('updated_at', 'desc')
                ->take(2)
                ->get();

            // --- C. ACTION REQUIRED / UPCOMING ---
            // Fetch the newest published district exams available to take
            $availableAssessments = \App\Models\Assessment::where('status', 'published')
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();

            return view('dashboard.partials.student.home', compact(
                'activeModulesCount',
                'completedModulesCount',
                'averageExamScore',
                'overallProgress',
                'continueLearning',
                'availableAssessments'
            ));
        }
    }


    public function loadEnrolledPartial()
    {
        return view('dashboard.partials.student.enrolled');
    }

    public function loadProfilePartial()
    {
        $schools = \App\Models\School::orderBy('name', 'asc')->get();
        return view('dashboard.partials.admin.profile', compact('schools'));
    }

    public function loadAssignmentsPartial()
    {
        return view('dashboard.partials.shared.assignments');
    }

    public function loadMaterialsPartial()
    {
        $role = Auth::user()->role;

        if (in_array($role, ['admin', 'cid'])) {
            return view('dashboard.partials.admin.materials');
        } else if ($role === 'teacher') {
            return view('dashboard.partials.teacher.materials');
        }
    }

    public function loadTeachersPartial()
    {
        // Make sure we only grab users with the role of 'teacher'
        // Eager load the school and district relationships to prevent crashing
        $teachers = \App\Models\User::whereIn('role', ['teacher', 'cid'])
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

    public function loadExplorePartial(\Illuminate\Http\Request $request)
    {
        // 1. Featured Materials
        $featuredMaterials = \App\Models\Material::with('instructor')
            ->where('is_featured', true)
            ->where('status', 'published')
            ->where('is_public', true)
            ->latest()
            ->get();

        // 2. Popular Materials
        $popularMaterials = \App\Models\Material::with('instructor')
            ->where('status', 'published')
            ->where('is_public', true)
            ->orderBy('views', 'desc')
            ->take(10)
            ->get();

        // 3. Dynamic Sections
        $dynamicSections = \App\Models\ExplorePageSection::where('is_active', true)
            ->orderBy('order', 'asc')
            ->get()
            ->map(function ($section) {
                // Decode the JSON array of tags
                $tagsArray = json_decode($section->tag_name, true);
                if (!is_array($tagsArray))
                    $tagsArray = [$section->tag_name];

                $section->materials = \App\Models\Material::with('instructor')
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

        // 4. School Materials
        // Since this route is for public guests, they don't have a school. 
        // We pass an empty collection so the Blade file doesn't throw an "undefined variable" error.
        $schoolMaterials = collect();

        return view('dashboard.partials.student.explore', compact(
            'featuredMaterials',
            'popularMaterials',
            'dynamicSections',
            'schoolMaterials'
        ));
    }

    public function publicExplore()
    {
        // 1. Featured Materials
        $featuredMaterials = \App\Models\Material::with('instructor')
            ->where('is_featured', true)
            ->where('status', 'published')
            ->where('is_public', true)
            ->latest()
            ->get();

        // 2. Popular Materials
        $popularMaterials = \App\Models\Material::with('instructor')
            ->where('status', 'published')
            ->where('is_public', true)
            ->orderBy('views', 'desc')
            ->take(10)
            ->get();

        // 3. Dynamic Sections
        $dynamicSections = \App\Models\ExplorePageSection::where('is_active', true)
            ->orderBy('order', 'asc')
            ->get()
            ->map(function ($section) {
                $tagsArray = json_decode($section->tag_name, true);
                if (!is_array($tagsArray))
                    $tagsArray = [$section->tag_name];

                $section->materials = \App\Models\Material::with('instructor')
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

        return view('explore-public', compact('featuredMaterials', 'popularMaterials', 'dynamicSections'));
    }

    public function viewByTagJson($tag)
    {
        // 1. Decode the input. It might be a single string or a JSON array.
        $decodedTags = json_decode(urldecode($tag), true);

        // If it's not JSON (just a single string), put it into an array
        $searchTags = is_array($decodedTags) ? $decodedTags : [trim(urldecode($tag))];

        // 2. Query materials
        $materials = \App\Models\Material::with('instructor')
            ->where('status', 'published')
            ->where('is_public', true)
            ->whereHas('tags', function ($query) use ($searchTags) {
                // Search for materials matching ANY of the tags in the list
                $query->whereIn('name', $searchTags);

                // Fallback: Check if any tag IDs were passed
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

    public function loadAnalyticsPartial()
    {
        $role = Auth::user()->role;

        if (in_array($role, ['admin', 'cid'])) {
            return $this->loadAdminAnalytics();
        } elseif ($role === 'teacher') {
            return $this->loadTeacherAnalytics();
        }

        return $this->loadStudentAnalytics();
    }


    public function publicMaterialShow($hashid)
    {
        
        $decoded = Hashids::decode($hashid);
        // If the hash is invalid or tampered with, throw a 404
        if (empty($decoded)) {
            abort(404, 'Invalid material link.');
        }
        $id = $decoded[0];
    
        // Fetch the material, ensuring it is public and published
        $material = \App\Models\Material::with(['instructor', 'tags', 'lessons.contents', 'exams'])
            ->where('is_public', true)
            ->where('status', 'published')
            ->findOrFail($id);

        // Point straight to the new standalone view in the root resources/views folder
        // Note: No need to pass $isEnrolled anymore, as it's hardcoded for guests!
        return view('materials-show-public', compact('material'));
    }

    private function loadAdminAnalytics()
    {
        // ==========================================
        // 1. USER & DEMOGRAPHICS
        // ==========================================
        $totalStudents = \App\Models\User::where('role', 'student')->count();
        $totalTeachers = \App\Models\User::where('role', 'teacher')->count();
        $totalSchools = \App\Models\School::count();

        $dailyActiveUsers = \App\Models\User::where('updated_at', '>=', now()->subDay())->count();
        $weeklyActiveUsers = \App\Models\User::where('updated_at', '>=', now()->subDays(7))->count();

        // Top 5 Schools by Student Count
        $topSchoolsRaw = \App\Models\User::where('role', 'student')
            ->whereNotNull('school_id')
            ->selectRaw('school_id, count(*) as count')
            ->groupBy('school_id')
            ->orderBy('count', 'desc')
            ->take(5)
            ->with('school')
            ->get();

        $schoolLabels = [];
        $schoolData = [];
        foreach ($topSchoolsRaw as $ts) {
            if ($ts->school) {
                $schoolLabels[] = \Illuminate\Support\Str::limit($ts->school->name, 20);
                $schoolData[] = $ts->count;
            }
        }

        // ==========================================
        // 2. CONTENT & ENGAGEMENT
        // ==========================================
        $totalMaterials = \App\Models\Material::count();
        $certificatesIssued = \App\Models\Enrollment::where('status', 'completed')->count();
        $totalEnrollments = \App\Models\Enrollment::count();

        $completionRate = $totalEnrollments > 0 ? round(($certificatesIssued / $totalEnrollments) * 100) : 0;

        // Top Materials by Views
        $topMaterialsRaw = \App\Models\Material::orderBy('views', 'desc')->take(5)->get();
        $topMaterialsLabels = [];
        $topMaterialsData = [];
        foreach ($topMaterialsRaw as $mat) {
            $topMaterialsLabels[] = \Illuminate\Support\Str::limit($mat->title, 20);
            $topMaterialsData[] = $mat->views;
        }

        // ==========================================
        // 3. ASSESSMENT & PERFORMANCE
        // ==========================================
        $totalAssessments = \App\Models\Assessment::count();
        $totalExamAnswers = \App\Models\ExamAnswer::count();
        $correctExamAnswers = \App\Models\ExamAnswer::where('is_correct', true)->count();

        $globalSuccessRate = $totalExamAnswers > 0 ? round(($correctExamAnswers / $totalExamAnswers) * 100, 1) : 0;

        // ==========================================
        // 4. SYSTEM HEALTH & RESOURCES
        // ==========================================
        $storagePath = storage_path();
        $freeSpace = function_exists('disk_free_space') ? @disk_free_space($storagePath) : 0;
        $totalSpace = function_exists('disk_total_space') ? @disk_total_space($storagePath) : 1;
        $usedSpace = $totalSpace - $freeSpace;

        $storagePercentage = round(($usedSpace / $totalSpace) * 100);
        $usedGb = round($usedSpace / 1073741824, 1);
        $totalGb = round($totalSpace / 1073741824, 1);

        return view('dashboard.partials.admin.analytics', compact(
            'totalStudents',
            'totalTeachers',
            'totalSchools',
            'dailyActiveUsers',
            'weeklyActiveUsers',
            'schoolLabels',
            'schoolData',
            'totalMaterials',
            'completionRate',
            'totalEnrollments',
            'topMaterialsLabels',
            'topMaterialsData',
            'totalAssessments',
            'globalSuccessRate',
            'storagePercentage',
            'usedGb',
            'totalGb'
        ));
    }

    public function exportAdminAnalyticsPdf(Request $request)
    {
        // 1. Ensure only admins/cid can download this
        if (!in_array(\Illuminate\Support\Facades\Auth::user()->role, ['admin', 'cid'])) {
            abort(403, 'Unauthorized action.');
        }

        // 2. Fetch the User & Demographics data
        $totalStudents = \App\Models\User::where('role', 'student')->count();
        $totalTeachers = \App\Models\User::where('role', 'teacher')->count();
        $totalSchools = \App\Models\School::count();

        $dailyActiveUsers = \App\Models\User::where('updated_at', '>=', now()->subDay())->count();
        $weeklyActiveUsers = \App\Models\User::where('updated_at', '>=', now()->subDays(7))->count();

        // Fetch Top Schools Data (For the PDF Table)
        $topSchoolsRaw = \App\Models\User::where('role', 'student')
            ->whereNotNull('school_id')
            ->selectRaw('school_id, count(*) as count')
            ->groupBy('school_id')
            ->orderBy('count', 'desc')
            ->take(5)
            ->with('school')
            ->get();

        $topSchools = [];
        foreach ($topSchoolsRaw as $ts) {
            if ($ts->school) {
                $topSchools[] = [
                    'name' => $ts->school->name,
                    'count' => $ts->count
                ];
            }
        }

        // 3. Fetch the Content & Engagement data
        $totalMaterials = \App\Models\Material::count();
        $totalEnrollments = \App\Models\Enrollment::count();
        $certificatesIssued = \App\Models\Enrollment::where('status', 'completed')->count();
        $completionRate = $totalEnrollments > 0 ? round(($certificatesIssued / $totalEnrollments) * 100) : 0;

        // Fetch Top Materials Data (For the PDF Table)
        $topMaterials = \App\Models\Material::orderBy('views', 'desc')->take(5)->get();

        // 4. Fetch the System Health & Resources data
        $storagePath = storage_path();
        $freeSpace = function_exists('disk_free_space') ? @disk_free_space($storagePath) : 0;
        $totalSpace = function_exists('disk_total_space') ? @disk_total_space($storagePath) : 1;
        $usedSpace = $totalSpace - $freeSpace;

        $storagePercentage = round(($usedSpace / $totalSpace) * 100);
        $usedGb = round($usedSpace / 1073741824, 1);
        $totalGb = round($totalSpace / 1073741824, 1);

        // 5. Bundle it all together for the view
        $isPrint = $request->input('action') === 'print';

        $data = [
            'totalStudents' => $totalStudents,
            'totalTeachers' => $totalTeachers,
            'totalSchools' => $totalSchools,
            'dailyActiveUsers' => $dailyActiveUsers,
            'weeklyActiveUsers' => $weeklyActiveUsers,
            'topSchools' => $topSchools,

            'totalMaterials' => $totalMaterials,
            'totalEnrollments' => $totalEnrollments,
            'completionRate' => $completionRate,
            'topMaterials' => $topMaterials,

            'totalGb' => $totalGb,
            'usedGb' => $usedGb,
            'storagePercentage' => $storagePercentage,

            'showUsers' => $request->has('check_users'),
            'showContent' => $request->has('check_content'),
            'showHealth' => $request->has('check_health'),

            'isPrint' => $isPrint,
        ];

        // 6. IF PRINT: Return the HTML view directly for browser printing
        if ($isPrint) {
            return view('dashboard.partials.admin.analytics-report', $data);
        }

        // 7. IF PDF: Generate and Download using DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.partials.admin.analytics-report', $data);
        return $pdf->download('Admin_Analytics_Report_' . now()->format('Y_m_d') . '.pdf');
    }

    private function loadTeacherAnalytics()
    {
        $teacherId = Auth::id();

        // 1. CLASS OVERVIEW
        $myMaterialIds = \App\Models\Material::where('instructor_id', $teacherId)->pluck('id');

        $totalLearners = \App\Models\Enrollment::whereIn('material_id', $myMaterialIds)
            ->distinct('user_id')
            ->count();

        $activeLearners = \App\Models\Enrollment::whereIn('material_id', $myMaterialIds)
            ->where('updated_at', '>=', now()->subDays(7))
            ->distinct('user_id')
            ->count();

        $pendingRequests = \App\Models\MaterialAccess::whereIn('material_id', $myMaterialIds)
            ->where('status', 'pending')
            ->count();

        // 2. MATERIAL ENGAGEMENT
        $totalMaterials = $myMaterialIds->count();
        $totalViews = \App\Models\Material::where('instructor_id', $teacherId)->sum('views');

        $topMaterials = \App\Models\Material::where('instructor_id', $teacherId)
            ->orderBy('views', 'desc')
            ->take(5)
            ->get();

        $materialLabels = $topMaterials->pluck('title')->map(fn($t) => \Illuminate\Support\Str::limit($t, 20))->toArray();
        $materialViews = $topMaterials->pluck('views')->toArray();

        $completedCount = \App\Models\Enrollment::whereIn('material_id', $myMaterialIds)->where('status', 'completed')->count();
        $inProgressCount = \App\Models\Enrollment::whereIn('material_id', $myMaterialIds)->where('status', 'in_progress')->count();

        // 3. ASSESSMENT & PERFORMANCE
        $teacherExamIds = \App\Models\Exam::whereIn('material_id', $myMaterialIds)->pluck('id');
        $totalAnswers = \App\Models\ExamAnswer::whereIn('exam_id', $teacherExamIds)->count();
        $correctAnswers = \App\Models\ExamAnswer::whereIn('exam_id', $teacherExamIds)->where('is_correct', true)->count();

        $averageScore = $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 1) : 0;
        $incorrectAnswers = $totalAnswers - $correctAnswers;

        // 4. RECENT ACTIVITY TREND (Last 7 days of Enrollments)
        $activityDates = [];
        $activityTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $activityDates[] = now()->subDays($i)->format('M d');
            $activityTrend[] = \App\Models\Enrollment::whereDate('created_at', $date)
                ->whereIn('material_id', $myMaterialIds)
                ->count();
        }

        return view('dashboard.partials.teacher.analytics', compact(
            'totalLearners',
            'activeLearners',
            'pendingRequests',
            'totalMaterials',
            'totalViews',
            'materialLabels',
            'materialViews',
            'completedCount',
            'inProgressCount',
            'averageScore',
            'correctAnswers',
            'incorrectAnswers',
            'activityDates',
            'activityTrend'
        ));
    }

    public function exportTeacherAnalyticsPdf(Request $request)
    {
        // 1. Ensure only teachers can download this
        if (\Illuminate\Support\Facades\Auth::user()->role !== 'teacher') {
            abort(403, 'Unauthorized action.');
        }

        $teacherId = \Illuminate\Support\Facades\Auth::id();

        // 2. CLASS OVERVIEW
        $myMaterialIds = \App\Models\Material::where('instructor_id', $teacherId)->pluck('id');

        $totalLearners = \App\Models\Enrollment::whereIn('material_id', $myMaterialIds)
            ->distinct('user_id')
            ->count();

        $activeLearners = \App\Models\Enrollment::whereIn('material_id', $myMaterialIds)
            ->where('updated_at', '>=', now()->subDays(7))
            ->distinct('user_id')
            ->count();

        $pendingRequests = \App\Models\MaterialAccess::whereIn('material_id', $myMaterialIds)
            ->where('status', 'pending')
            ->count();

        // 3. MATERIAL ENGAGEMENT
        $totalMaterials = $myMaterialIds->count();
        $totalViews = \App\Models\Material::where('instructor_id', $teacherId)->sum('views');

        $topMaterials = \App\Models\Material::where('instructor_id', $teacherId)
            ->orderBy('views', 'desc')
            ->take(5)
            ->get();

        $completedCount = \App\Models\Enrollment::whereIn('material_id', $myMaterialIds)->where('status', 'completed')->count();
        $inProgressCount = \App\Models\Enrollment::whereIn('material_id', $myMaterialIds)->where('status', 'in_progress')->count();

        // 4. ASSESSMENT & PERFORMANCE
        $teacherExamIds = \App\Models\Exam::whereIn('material_id', $myMaterialIds)->pluck('id');
        $totalAnswers = \App\Models\ExamAnswer::whereIn('exam_id', $teacherExamIds)->count();
        $correctAnswers = \App\Models\ExamAnswer::whereIn('exam_id', $teacherExamIds)->where('is_correct', true)->count();

        $averageScore = $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 1) : 0;
        $incorrectAnswers = $totalAnswers - $correctAnswers;

        // 5. RECENT ACTIVITY TREND (Tabular Format for PDF)
        $activityTrends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $displayDate = now()->subDays($i)->format('M d, Y');
            $count = \App\Models\Enrollment::whereDate('created_at', $date)
                ->whereIn('material_id', $myMaterialIds)
                ->count();

            $activityTrends[] = [
                'date' => $displayDate,
                'count' => $count
            ];
        }

        // 6. Bundle it all together
        $isPrint = $request->input('action') === 'print';

        $data = [
            'totalLearners' => $totalLearners,
            'activeLearners' => $activeLearners,
            'pendingRequests' => $pendingRequests,
            'totalMaterials' => $totalMaterials,
            'totalViews' => $totalViews,
            'topMaterials' => $topMaterials,
            'completedCount' => $completedCount,
            'inProgressCount' => $inProgressCount,
            'averageScore' => $averageScore,
            'correctAnswers' => $correctAnswers,
            'incorrectAnswers' => $incorrectAnswers,
            'activityTrends' => $activityTrends,

            // Filter checkboxes from the UI Modal
            'showOverview' => $request->has('check_overview'),
            'showEngagement' => $request->has('check_engagement'),
            'showPerformance' => $request->has('check_performance'),
            'showTrends' => $request->has('check_trends'),

            'isPrint' => $isPrint,
        ];

        // 7. IF PRINT: Return HTML directly. IF PDF: Download DomPDF
        if ($isPrint) {
            return view('dashboard.partials.teacher.analytics-report', $data);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.partials.teacher.analytics-report', $data);
        return $pdf->download('Class_Analytics_Report_' . now()->format('Y_m_d') . '.pdf');
    }

    private function loadStudentAnalytics()
    {
        $studentId = \Illuminate\Support\Facades\Auth::id();

        // 1. ACHIEVEMENTS & PROGRESS
        $totalEnrollments = \App\Models\Enrollment::where('user_id', $studentId)->count();
        $completedCount = \App\Models\Enrollment::where('user_id', $studentId)->where('status', 'completed')->count();
        $inProgressCount = \App\Models\Enrollment::where('user_id', $studentId)->where('status', 'in_progress')->count();

        $completionRate = $totalEnrollments > 0 ? round(($completedCount / $totalEnrollments) * 100) : 0;

        // NEW: Total Hours Learned (Sum of lesson time_limits from completed modules)
        $totalMinutes = \Illuminate\Support\Facades\DB::table('enrollments')
            ->where('enrollments.user_id', $studentId)
            ->where('enrollments.status', 'completed')
            ->join('lessons', 'enrollments.material_id', '=', 'lessons.material_id')
            ->sum('lessons.time_limit');

        $totalHours = round($totalMinutes / 60, 1);

        // 2. ALL-TIME QUIZ PERFORMANCE (Kept for accuracy charts)
        $totalAnswers = \App\Models\ExamAnswer::where('user_id', $studentId)->count();
        $correctAnswers = \App\Models\ExamAnswer::where('user_id', $studentId)->where('is_correct', true)->count();
        $incorrectAnswers = $totalAnswers - $correctAnswers;

        // 3. RECENT QUIZ TREND (Last 7 Days)
        $examDates = [];
        $examScores = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $examDates[] = now()->subDays($i)->format('M d');

            $dailyScore = \App\Models\ExamAnswer::where('user_id', $studentId)
                ->whereDate('created_at', $date)
                ->where('is_correct', true)
                ->count();

            $examScores[] = $dailyScore;
        }

        // 4. LEARNING STREAK (Consecutive days of activity)
        $streak = 0;
        $checkDate = now();

        while (true) {
            $hasActivity = \App\Models\ExamAnswer::where('user_id', $studentId)
                ->whereDate('created_at', $checkDate->format('Y-m-d'))
                ->exists() ||
                \App\Models\Enrollment::where('user_id', $studentId)
                    ->whereDate('updated_at', $checkDate->format('Y-m-d'))
                    ->exists();

            if ($hasActivity) {
                $streak++;
                $checkDate->subDay();
            } else {
                // If checking today and no activity, check if they had a streak going up until yesterday
                if ($streak == 0 && $checkDate->isToday()) {
                    $checkDate->subDay();
                    continue;
                }
                break; // Streak broken
            }
        }

        // 5. TOPIC MASTERY (Average score grouped by Material)
        $masteryRaw = \Illuminate\Support\Facades\DB::table('exam_answers')
            ->join('exams', 'exam_answers.exam_id', '=', 'exams.id')
            ->join('materials', 'exams.material_id', '=', 'materials.id')
            ->where('exam_answers.user_id', $studentId)
            ->selectRaw('materials.title, 
                         COUNT(exam_answers.id) as total_attempts, 
                         SUM(CASE WHEN exam_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct_attempts')
            ->groupBy('materials.id', 'materials.title')
            ->get();

        $masteryLabels = [];
        $masteryScores = [];
        foreach ($masteryRaw as $m) {
            $masteryLabels[] = \Illuminate\Support\Str::limit($m->title, 15);
            $masteryScores[] = $m->total_attempts > 0 ? round(($m->correct_attempts / $m->total_attempts) * 100) : 0;
        }

        return view('dashboard.partials.student.analytics', compact(
            'totalEnrollments',
            'completedCount',
            'inProgressCount',
            'completionRate',
            'totalAnswers',
            'correctAnswers',
            'incorrectAnswers',
            'totalHours',
            'examDates',
            'examScores',
            'streak',
            'masteryLabels',
            'masteryScores'
        ));
    }

    public function exportStudentAnalyticsPdf(\Illuminate\Http\Request $request)
    {
        $studentId = \Illuminate\Support\Facades\Auth::id();

        // 1. ACHIEVEMENTS & PROGRESS
        $totalEnrollments = \App\Models\Enrollment::where('user_id', $studentId)->count();
        $completedCount = \App\Models\Enrollment::where('user_id', $studentId)->where('status', 'completed')->count();
        $inProgressCount = \App\Models\Enrollment::where('user_id', $studentId)->where('status', 'in_progress')->count();
        $completionRate = $totalEnrollments > 0 ? round(($completedCount / $totalEnrollments) * 100) : 0;

        // NEW: Total Hours Learned (Calculated for the PDF)
        $totalMinutes = \Illuminate\Support\Facades\DB::table('enrollments')
            ->where('enrollments.user_id', $studentId)
            ->where('enrollments.status', 'completed')
            ->join('lessons', 'enrollments.material_id', '=', 'lessons.material_id')
            ->sum('lessons.time_limit');
        $totalHours = round($totalMinutes / 60, 1);

        // 2. EXAM PERFORMANCE
        $totalAnswers = \App\Models\ExamAnswer::where('user_id', $studentId)->count();
        $correctAnswers = \App\Models\ExamAnswer::where('user_id', $studentId)->where('is_correct', true)->count();
        $incorrectAnswers = $totalAnswers - $correctAnswers;

        // 3. TOPIC MASTERY DATA
        $masteryData = \Illuminate\Support\Facades\DB::table('exam_answers')
            ->join('exams', 'exam_answers.exam_id', '=', 'exams.id')
            ->join('materials', 'exams.material_id', '=', 'materials.id')
            ->where('exam_answers.user_id', $studentId)
            ->selectRaw('materials.title, COUNT(exam_answers.id) as total_attempts, SUM(CASE WHEN exam_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct_attempts')
            ->groupBy('materials.id', 'materials.title')
            ->get();

        // 4. LEARNING STREAK (Re-calculating for PDF)
        $streak = 0;
        $checkDate = now();
        while (true) {
            $hasActivity = \App\Models\ExamAnswer::where('user_id', $studentId)->whereDate('created_at', $checkDate->format('Y-m-d'))->exists() ||
                \App\Models\Enrollment::where('user_id', $studentId)->whereDate('updated_at', $checkDate->format('Y-m-d'))->exists();
            if ($hasActivity) {
                $streak++;
                $checkDate->subDay();
            } else {
                if ($streak == 0 && $checkDate->isToday()) {
                    $checkDate->subDay();
                    continue;
                }
                break;
            }
        }

        // 5. Bundle it all together
        $isPrint = $request->input('action') === 'print';
        $data = [
            'totalEnrollments' => $totalEnrollments,
            'completedCount' => $completedCount,
            'inProgressCount' => $inProgressCount,
            'completionRate' => $completionRate,
            'totalAnswers' => $totalAnswers,
            'correctAnswers' => $correctAnswers,
            'incorrectAnswers' => $incorrectAnswers,
            'totalHours' => $totalHours,
            'masteryData' => $masteryData,
            'streak' => $streak,

            // Filter checkboxes
            'showAchievements' => $request->has('check_achievements'),
            'showProgress' => $request->has('check_progress'),
            'showPerformance' => $request->has('check_performance'),

            'isPrint' => $isPrint,
        ];

        if ($isPrint) {
            return view('dashboard.partials.student.analytics-report', $data);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.partials.student.analytics-report', $data);
        return $pdf->download('My_Learning_Report_' . now()->format('Y_m_d') . '.pdf');
    }
    public function loadFeedbackPartial()
    {
        // If you have a Feedback model later, you can fetch data here:
        // $feedbacks = \App\Models\Feedback::latest()->paginate(15);
        // return view('dashboard.partials.admin.feedback', compact('feedbacks'));

        return view('dashboard.partials.admin.feedback');
    }

    /**
     * Handle global navigation search (AJAX)
     */
    public function globalSearch(\Illuminate\Http\Request $request)
    {
        $query = $request->input('q');
        $user = auth()->user();

        if (!$query || strlen($query) < 2) {
            return response()->json(['materials' => [], 'users' => []]);
        }

        // 1. Search Materials (Base Query isolating the text search)
        $materialsQuery = \App\Models\Material::with('instructor:id,first_name,last_name')
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            });

        // --- PRIVACY & ENROLLMENT LOGIC ---
        // Admins & CID bypass this and see everything. 
        if (!in_array($user->role, ['admin', 'cid'])) {
            $materialsQuery->where(function ($q) use ($user) {

                // Condition A: Material is public 
                // ⚠️ NOTE: Change 'is_public' to match your actual database column 
                // (e.g., if you use a string, change to ->where('visibility', 'public') )
                $q->where('is_public', true)

                    // Condition B: The user is the teacher who created the material
                    ->orWhere('instructor_id', $user->id);

                // Condition C: The user is a student who is currently enrolled
                if ($user->role === 'student') {
                    // ⚠️ NOTE: Change 'enrollments' to match the relationship name in your Material.php model
                    $q->orWhereHas('enrollments', function ($eq) use ($user) {
                        $eq->where('user_id', $user->id);
                    });
                }
            });
        }

        // Execute query
        $materials = $materialsQuery->limit(5)->get(['id', 'title', 'thumbnail', 'instructor_id']);

        $users = [];

        // 2. If Admin or CID, also search Users (Students/Teachers)
        if (in_array($user->role, ['admin', 'cid'])) {
            $users = \App\Models\User::where('first_name', 'LIKE', "%{$query}%")
                ->orWhere('last_name', 'LIKE', "%{$query}%")
                ->orWhere('employee_id', 'LIKE', "%{$query}%")
                ->orWhere('lrn', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get(['id', 'first_name', 'last_name', 'role']);
        }

        return response()->json([
            'materials' => $materials,
            'users' => $users
        ]);
    }

    public function loadCriteriaPartial()
    {
        $rubricData = [];

        // Read the global rubric JSON file if it exists
        if (\Illuminate\Support\Facades\Storage::exists('global_rubric.json')) {
            $rubricData = json_decode(\Illuminate\Support\Facades\Storage::get('global_rubric.json'), true);
        }

        // Pass the entire data object (which contains 'rubric' and 'passing_rate')
        return view('dashboard.partials.admin.criteria', ['rubric' => $rubricData]);
    }

    public function storeCriteria(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'rubric' => 'required|array',
            'passing_rate' => 'required|numeric|min:1|max:100' // Added validation for passing rate
        ]);

        // Save the entire request payload (both passing_rate and rubric)
        \Illuminate\Support\Facades\Storage::put(
            'global_rubric.json',
            json_encode($request->all(), JSON_PRETTY_PRINT)
        );

        return response()->json([
            'success' => true,
            'message' => 'Criteria and Passing Rate updated successfully!'
        ]);
    }
}