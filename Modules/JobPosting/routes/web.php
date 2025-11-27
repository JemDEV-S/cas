<?php

use Illuminate\Support\Facades\Route;
use Modules\JobPosting\Http\Controllers\JobPostingController;

Route::middleware(['auth', 'verified'])->prefix('convocatorias')->name('jobposting.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [JobPostingController::class, 'dashboard'])->name('dashboard');
    
    // CRUD básico
    Route::resource('/', JobPostingController::class)->parameters(['' => 'jobPosting']);
    
    // Acciones especiales
    Route::post('/{jobPosting}/publicar', [JobPostingController::class, 'publish'])->name('publish');
    Route::post('/{jobPosting}/iniciar-proceso', [JobPostingController::class, 'startProcess'])->name('startProcess');
    Route::post('/{jobPosting}/finalizar', [JobPostingController::class, 'finalize'])->name('finalize');
    Route::post('/{jobPosting}/cancelar', [JobPostingController::class, 'cancel'])->name('cancel');
    Route::post('/{jobPosting}/clonar', [JobPostingController::class, 'clone'])->name('clone');
    
    // Cronograma
    Route::get('/{jobPosting}/cronograma', [JobPostingController::class, 'schedule'])->name('schedule');
    
    // Historial
    Route::get('/{jobPosting}/historial', [JobPostingController::class, 'history'])->name('history');
});