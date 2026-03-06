<?php 

use App\Http\Controllers\AssessmentController;

Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard.')->group(function () {
    // Step 1: Create Assessment Setup
    Route::get('/assessments/create', [AssessmentController::class, 'create'])->name('assessments.create');
    Route::post('/assessments/store-setup', [AssessmentController::class, 'storeSetup'])->name('assessments.store_setup');

    // Step 2: Assessment Builder
    Route::get('/assessments/{id}/build', [AssessmentController::class, 'builder'])->name('assessments.builder');
    Route::post('/assessments/{id}/store-questions', [AssessmentController::class, 'storeQuestions'])->name('assessments.store_questions');
});