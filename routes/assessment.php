<?php

use App\Http\Controllers\AssessmentController;

Route::middleware(['auth', 'verified'])->prefix('dashboard/assessment')->name('dashboard.assessments.')->group(function () {
    Route::get('/create', [AssessmentController::class, 'create'])->name('create');
    Route::get('/{id}/build', [AssessmentController::class, 'builder'])->name('builder');
    Route::post('/{id}/store-questions', [AssessmentController::class, 'storeQuestions'])->name('store_questions');
    Route::delete('/{id}', [AssessmentController::class, 'destroy'])->name('destroy');
});