<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentAssessmentController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MaterialsController;
use App\Http\Controllers\ExploreLayoutController;

// 👈 NEW: Import the middleware we created
use App\Http\Middleware\CheckAccountStatus;

// 1. GUEST ROUTE
Route::get('/', function () {
    return view('index');
})->middleware('guest')->name('index');

/*
|--------------------------------------------------------------------------
| 2. AUTHENTICATED DASHBOARD ROUTES
|--------------------------------------------------------------------------
*/
// 👈 NEW: Added CheckAccountStatus::class to automatically kick out suspended users!
Route::middleware(['auth', 'verified', CheckAccountStatus::class])->group(function () {

    // Main Dashboard Entry Point
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.main');

    // Alias for the student middleware redirect to fallback to the main dashboard
    Route::get('/student/dashboard', [DashboardController::class, 'index'])->name('student.dashboard');

    // Dashboard Partials (All prefixed with /dashboard in the URL)
    Route::prefix('dashboard')->group(function () {

        Route::get('/search', [DashboardController::class, 'globalSearch'])->name('dashboard.search');

        // General Dashboard Links
        Route::get('/home', [DashboardController::class, 'loadHomePartial'])->name('dashboard.home');
        Route::get('/enrolled', [DashboardController::class, 'loadEnrolledPartial'])->name('dashboard.enrolled');
        Route::get('/certificates', [DashboardController::class, 'loadCertificatesPartial'])->name('dashboard.certificates');
        Route::get('/materials', [DashboardController::class, 'loadMaterialsPartial'])->name('dashboard.materials');
        Route::get('/teachers', [DashboardController::class, 'loadTeachersPartial'])->name('dashboard.teachers');
        Route::get('/students', [StudentController::class, 'loadStudentsPartial'])->name('dashboard.students');

        // FIXED: Removed extra /dashboard from these paths since they are already inside the prefix
        Route::get('/profile', [ProfileController::class, 'show'])->name('dashboard.profile');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');

        Route::get('/settings', [DashboardController::class, 'loadSettingsPartial'])->name('dashboard.settings');
        Route::get('/assessment', [DashboardController::class, 'loadAssessmentPartial'])->name('dashboard.assessment');
        Route::get('/analytics', [DashboardController::class, 'loadAnalyticsPartial'])->name('dashboard.analytics');

        // Schools Management
        Route::get('/schools', [DashboardController::class, 'loadSchoolsPartial'])->name('schools');
        Route::get('/schools/create', [DashboardController::class, 'loadSchoolCreatePartial'])->name('schools.create');
        Route::post('/schools/store', [DashboardController::class, 'storeSchool'])->name('schools.store');
        Route::get('/schools/{school}/edit', [DashboardController::class, 'editSchoolPartial'])->name('schools.edit');
        Route::put('/schools/{school}', [DashboardController::class, 'updateSchool'])->name('schools.update');
        Route::delete('/schools/{school}', [DashboardController::class, 'destroySchool'])->name('schools.destroy');

        /*
        |--------------------------------------------------------------------------
        | STUDENTS MANAGEMENT
        |--------------------------------------------------------------------------
        */
        Route::delete('/students/bulk-delete', [StudentController::class, 'bulkDestroy'])->name('students.bulk-delete');
        Route::get('/students/create', [StudentController::class, 'createStudentPartial'])->name('students.create');
        Route::post('/students/store', [StudentController::class, 'storeStudent'])->name('students.store');
        Route::get('/students/import-template', [StudentController::class, 'downloadTemplate'])->name('students.import.template');
        Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');

        Route::get('/students/{student}/edit', [StudentController::class, 'editStudentPartial'])->name('students.edit');
        Route::put('/students/{student}', [StudentController::class, 'updateStudent'])->name('students.update');
        Route::delete('/students/{student}', [StudentController::class, 'destroyStudent'])->name('students.destroy');

        /*
        |--------------------------------------------------------------------------
        | TEACHERS MANAGEMENT
        |--------------------------------------------------------------------------
        */
        Route::delete('/teachers/bulk-delete', [TeacherController::class, 'bulkDestroy'])->name('teachers.bulk-delete');
        Route::get('/teachers/create', [TeacherController::class, 'createTeacherPartial'])->name('teachers.create');
        Route::post('/teachers/store', [TeacherController::class, 'storeTeacher'])->name('teachers.store');
        Route::get('/teachers/import-template', [TeacherController::class, 'downloadTemplate'])->name('teachers.import.template');
        Route::post('/teachers/import', [TeacherController::class, 'import'])->name('teachers.import');

        Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'editTeacherPartial'])->name('teachers.edit');
        Route::put('/teachers/{teacher}', [TeacherController::class, 'updateTeacher'])->name('teachers.update');
        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroyTeacher'])->name('teachers.destroy');

        // Admin Assessment Management
        Route::get('/assessments/{assessment}/manage', [AssessmentController::class, 'manage'])->name('dashboard.assessments.manage');
        Route::post('/assessments/{assessment}/access', [AssessmentController::class, 'addAccess'])->name('dashboard.assessments.access.add');
        Route::delete('/assessments/access/{access}', [AssessmentController::class, 'removeAccess'])->name('dashboard.assessments.access.remove');
        Route::post('/assessments/{assessment}/import-access', [AssessmentController::class, 'importAccess'])->name('dashboard.assessments.access.import');

        Route::patch('/assessments/{assessment}/toggle-status', [AssessmentController::class, 'toggleStatus'])->name('dashboard.assessments.toggle-status');
        Route::patch('/assessments/{assessment}/toggle-results', [AssessmentController::class, 'toggleResults'])->name('dashboard.assessments.toggle-results');

        Route::get('/explore', [DashboardController::class, 'loadExplorePartial'])->name('dashboard.explore');

        // FIXED: Removed extra /dashboard since it's already inside the prefix
        Route::post('/materials/{material}/tags', [MaterialsController::class, 'addTag'])->name('dashboard.materials.tags.add');
        Route::delete('/materials/{material}/tags/{tag}', [MaterialsController::class, 'removeTag'])->name('dashboard.materials.tags.remove');

        // Admin Explore Layout Management
        Route::get('/explore-layout', [ExploreLayoutController::class, 'index'])->name('dashboard.explore-layout');
        Route::post('/explore-layout', [ExploreLayoutController::class, 'store'])->name('dashboard.explore-layout.store');
        Route::put('/explore-layout/{section}', [ExploreLayoutController::class, 'update'])->name('dashboard.explore-layout.update');
        Route::delete('/explore-layout/{section}', [ExploreLayoutController::class, 'destroy'])->name('dashboard.explore-layout.destroy');
        Route::patch('/explore-layout/{section}/toggle', [ExploreLayoutController::class, 'toggleActive'])->name('dashboard.explore-layout.toggle');
        Route::post('/explore-layout/reorder', [ExploreLayoutController::class, 'reorder'])->name('dashboard.explore-layout.reorder');

        Route::get('/explore-layout/search-materials', [ExploreLayoutController::class, 'searchMaterials'])->name('dashboard.explore-layout.search');

        Route::patch('/materials/{material}/toggle-featured', [MaterialsController::class, 'toggleFeatured'])->name('dashboard.materials.toggle-featured');

        Route::get('/materials/{material}/show', [MaterialsController::class, 'show'])->name('dashboard.materials.show');
        Route::post('/materials/{material}/enroll', [MaterialsController::class, 'enroll'])->name('materials.enroll');
        Route::get('/materials/{material}/study', [MaterialsController::class, 'study'])->name('dashboard.materials.study');
        Route::post('/materials/{material}/unenroll', [MaterialsController::class, 'unenroll'])->name('dashboard.materials.unenroll');

        Route::post('/materials/{id}/grading', [MaterialsController::class, 'updateGrading'])->name('dashboard.materials.grading');
        Route::post('/materials/{material}/progress', [MaterialsController::class, 'saveProgress'])->name('dashboard.materials.progress');
        Route::post('/materials/{material}/complete', [MaterialsController::class, 'complete'])->name('dashboard.materials.complete');
        Route::get('/materials/{material}/result', [MaterialsController::class, 'result'])->name('dashboard.materials.result');
        Route::post('/materials/{material}/retake', [MaterialsController::class, 'retake'])->name('dashboard.materials.retake');
        Route::get('/materials/{material}/certificate', [MaterialsController::class, 'certificate'])->name('dashboard.materials.certificate');
        

        Route::get('/notifications', [MaterialsController::class, 'getNotifications'])->name('dashboard.notifications');
        Route::post('/notifications/{id}/read', [MaterialsController::class, 'markNotificationRead']);

        Route::post('/materials/{material}/download-count', [StudentController::class, 'incrementDownload']);

        Route::get('/feedback', [ProfileController::class, 'loadFeedbackPartial'])->name('dashboard.feedback');
        Route::post('/feedback/store', [ProfileController::class, 'storeFeedback'])->name('feedback.store');
        Route::post('/feedback/{id}/reply', [ProfileController::class, 'replyToFeedback'])->name('dashboard.feedback.reply');
    });

    Route::get('/get-districts/{quadrantId}', [DashboardController::class, 'getDistricts'])->name('districts.get');

    // EXPLORE ROUTES outside prefix
    Route::get('/dashboard/explore', [StudentController::class, 'explore'])->name('dashboard.explore');
    Route::get('/dashboard/explore/tags/{tag}/json', [App\Http\Controllers\StudentController::class, 'viewByTagJson'])->name('dashboard.explore.tag.json');
    Route::get('/dashboard/materials/{material}/view', [StudentController::class, 'viewMaterial'])->name('dashboard.materials.view');

    // ANALYTICS EXPORTS
    Route::get('/analytics/export/admin', [DashboardController::class, 'exportAdminAnalyticsPdf'])->name('analytics.export.admin');
    Route::get('/analytics/export/student', [DashboardController::class, 'exportStudentAnalyticsPdf'])->name('analytics.export.student');
    Route::get('/analytics/export/teacher', [DashboardController::class, 'exportTeacherAnalyticsPdf'])->name('analytics.export.teacher');

});


