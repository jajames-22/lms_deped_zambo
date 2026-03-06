<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\QuizController;

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

        // --- SCHOOL MANAGEMENT SYSTEM ---
        // Notice how these exact names match your Blade buttons: route('schools'), route('schools.create')
        Route::get('/schools', [DashboardController::class, 'loadSchoolsPartial'])->name('schools');
        Route::get('/schools/create', [DashboardController::class, 'loadSchoolCreatePartial'])->name('schools.create');
        Route::post('/schools/store', [DashboardController::class, 'storeSchool'])->name('schools.store');
        
        // AJAX Route for District Dropdown (Secured behind auth)
    });
        Route::get('/get-districts/{quadrantId}', [DashboardController::class, 'getDistricts'])->name('districts.get');

});

// 3. COURSE MANAGEMENT ROUTES
Route::middleware(['auth'])->group(function () {
    Route::resource('courses', CourseController::class);
    Route::resource('lessons', LessonController::class);
    Route::resource('quizzes', QuizController::class);

    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store'])->name('courses.enroll');
});

// 4. REQUIRED AUTH FILES
require __DIR__ . '/auth.php';
require __DIR__ . '/assessment.php';