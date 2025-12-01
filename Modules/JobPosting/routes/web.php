<?php

use Illuminate\Support\Facades\Route;
use Modules\JobPosting\Http\Controllers\JobPostingController;
use Modules\JobPosting\Http\Controllers\ScheduleController;

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
    
    // RUTAS DEL CRONOGRAMA
    // Ver/Editar cronograma
    Route::get('/{jobPosting}/cronograma', [ScheduleController::class, 'edit'])->name('schedule.edit');
    // Guardar cambios
    Route::put('/{jobPosting}/cronograma', [ScheduleController::class, 'update'])->name('schedule.update');
    // Generar automático (botón mágico)
    Route::post('/{jobPosting}/cronograma/init', [ScheduleController::class, 'initialize'])->name('schedule.init');
    
    // Historial
    Route::get('/{jobPosting}/historial', [JobPostingController::class, 'history'])->name('history');
});