// 1. GUEST ROUTE
Route::get('/', function () {
    return view('index');
})->middleware('guest')->name('index');

// 👇 NEW: Publicly accessible Explore Route
Route::get('/explore', [App\Http\Controllers\DashboardController::class, 'publicExplore'])->name('explore.public');
Route::get('/explore/materials/{id}/show', [App\Http\Controllers\DashboardController::class, 'publicMaterialShow'])->name('explore.materials.show');
// web.php (Guest Section)
Route::get('/explore/tags/{tag}/json', [App\Http\Controllers\DashboardController::class, 'viewByTagJson'])->name('explore.tag.json');
// Note: Depending on how 'loadExplorePartial' is written, you may need to ensure it doesn't call Auth::user() directly, or create a duplicate method dedicated to guests.

/*
|--------------------------------------------------------------------------
| 3. COURSE MANAGEMENT ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', CheckAccountStatus::class])->group(function () {
    Route::resource('courses', CourseController::class);
    Route::resource('lessons', LessonController::class);
    Route::resource('quizzes', QuizController::class);
});

/*
|--------------------------------------------------------------------------
| 4. STUDENT ASSESSMENT ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', CheckAccountStatus::class])->group(function () {

    // Public validation endpoint (Checks if code is valid)
    Route::post('/student/assessment/verify', [StudentAssessmentController::class, 'verifyCode'])->name('student.assessment.verify');

    // Secure endpoints (Checks if Student LRN is authorized using your new Middleware)
    Route::middleware(['check.assessment.access'])->group(function () {
        Route::get('/assessment/{access_key}/lobby', [StudentAssessmentController::class, 'lobby'])->name('student.assessment.lobby');
        Route::get('/assessment/{access_key}/exam', [StudentAssessmentController::class, 'exam'])->name('student.assessment.exam');
        Route::post('/assessment/{access_key}/submit', [StudentAssessmentController::class, 'submit'])->name('student.assessment.submit');
        Route::post('/assessment/{access_key}/autosave', [StudentAssessmentController::class, 'autoSave'])->name('student.assessment.autosave');
        Route::get('/assessment/{access_key}/results', [StudentAssessmentController::class, 'results'])->name('student.assessment.results');
    });
});

/*
|--------------------------------------------------------------------------
| 5. REQUIRED AUTH FILES
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
require __DIR__ . '/assessment.php';
require __DIR__ . '/materials.php';