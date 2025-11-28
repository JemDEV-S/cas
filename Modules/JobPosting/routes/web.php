<?php

use Illuminate\Support\Facades\Route;
use Modules\JobPosting\Http\Controllers\JobPostingController;

Route::middleware(['auth', 'verified'])->prefix('convocatorias')->name('jobposting.')->group(function () {

    // ⭐ DASHBOARD COMO RUTA PRINCIPAL
    Route::get('/', [JobPostingController::class, 'dashboard'])->name('index');
    Route::get('/dashboard', [JobPostingController::class, 'dashboard'])->name('dashboard');

    // Listado completo
    Route::get('/listado', [JobPostingController::class, 'listAll'])->name('list');

    // CRUD
    Route::get('/crear', [JobPostingController::class, 'create'])->name('create')->can('jobposting.create.posting');
    Route::post('/crear', [JobPostingController::class, 'store'])->name('store')->can('jobposting.create.posting');
    Route::get('/{jobPosting}', [JobPostingController::class, 'show'])->name('show');
    Route::get('/{jobPosting}/editar', [JobPostingController::class, 'edit'])->name('edit');
    Route::put('/{jobPosting}', [JobPostingController::class, 'update'])->name('update');
    Route::delete('/{jobPosting}', [JobPostingController::class, 'destroy'])->name('destroy');

    // Acciones especiales
    Route::post('/{jobPosting}/publicar', [JobPostingController::class, 'publish'])->name('publish');
    Route::post('/{jobPosting}/iniciar-proceso', [JobPostingController::class, 'startProcess'])->name('startProcess');
    Route::post('/{jobPosting}/finalizar', [JobPostingController::class, 'finalize'])->name('finalize');
    Route::post('/{jobPosting}/cancelar', [JobPostingController::class, 'cancel'])->name('cancel');
    Route::post('/{jobPosting}/clonar', [JobPostingController::class, 'clone'])->name('clone');

    // Cronograma e historial
    Route::get('/{jobPosting}/cronograma', [JobPostingController::class, 'schedule'])->name('schedule');
    Route::get('/{jobPosting}/historial', [JobPostingController::class, 'history'])->name('history');
});
