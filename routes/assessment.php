<?php 

use App\Http\Controllers\AssessmentController;

Route::middleware(['auth', 'verified'])->prefix('dashboard/assessment')->name('dashboard.assessments.')->group(function () {
    // Matches: /dashboard/assessment/create
    Route::get('/create', [AssessmentController::class, 'create'])->name('create');
    Route::post('/store-setup', [AssessmentController::class, 'storeSetup'])->name('store_setup');

    // Matches: /dashboard/assessment/{id}/build
    Route::get('/{id}/build', [AssessmentController::class, 'builder'])->name('builder');
    Route::post('/{id}/store-questions', [AssessmentController::class, 'storeQuestions'])->name('store_questions');
});