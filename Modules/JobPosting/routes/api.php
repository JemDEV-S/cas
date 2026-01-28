<?php

use Illuminate\Support\Facades\Route;
use Modules\JobPosting\Http\Controllers\JobPostingController;

/*
|--------------------------------------------------------------------------
| API Routes - JobPosting Module
|--------------------------------------------------------------------------
|
| Rutas para búsqueda dinámica, validación y operaciones en tiempo real
|
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('jobpostings', JobPostingController::class)->names('jobposting');
});

/*
|--------------------------------------------------------------------------
| API de Búsqueda Dinámica (Sin autenticación para selectores públicos)
|--------------------------------------------------------------------------
*/

Route::prefix('search')->name('search.')->group(function () {

    // Buscar Unidades Organizacionales
    Route::get('/organizational-units', [JobPostingController::class, 'searchOrganizationalUnits'])
         ->name('organizational-units');

    // Buscar Fases del Proceso
    Route::get('/process-phases', [JobPostingController::class, 'searchProcessPhases'])
         ->name('process-phases');

    // Buscar Convocatorias
    Route::get('/job-postings', [JobPostingController::class, 'searchJobPostings'])
         ->name('job-postings');
});

/*
|--------------------------------------------------------------------------
| API de Validación
|--------------------------------------------------------------------------
*/

Route::prefix('validate')->name('validate.')->group(function () {

    // Validar código único
    Route::get('/job-posting-code', [JobPostingController::class, 'validateCode'])
         ->name('job-posting-code');
});

/*
|--------------------------------------------------------------------------
| API de Generación
|--------------------------------------------------------------------------
*/

Route::prefix('generate')->name('generate.')->group(function () {

    // Generar siguiente código disponible
    Route::get('/job-posting-code', [JobPostingController::class, 'generateCode'])
         ->name('job-posting-code');
});

/*
|--------------------------------------------------------------------------
| API de Vista Previa y Utilidades
|--------------------------------------------------------------------------
*/

Route::prefix('preview')->name('preview.')->middleware(['web', 'auth'])->group(function () {

    // Vista previa de cronograma
    Route::post('/schedule', [JobPostingController::class, 'previewSchedule'])
         ->name('schedule');
});

Route::prefix('check')->name('check.')->middleware(['web', 'auth'])->group(function () {

    // Verificar fase duplicada
    Route::get('/duplicate-phase', [JobPostingController::class, 'checkDuplicatePhase'])
         ->name('duplicate-phase');
});

Route::prefix('regenerate')->name('regenerate.')->middleware(['web', 'auth'])->group(function () {

    // Regenerar cronograma completo
    Route::post('/schedule/{jobPosting}', [JobPostingController::class, 'regenerateSchedule'])
         ->name('schedule');
});

/*
|--------------------------------------------------------------------------
| API de Datos para Módulos
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth'])->group(function () {
    // Obtener unidades orgánicas de una convocatoria
    Route::get('/job-postings/{id}/requesting-units', [JobPostingController::class, 'getRequestingUnits'])
         ->name('job-postings.requesting-units');
});
