<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\QuizController;

// Added Middleware to ensure only guests (non-logged-in users) see this
Route::get('/', function () {
    return view('index');
})->middleware('guest')->name('index');

// The Main Dashboard Layout
Route::middleware(['auth', 'verified'])->group(function () {
    // 🔹 This now points to the controller which will decide which layout to load
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.main');
});

// The Partials (Grouped to share middleware and name prefixes)
Route::prefix('dashboard')
    ->middleware(['auth', 'verified']) // Added 'verified' here so unverified users can't fetch partials
    ->name('dashboard.') // Prefixes names, e.g., 'dashboard.home'
    ->group(function () {

        Route::get('/home', [DashboardController::class, 'loadHomePartial'])->name('home');

        Route::get('/enrolled', [DashboardController::class, 'loadEnrolledPartial'])->name('enrolled');

        Route::get('/certificates', [DashboardController::class, 'loadCertificatesPartial'])->name('certificates');

        Route::get('/materials', [DashboardController::class, 'loadMaterialsPartial'])->name('materials');

        Route::get('/schools', [DashboardController::class, 'loadSchoolsPartial'])->name('schools');

        Route::get('/teachers', [DashboardController::class, 'loadTeachersPartial'])->name('teachers');

        Route::get('/students', [DashboardController::class, 'loadStudentsPartial'])->name('students');

        Route::get('/profile', [DashboardController::class, 'loadProfilePartial'])->name('profile');

        Route::get('/settings', [DashboardController::class, 'loadSettingsPartial'])->name('settings');

    });


Route::middleware(['auth'])->group(function () {

    Route::resource('courses', CourseController::class);

    Route::resource('lessons', LessonController::class);

    Route::resource('quizzes', QuizController::class);


    Route::get('/courses/{course}', [CourseController::class, 'show'])
        ->name('courses.show');

    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store'])
        ->middleware('auth')
        ->name('courses.enroll');

});
require __DIR__ . '/auth.php';