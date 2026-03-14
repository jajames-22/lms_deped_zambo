<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MaterialsController;

Route::prefix('dashboard/materials')->name('dashboard.materials.')->middleware(['auth'])->group(function () {
    Route::get('/', [MaterialsController::class, 'index'])->name('index');
    Route::get('/create', [MaterialsController::class, 'create'])->name('create');
    Route::get('/{id}/edit', [MaterialsController::class, 'edit'])->name('edit');
    Route::get('/{id}/show', [MaterialsController::class, 'edit'])->name('show'); // Added this to prevent a future error!
    
    // Builder APIs
    Route::post('/{id}/store', [MaterialsController::class, 'store'])->name('store');
    Route::post('/{id}/autosave', [MaterialsController::class, 'autosave'])->name('autosave');
    Route::post('/upload-media', [MaterialsController::class, 'uploadMedia'])->name('upload_media');
    Route::delete('/{id}', [MaterialsController::class, 'destroy'])->name('destroy');
});