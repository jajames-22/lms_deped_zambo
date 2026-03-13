<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentAssessmentController;
use App\Http\Controllers\AssessmentController;

// 1. GUEST ROUTE
Route::get('/', function () {
    return view('index');
})->middleware('guest')->name('index');

// 2. AUTHENTICATED DASHBOARD ROUTES
Route::middleware(['auth', 'verified'])->group(function () {

    // Main Dashboard Entry Point
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.main');

    // Dashboard Partials (All prefixed with /dashboard in the URL)
    Route::prefix('dashboard')->group(function () {

        // General Dashboard Links
        Route::get('/home', [DashboardController::class, 'loadHomePartial'])->name('dashboard.home');
        Route::get('/enrolled', [DashboardController::class, 'loadEnrolledPartial'])->name('dashboard.enrolled');
        Route::get('/certificates', [DashboardController::class, 'loadCertificatesPartial'])->name('dashboard.certificates');
        Route::get('/materials', [DashboardController::class, 'loadMaterialsPartial'])->name('dashboard.materials');
        Route::get('/teachers', [DashboardController::class, 'loadTeachersPartial'])->name('dashboard.teachers');
        Route::get('/students', [DashboardController::class, 'loadStudentsPartial'])->name('dashboard.students');
        Route::get('/profile', [DashboardController::class, 'loadProfilePartial'])->name('dashboard.profile');
        Route::get('/settings', [DashboardController::class, 'loadSettingsPartial'])->name('dashboard.settings');
        Route::get('/assessment', [DashboardController::class, 'loadAssessmentPartial'])->name('dashboard.assessment');

        Route::get('/schools', [DashboardController::class, 'loadSchoolsPartial'])->name('schools');
        Route::get('/schools/create', [DashboardController::class, 'loadSchoolCreatePartial'])->name('schools.create');
        Route::post('/schools/store', [DashboardController::class, 'storeSchool'])->name('schools.store');
        Route::get('/schools/{school}/edit', [DashboardController::class, 'editSchoolPartial'])->name('schools.edit');
        Route::put('/schools/{school}', [DashboardController::class, 'updateSchool'])->name('schools.update');
        Route::delete('/schools/{school}', [DashboardController::class, 'destroySchool'])->name('schools.destroy');

        Route::get('/teachers/create', [TeacherController::class, 'createTeacherPartial'])->name('teachers.create');
        Route::post('/teachers/store', [TeacherController::class, 'storeTeacher'])->name('teachers.store');
        Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'editTeacherPartial'])->name('teachers.edit');
        Route::put('/teachers/{teacher}', [TeacherController::class, 'updateTeacher'])->name('teachers.update');
        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroyTeacher'])->name('teachers.destroy');

        Route::get('/students', [StudentController::class, 'loadStudentsPartial'])->name('dashboard.students');
        Route::get('/students/create', [StudentController::class, 'createStudentPartial'])->name('students.create');
        Route::post('/students/store', [StudentController::class, 'storeStudent'])->name('students.store');
        Route::get('/students/{student}/edit', [StudentController::class, 'editStudentPartial'])->name('students.edit');
        Route::put('/students/{student}', [StudentController::class, 'updateStudent'])->name('students.update');
        Route::delete('/students/{student}', [StudentController::class, 'destroyStudent'])->name('students.destroy');

        Route::post('/student/assessment/verify', [StudentAssessmentController::class, 'verifyCode'])->name('student.assessment.verify');
        Route::get('/student/assessment/{access_key}/lobby', [StudentAssessmentController::class, 'lobby'])->name('student.assessment.lobby');

        Route::get('/dashboard/assessments/{assessment}/manage', [AssessmentController::class, 'manage'])->name('dashboard.assessments.manage');

        // Explicitly named Student Access Routes
        Route::post('/dashboard/assessments/{assessment}/access', [App\Http\Controllers\AssessmentController::class, 'addAccess'])->name('dashboard.assessments.access.add');

        Route::delete('/dashboard/assessments/access/{access}', [App\Http\Controllers\AssessmentController::class, 'removeAccess'])->name('dashboard.assessments.access.remove');

    });
    Route::get('/get-districts/{quadrantId}', [DashboardController::class, 'getDistricts'])->name('districts.get');

});



Route::prefix('assessments')->name('dashboard.assessments.')->group(function () {

    // ... your other assessment routes (manage, builder, etc.)

    // ADD THIS LINE EXACTLY:
    Route::post('/{assessment}/import-access', [AssessmentController::class, 'importAccess'])->name('access.import');

});

// 4. REQUIRED AUTH FILES
require __DIR__ . '/auth.php';
require __DIR__ . '/assessment.php';
require __DIR__ . '/materials.php';