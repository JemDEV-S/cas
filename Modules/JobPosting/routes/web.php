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
    Route::get('/{jobPosting}/editar', [JobPostingController::class, 'edit'])->name('edit');
    Route::put('/{jobPosting}', [JobPostingController::class, 'update'])->name('update');
    Route::delete('/{jobPosting}', [JobPostingController::class, 'destroy'])->name('destroy');

    Route::get('/{jobPosting}', [JobPostingController::class, 'show'])->name('show');

    // Acciones especiales
    Route::post('/{jobPosting}/publicar', [JobPostingController::class, 'publish'])->name('publish');
    Route::post('/{jobPosting}/iniciar-proceso', [JobPostingController::class, 'startProcess'])->name('startProcess');
    Route::post('/{jobPosting}/finalizar', [JobPostingController::class, 'finalize'])->name('finalize');
    Route::post('/{jobPosting}/cancelar', [JobPostingController::class, 'cancel'])->name('cancel');
    Route::post('/{jobPosting}/clonar', [JobPostingController::class, 'clone'])->name('clone');

    // RUTAS DEL CRONOGRAMA
    Route::get('/{jobPosting}/cronograma', [ScheduleController::class, 'edit'])->name('schedule.edit');
    Route::put('/{jobPosting}/cronograma', [ScheduleController::class, 'update'])->name('schedule.update');
    Route::post('/{jobPosting}/cronograma/init', [ScheduleController::class, 'initialize'])->name('schedule.init');

    // Gestión de Fases
    Route::post('/{jobPosting}/actualizar-fases', [JobPostingController::class, 'updatePhases'])->name('updatePhases')->can('jobposting.manage.phases');
    Route::post('/fase/{schedule}/iniciar', [ScheduleController::class, 'startPhase'])->name('phase.start')->can('jobposting.manage.phases');
    Route::post('/fase/{schedule}/completar', [ScheduleController::class, 'completePhase'])->name('phase.complete')->can('jobposting.manage.phases');
    Route::post('/fase/{schedule}/saltar-siguiente', [ScheduleController::class, 'skipToNext'])->name('phase.skipToNext')->can('jobposting.manage.phases');

    // Historial
    Route::get('/{jobPosting}/historial', [JobPostingController::class, 'history'])->name('history');
});
