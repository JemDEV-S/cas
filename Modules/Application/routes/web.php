<?php

use Illuminate\Support\Facades\Route;
use Modules\Application\Http\Controllers\ApplicationController;
use Modules\Application\Http\Controllers\Admin\ApplicationEvaluationController;
use Modules\Application\Http\Controllers\Admin\EligibilityOverrideController;

/*
 * Rutas del módulo Application
 * Todas las rutas requieren autenticación
 */
Route::middleware(['auth', 'verified'])->group(function () {
    // Rutas de recurso estándar (index, create, store, show, edit, update, destroy)
    Route::resource('applications', ApplicationController::class)->names('application');

    // Rutas adicionales
    Route::prefix('applications')->name('application.')->group(function () {
        // Desistir de postulación
        Route::post('{id}/withdraw', [ApplicationController::class, 'withdraw'])->name('withdraw');

        // Evaluar elegibilidad
        Route::post('{id}/evaluate-eligibility', [ApplicationController::class, 'evaluateEligibility'])
            ->name('evaluate-eligibility')
            ->middleware('can:evaluate,id');

        // Ver historial de cambios
        Route::get('{id}/history', [ApplicationController::class, 'history'])->name('history');

        // Gestión de documentos (se implementarán después)
        // Route::get('{id}/documents', [ApplicationDocumentController::class, 'index'])->name('documents.index');
        // Route::post('{id}/documents', [ApplicationDocumentController::class, 'store'])->name('documents.store');
        // Route::delete('documents/{documentId}', [ApplicationDocumentController::class, 'destroy'])->name('documents.destroy');
        // Route::get('documents/{documentId}/download', [ApplicationDocumentController::class, 'download'])->name('documents.download');
    });
});

/*
 * Rutas de administración - Evaluación automática
 */
Route::middleware(['auth', 'verified'])->prefix('admin/applications')->name('admin.applications.')->group(function () {
    // Dashboard de evaluación por convocatoria
    Route::get('evaluation/{posting}', [ApplicationEvaluationController::class, 'index'])
        ->name('evaluation.index');

    // Ejecutar evaluación automática masiva
    Route::post('evaluation/{posting}/evaluate', [ApplicationEvaluationController::class, 'evaluate'])
        ->name('evaluation.evaluate');

    // Publicar resultados de elegibilidad
    Route::post('evaluation/{posting}/publish', [ApplicationEvaluationController::class, 'publish'])
        ->name('evaluation.publish');

    // Override manual de resultado
    Route::post('evaluation/{application}/override', [ApplicationEvaluationController::class, 'override'])
        ->name('evaluation.override');

    // Ver detalle de evaluación
    Route::get('evaluation/{application}/detail', [ApplicationEvaluationController::class, 'show'])
        ->name('evaluation.show');
});

/*
 * Rutas de administración - Reevaluación de Elegibilidad (Reclamos)
 */
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Lista de postulaciones para reevaluar por convocatoria
    Route::get('eligibility-override/{posting}', [EligibilityOverrideController::class, 'index'])
        ->name('eligibility-override.index');

    // Detalle de postulación para reevaluar
    Route::get('eligibility-override/application/{application}', [EligibilityOverrideController::class, 'show'])
        ->name('eligibility-override.show');

    // Aprobar reevaluación (cambiar a APTO)
    Route::post('eligibility-override/{application}/approve', [EligibilityOverrideController::class, 'approve'])
        ->name('eligibility-override.approve');

    // Rechazar reevaluación (mantener NO_APTO)
    Route::post('eligibility-override/{application}/reject', [EligibilityOverrideController::class, 'reject'])
        ->name('eligibility-override.reject');

    // Generar PDF de resolución
    Route::get('eligibility-override/{application}/pdf', [EligibilityOverrideController::class, 'generatePdf'])
        ->name('eligibility-override.pdf');
});
