<?php

use App\Http\Controllers\AssessmentController;

Route::middleware(['auth', 'verified'])->prefix('dashboard/assessments')->name('dashboard.assessments.')->group(function () {
    // ADD THIS MISSING LINE:
    Route::get('/', [AssessmentController::class, 'index'])->name('index');

    Route::get('/create', [AssessmentController::class, 'create'])->name('create');
    Route::get('/{id}/build', [AssessmentController::class, 'builder'])->name('builder');
    Route::post('/{id}/store-questions', [AssessmentController::class, 'storeQuestions'])->name('store_questions');
    Route::delete('/{id}', [AssessmentController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/autosave', [AssessmentController::class, 'autosave'])
        ->name('autosave');

    Route::post('/upload-media', [AssessmentController::class, 'uploadMedia'])
        ->name('upload_media');

    Route::post('/quiz/{quizId}/import', [AssessmentController::class, 'importQuestions'])->name('quiz.import');

    \Route::get('template/download', [AssessmentController::class, 'downloadTemplate'])
        ->name('download_template');

    Route::post('/{id}/import', [AssessmentController::class, 'importQuestions'])
        ->name('import');

    Route::patch('/{id}/toggle-status', [AssessmentController::class, 'toggleStatus'])
    ->name('toggle-status');
    
    Route::get('/{assessment}/analytics', [AssessmentController::class, 'analytics'])->name('analytics');
    
    // ADD THIS NEW LINE FOR THE EXPORT:
    Route::get('/{assessment}/export', [AssessmentController::class, 'exportReport'])->name('export');
});