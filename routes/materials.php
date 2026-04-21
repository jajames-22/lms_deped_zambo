<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MaterialsController;
use App\Http\Controllers\StudentEnrollmentController;
use App\Http\Middleware\CheckRole;

// ==========================================
// STUDENT ROUTE (Must be outside the dashboard prefix/middleware)
// ==========================================
Route::get('/materials/{material}/enroll/{email}', [StudentEnrollmentController::class, 'acceptInvitation'])
    ->name('student.materials.enroll')
    ->middleware(['signed', 'auth']);

Route::get('/dashboard/student/materials/{id}', [App\Http\Controllers\StudentEnrollmentController::class, 'show'])
    ->name('student.materials.show')
    ->middleware(['auth']);

Route::post('/student/enroll-code', [StudentEnrollmentController::class, 'enrollWithCode'])
    ->name('student.enroll.code')
    ->middleware(['auth']);

Route::get('/dashboard/enrolled', [App\Http\Controllers\StudentEnrollmentController::class, 'index'])
    ->name('student.enrolled')
    ->middleware(['auth']);

Route::post('/dashboard/student/materials/{id}/complete', [App\Http\Controllers\StudentEnrollmentController::class, 'markAsCompleted'])
    ->name('student.materials.complete')
    ->middleware(['auth']);

Route::get('/dashboard/certificates', [App\Http\Controllers\StudentEnrollmentController::class, 'myCertificates'])
    ->name('student.certificates.index')
    ->middleware(['auth']);


// Remove any ->middleware('signed') attachments here


// Public Certificate Verification and Download
Route::get('/certificate/{hashid}', [StudentEnrollmentController::class, 'completionPage'])->name('student.materials.achieved');
Route::get('/certificate/download/{hashid}', [StudentEnrollmentController::class, 'downloadCertificate'])->name('student.certificate.download');

// Route to preview the PDF template directly in the browser as HTML
Route::get('/certificate/preview/{enrollment_id}', [App\Http\Controllers\StudentEnrollmentController::class, 'previewCertificateTemplate'])
    ->name('student.certificate.preview')
    ->middleware(['auth']); // Requires you to be logged in to view it
// ==========================================
// TEACHER / ADMIN ROUTES
// ==========================================
Route::prefix('dashboard/materials')
    ->name('dashboard.materials.')
    // Apply auth and role middleware to the entire group
    ->middleware(['auth', 'verified', CheckRole::class . ':admin,teacher,cid']) // 👈 Replaced superadmin with cid
    ->group(function () {

    // The main index route (acts as a dispatcher)
    Route::get('/', [MaterialsController::class, 'index'])->name('index');

    // Shared CRUD & Builder Routes
    Route::get('/create', [MaterialsController::class, 'create'])->name('create');
    Route::get('/{id}/edit', [MaterialsController::class, 'edit'])->name('edit');
    Route::get('/{id}/show', [MaterialsController::class, 'edit'])->name('show');

    // Management & Access Routes
    Route::get('/{id}/manage', [MaterialsController::class, 'manage'])->name('manage');
    Route::patch('/{id}/toggle-status', [MaterialsController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{id}/access', [MaterialsController::class, 'addAccess'])->name('access.add');
    Route::delete('/access/{id}', [MaterialsController::class, 'removeAccess'])->name('access.remove');
    Route::post('/{id}/access/import', [MaterialsController::class, 'importAccess'])->name('access.import');

    // Builder APIs
    Route::post('/{id}/store', [MaterialsController::class, 'store'])->name('store');
    Route::post('/{id}/autosave', [MaterialsController::class, 'autosave'])->name('autosave');
    Route::post('/upload-media', [MaterialsController::class, 'uploadMedia'])->name('upload_media');
    Route::delete('/{id}', [MaterialsController::class, 'destroy'])->name('destroy');

    // Import/Export Routes
    Route::get('/template/download', [MaterialsController::class, 'downloadTemplate'])->name('download_template');
    Route::post('/{id}/import', [MaterialsController::class, 'importLessons'])->name('import');

    // Visibility & Notification Routes
    Route::patch('/{id}/toggle-visibility', [MaterialsController::class, 'toggleVisibility'])->name('toggle-visibility');
    Route::post('/{id}/notify-students', [MaterialsController::class, 'notifyStudents'])->name('notify-students');
    Route::post('/access/{id}/invite', [MaterialsController::class, 'sendIndividualInvite'])->name('access.invite');
    Route::get('/{id}/preview', [MaterialsController::class, 'preview'])->name('preview');
    Route::get('/{id}/analytics', [MaterialsController::class, 'analytics'])
        ->name('analytics');
    Route::get('/{id}/analytics', [MaterialsController::class, 'analytics'])
        ->name('analytics');
    Route::get('/{id}/export', [MaterialsController::class, 'exportMaterialAnalyticsPdf'])
        ->name('export');

});