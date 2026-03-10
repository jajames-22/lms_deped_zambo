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

    // 2. FIXED: Removed the redundant 'assessments/' prefix and 'dashboard.assessments.' name
    Route::post('/upload-image', [AssessmentController::class, 'uploadImage'])
        ->name('upload_image');

    Route::post('/quiz/{quizId}/import', [AssessmentController::class, 'importQuestions'])->name('quiz.import');

    \Route::get('template/download', [AssessmentController::class, 'downloadTemplate'])
        ->name('download_template');

    Route::post('/{id}/import', [AssessmentController::class, 'importQuestions'])
        ->name('import');
});