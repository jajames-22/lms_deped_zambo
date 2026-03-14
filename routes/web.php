<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentAssessmentController;
use App\Http\Controllers\AssessmentController;

// Note: Ensure you import these if you haven't already!
// use App\Http\Controllers\CourseController;
// use App\Http\Controllers\LessonController;
// use App\Http\Controllers\QuizController;

// 1. GUEST ROUTE
Route::get('/', function () {
    return view('index');
})->middleware('guest')->name('index');

/*
|--------------------------------------------------------------------------
| 2. AUTHENTICATED DASHBOARD ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // Main Dashboard Entry Point
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.main');
    
    // Alias for the student middleware redirect to fallback to the main dashboard
    Route::get('/student/dashboard', [DashboardController::class, 'index'])->name('student.dashboard');

    // Dashboard Partials (All prefixed with /dashboard in the URL)
    Route::prefix('dashboard')->group(function () {

        // General Dashboard Links
        Route::get('/home', [DashboardController::class, 'loadHomePartial'])->name('dashboard.home');
        Route::get('/enrolled', [DashboardController::class, 'loadEnrolledPartial'])->name('dashboard.enrolled');
        Route::get('/certificates', [DashboardController::class, 'loadCertificatesPartial'])->name('dashboard.certificates');
        Route::get('/materials', [DashboardController::class, 'loadMaterialsPartial'])->name('dashboard.materials');
        Route::get('/teachers', [DashboardController::class, 'loadTeachersPartial'])->name('dashboard.teachers');
        Route::get('/students', [StudentController::class, 'loadStudentsPartial'])->name('dashboard.students');
        Route::get('/profile', [DashboardController::class, 'loadProfilePartial'])->name('dashboard.profile');
        Route::get('/settings', [DashboardController::class, 'loadSettingsPartial'])->name('dashboard.settings');
        Route::get('/assessment', [DashboardController::class, 'loadAssessmentPartial'])->name('dashboard.assessment');

        // Schools Management
        Route::get('/schools', [DashboardController::class, 'loadSchoolsPartial'])->name('schools');
        Route::get('/schools/create', [DashboardController::class, 'loadSchoolCreatePartial'])->name('schools.create');
        Route::post('/schools/store', [DashboardController::class, 'storeSchool'])->name('schools.store');
        Route::get('/schools/{school}/edit', [DashboardController::class, 'editSchoolPartial'])->name('schools.edit');
        Route::put('/schools/{school}', [DashboardController::class, 'updateSchool'])->name('schools.update');
        Route::delete('/schools/{school}', [DashboardController::class, 'destroySchool'])->name('schools.destroy');

        // Teachers Management
        Route::get('/teachers/create', [TeacherController::class, 'createTeacherPartial'])->name('teachers.create');
        Route::post('/teachers/store', [TeacherController::class, 'storeTeacher'])->name('teachers.store');
        Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'editTeacherPartial'])->name('teachers.edit');
        Route::put('/teachers/{teacher}', [TeacherController::class, 'updateTeacher'])->name('teachers.update');
        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroyTeacher'])->name('teachers.destroy');

        // Students Management
        Route::get('/students/create', [StudentController::class, 'createStudentPartial'])->name('students.create');
        Route::post('/students/store', [StudentController::class, 'storeStudent'])->name('students.store');
        Route::get('/students/{student}/edit', [StudentController::class, 'editStudentPartial'])->name('students.edit');
        Route::put('/students/{student}', [StudentController::class, 'updateStudent'])->name('students.update');
        Route::delete('/students/{student}', [StudentController::class, 'destroyStudent'])->name('students.destroy');

        // Admin Assessment Management
        Route::get('/assessments/{assessment}/manage', [AssessmentController::class, 'manage'])->name('dashboard.assessments.manage');
        Route::post('/assessments/{assessment}/access', [AssessmentController::class, 'addAccess'])->name('dashboard.assessments.access.add');
        Route::delete('/assessments/access/{access}', [AssessmentController::class, 'removeAccess'])->name('dashboard.assessments.access.remove');
        Route::post('/assessments/{assessment}/import-access', [AssessmentController::class, 'importAccess'])->name('dashboard.assessments.access.import');

    });

    Route::get('/get-districts/{quadrantId}', [DashboardController::class, 'getDistricts'])->name('districts.get');
});

/*
|--------------------------------------------------------------------------
| 3. COURSE MANAGEMENT ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::resource('courses', CourseController::class);
    Route::resource('lessons', LessonController::class);
    Route::resource('quizzes', QuizController::class);
}); // <-- FIXED: Was missing this closing bracket

/*
|--------------------------------------------------------------------------
| 4. STUDENT ASSESSMENT ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    // Public validation endpoint (Checks if code is valid)
    Route::post('/student/assessment/verify', [StudentAssessmentController::class, 'verifyCode'])->name('student.assessment.verify');

    // Secure endpoints (Checks if Student LRN is authorized using your new Middleware)
    Route::middleware(['check.assessment.access'])->group(function () {
        Route::get('/assessment/{access_key}/lobby', [StudentAssessmentController::class, 'lobby'])->name('student.assessment.lobby');
        Route::get('/assessment/{access_key}/exam', [StudentAssessmentController::class, 'exam'])->name('student.assessment.exam');
        
        // ADD YOUR SUBMIT ROUTE HERE LATER:
        // Route::post('/assessment/{access_key}/submit', [StudentAssessmentController::class, 'submit'])->name('student.assessment.submit');
    });

}); // <-- FIXED: Was missing this closing bracket

/*
|--------------------------------------------------------------------------
| 5. REQUIRED AUTH FILES
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
require __DIR__ . '/assessment.php';
require __DIR__ . '/materials.php';