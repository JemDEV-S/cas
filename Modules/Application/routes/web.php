<?php

use Illuminate\Support\Facades\Route;
use Modules\Application\Http\Controllers\ApplicationController;

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
