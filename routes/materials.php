<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MaterialsController;
use App\Http\Controllers\StudentEnrollmentController;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckAccountStatus;

// ==========================================
// STUDENT ROUTES (Must be outside the dashboard CheckRole middleware)
// ==========================================
Route::get('/materials/{hashid}/enroll/{email}', [StudentEnrollmentController::class, 'acceptInvitation'])
    ->name('student.materials.enroll')
    ->middleware(['signed', 'auth']);

Route::get('/dashboard/student/materials/{hashid}', [StudentEnrollmentController::class, 'show'])
    ->name('student.materials.show')
    ->middleware(['auth']);

Route::post('/student/enroll-code', [StudentEnrollmentController::class, 'enrollWithCode'])
    ->name('student.enroll.code')
    ->middleware(['auth']);

Route::get('/dashboard/enrolled', [StudentEnrollmentController::class, 'index'])
    ->name('student.enrolled')
    ->middleware(['auth']);

Route::post('/dashboard/student/materials/{id}/complete', [StudentEnrollmentController::class, 'markAsCompleted'])
    ->name('student.materials.complete')
    ->middleware(['auth']);

Route::get('/dashboard/certificates', [StudentEnrollmentController::class, 'myCertificates'])
    ->name('student.certificates.index')
    ->middleware(['auth']);


// ==========================================
// INSTRUCTOR & ADMIN MATERIAL MANAGEMENT ROUTES
// ==========================================
Route::middleware(['auth', 'verified', CheckAccountStatus::class, CheckRole::class . ':admin,teacher,cid'])
    ->prefix('dashboard/materials')
    ->name('materials.') 
    ->group(function () {
        
        // =========================================================
        // STATIC ROUTES (Must go BEFORE {hashid} to avoid 404 hijacks)
        // =========================================================
        Route::get('/', [MaterialsController::class, 'index'])->name('index');
        Route::post('/', [MaterialsController::class, 'store'])->name('store'); // Fixes Test 5 (Create)
        Route::get('/create', [MaterialsController::class, 'create'])->name('create');
        Route::get('/template/download', [MaterialsController::class, 'downloadTemplate'])->name('download_template');
        Route::post('/upload-media', [MaterialsController::class, 'uploadMedia'])->name('upload_media');
        Route::post('/upload-temp-media', [MaterialsController::class, 'uploadTempMedia'])->name('uploadTempMedia');
        Route::post('/check-access-code', [MaterialsController::class, 'checkAccessCode'])->name('checkAccessCode');
        Route::get('/tags', [MaterialsController::class, 'getTags'])->name('tags');

        // =========================================================
        // DYNAMIC ROUTES (Using {hashid} exactly as the Controller expects)
        // =========================================================
        Route::prefix('{hashid}')->group(function () {
            
            // Core CRUD operations
            Route::get('/manage', [MaterialsController::class, 'manage'])->name('manage');
            Route::get('/edit', [MaterialsController::class, 'edit'])->name('edit');
            Route::put('/update', [MaterialsController::class, 'update'])->name('update');
            Route::get('/show', [MaterialsController::class, 'show'])->name('show');
            Route::get('/preview', [MaterialsController::class, 'preview'])->name('preview');
            Route::delete('/', [MaterialsController::class, 'destroy'])->name('destroy');
            Route::post('/autosave', [MaterialsController::class, 'autosave'])->name('autosave');
            
            // Settings & Tools
            Route::post('/thumbnail', [MaterialsController::class, 'updateThumbnail'])->name('thumbnail.update');
            Route::post('/save-grading-settings', [MaterialsController::class, 'saveGradingSettings'])->name('saveGradingSettings');
            Route::post('/duplicate', [MaterialsController::class, 'duplicate'])->name('duplicate');
            Route::post('/import', [MaterialsController::class, 'importLessons'])->name('import');
            
            // Workflows & Approvals
            Route::post('/submit', [MaterialsController::class, 'submitForReview'])->name('submitForReview');
            Route::post('/revert', [MaterialsController::class, 'revertToDraft'])->name('revertToDraft');
            Route::post('/request-unpublish', [MaterialsController::class, 'requestUnpublish'])->name('requestUnpublish');
            Route::post('/unpublish', [MaterialsController::class, 'unpublish'])->name('unpublish');
            Route::post('/publish', [MaterialsController::class, 'publish'])->name('publish');
            Route::post('/approve', [MaterialsController::class, 'approve'])->name('approve');
            Route::post('/evaluate', [MaterialsController::class, 'saveEvaluation'])->name('evaluate');
            Route::post('/toggle-featured', [MaterialsController::class, 'toggleFeatured'])->name('toggleFeatured');
            Route::patch('/toggle-visibility', [MaterialsController::class, 'toggleVisibility'])->name('toggle-visibility');
            
            // Access Management & Notifications
            Route::post('/notify-students', [MaterialsController::class, 'notifyStudents'])->name('notify-students');
            Route::post('/access/invite', [MaterialsController::class, 'sendIndividualInvite'])->name('access.invite');
            
            // Analytics & Exports
            Route::get('/analytics', [MaterialsController::class, 'analytics'])->name('analytics');
            Route::get('/export', [MaterialsController::class, 'exportMaterialAnalyticsPdf'])->name('export');
            Route::get('/export-report', [MaterialsController::class, 'exportReport'])->name('exportReport');
            Route::get('/export-pdf', [MaterialsController::class, 'exportPdf'])->name('exportPdf');
            
            // Soft Deletes
            Route::patch('/restore', [MaterialsController::class, 'restore'])->name('restore');
            Route::delete('/force', [MaterialsController::class, 'forceDelete'])->name('forceDelete');
            
            // Certificates
            Route::post('/unlock-certificate', [MaterialsController::class, 'unlockCertificate'])->name('unlockCertificate');
            Route::get('/certificate/download', [MaterialsController::class, 'downloadCertificate'])->name('downloadCertificate');
        });
    });