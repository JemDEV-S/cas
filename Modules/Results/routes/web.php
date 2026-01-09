<?php

use Illuminate\Support\Facades\Route;
use Modules\Results\Http\Controllers\Admin\ResultPublicationController;
use Modules\Results\Http\Controllers\Applicant\MyResultsController;

/*
|--------------------------------------------------------------------------
| Admin Routes - Gestión de Publicaciones de Resultados
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.results.')->group(function () {

    // Dashboard de publicaciones
    Route::get('results', [ResultPublicationController::class, 'index'])->name('index');
    Route::get('results/{publication}', [ResultPublicationController::class, 'show'])->name('show');

    // Publicar resultados Fase 4 (Elegibilidad)
    Route::get('postings/{posting}/results/phase4/create', [ResultPublicationController::class, 'createPhase4'])
        ->name('create-phase4');
    Route::post('postings/{posting}/results/phase4', [ResultPublicationController::class, 'storePhase4'])
        ->name('store-phase4');

    // Publicar resultados Fase 7 (Evaluación Curricular)
    Route::get('postings/{posting}/results/phase7/create', [ResultPublicationController::class, 'createPhase7'])
        ->name('create-phase7');
    Route::post('postings/{posting}/results/phase7', [ResultPublicationController::class, 'storePhase7'])
        ->name('store-phase7');

    // Publicar resultados Fase 9 (Resultados Finales)
    Route::get('postings/{posting}/results/phase9/create', [ResultPublicationController::class, 'createPhase9'])
        ->name('create-phase9');
    Route::post('postings/{posting}/results/phase9', [ResultPublicationController::class, 'storePhase9'])
        ->name('store-phase9');

    // Acciones sobre publicaciones
    Route::post('results/{publication}/unpublish', [ResultPublicationController::class, 'unpublish'])
        ->name('unpublish');
    Route::post('results/{publication}/republish', [ResultPublicationController::class, 'republish'])
        ->name('republish');

    // Descargas
    Route::get('results/{publication}/download-pdf', [ResultPublicationController::class, 'downloadPdf'])
        ->name('download-pdf');
    Route::get('results/{publication}/download-excel', [ResultPublicationController::class, 'downloadExcel'])
        ->name('download-excel');
    Route::post('results/{publication}/generate-excel', [ResultPublicationController::class, 'generateExcel'])
        ->name('generate-excel');
});

/*
|--------------------------------------------------------------------------
| Applicant Routes - Portal del Postulante
|--------------------------------------------------------------------------
*/
Route::prefix('applicant')->middleware(['auth'])->name('applicant.results.')->group(function () {

    // Mis resultados
    Route::get('my-results', [MyResultsController::class, 'index'])->name('index');
    Route::get('my-results/{publication}', [MyResultsController::class, 'show'])->name('show');
    Route::get('my-results/{publication}/download-pdf', [MyResultsController::class, 'downloadPdf'])
        ->name('download-pdf');
});
