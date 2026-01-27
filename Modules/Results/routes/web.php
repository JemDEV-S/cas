<?php

use Illuminate\Support\Facades\Route;
use Modules\Results\Http\Controllers\Admin\ResultPublicationController;
use Modules\Results\Http\Controllers\Admin\CvResultProcessingController;
use Modules\Results\Http\Controllers\Applicant\MyResultsController;

/*
|--------------------------------------------------------------------------
| Admin Routes - Gestión de Publicaciones de Resultados
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth'])->name('admin.results.')->group(function () {
    Route::get('results/cv-processing', [CvResultProcessingController::class, 'list'])
        ->name('cv-processing.list');

    // Dashboard de publicaciones
    Route::get('results', [ResultPublicationController::class, 'index'])
        ->name('index')
        ->can('viewAny', \Modules\Results\Policies\ResultPublicationPolicy::class);
    Route::get('results/{publication}', [ResultPublicationController::class, 'show'])
        ->name('show')
        ->can('view', 'publication');

    // Publicar resultados Fase 4 (Elegibilidad)
    Route::get('postings/{posting}/results/phase4/create', [ResultPublicationController::class, 'createPhase4'])
        ->name('create-phase4')
        ->can('publishPhase4', \Modules\Results\Policies\ResultPublicationPolicy::class);
    Route::post('postings/{posting}/results/phase4', [ResultPublicationController::class, 'storePhase4'])
        ->name('store-phase4')
        ->can('publishPhase4', \Modules\Results\Policies\ResultPublicationPolicy::class);

    // Publicar resultados Fase 7 (Evaluación Curricular)
    Route::get('postings/{posting}/results/phase7/create', [ResultPublicationController::class, 'createPhase7'])
        ->name('create-phase7')
        ->can('publishPhase7', \Modules\Results\Policies\ResultPublicationPolicy::class);
    Route::post('postings/{posting}/results/phase7', [ResultPublicationController::class, 'storePhase7'])
        ->name('store-phase7')
        ->can('publishPhase7', \Modules\Results\Policies\ResultPublicationPolicy::class);

    // Publicar resultados Fase 9 (Resultados Finales)
    Route::get('postings/{posting}/results/phase9/create', [ResultPublicationController::class, 'createPhase9'])
        ->name('create-phase9')
        ->can('publishPhase9', \Modules\Results\Policies\ResultPublicationPolicy::class);
    Route::post('postings/{posting}/results/phase9', [ResultPublicationController::class, 'storePhase9'])
        ->name('store-phase9')
        ->can('publishPhase9', \Modules\Results\Policies\ResultPublicationPolicy::class);

    // Procesamiento de Resultados CV (Fase 6 -> 7)

    Route::get('postings/{posting}/results/cv-processing', [CvResultProcessingController::class, 'index'])
        ->name('cv-processing');
    Route::post('postings/{posting}/results/cv-processing/preview', [CvResultProcessingController::class, 'preview'])
        ->name('cv-processing.preview');
    Route::post('postings/{posting}/results/cv-processing/execute', [CvResultProcessingController::class, 'execute'])
        ->name('cv-processing.execute');
    Route::get('postings/{posting}/results/cv-processing/download-pdf', [CvResultProcessingController::class, 'downloadPdf'])
        ->name('cv-processing.download-pdf');

    // Acciones sobre publicaciones
    Route::post('results/{publication}/unpublish', [ResultPublicationController::class, 'unpublish'])
        ->name('unpublish')
        ->can('unpublish', 'publication');
    Route::post('results/{publication}/republish', [ResultPublicationController::class, 'republish'])
        ->name('republish')
        ->can('republish', 'publication');

    // Descargas
    Route::get('results/{publication}/download-pdf', [ResultPublicationController::class, 'downloadPdf'])
        ->name('download-pdf')
        ->can('download', \Modules\Results\Policies\ResultPublicationPolicy::class);
    Route::get('results/{publication}/download-excel', [ResultPublicationController::class, 'downloadExcel'])
        ->name('download-excel')
        ->can('download', \Modules\Results\Policies\ResultPublicationPolicy::class);
    Route::post('results/{publication}/generate-excel', [ResultPublicationController::class, 'generateExcel'])
        ->name('generate-excel')
        ->can('generateExcel', \Modules\Results\Policies\ResultPublicationPolicy::class);
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
