<?php

use Illuminate\Support\Facades\Route;
use Modules\Document\Http\Controllers\DocumentSignatureController;
use Modules\Document\Http\Controllers\DocumentRegenerationController;

// Rutas API para FIRMA PERÚ
// IMPORTANTE: Estas rutas NO usan sesión ni autenticación de usuario
// Usan validación por tokens de cache de un solo uso
// Son llamadas desde el componente local de FIRMA PERÚ (localhost:48596)
Route::prefix('documents')
    ->name('documents.')
    ->group(function () {
        // Endpoints para FIRMA PERÚ
        // Estas rutas NO usan SubstituteBindings para evitar que se apliquen policies automáticamente
        // La validación se hace manualmente con tokens de cache

        Route::post('/signature-params', [DocumentSignatureController::class, 'getSignatureParams'])
            ->name('signature-params');

        Route::get('/{document}/download-for-signature', [DocumentSignatureController::class, 'downloadForSignature'])
            ->name('download-for-signature');

        Route::post('/{document}/upload-signed', [DocumentSignatureController::class, 'uploadSigned'])
            ->name('upload-signed');

        Route::get('/signature-stamp', [DocumentSignatureController::class, 'getSignatureStamp'])
            ->name('signature-stamp');

        // Ruta para regenerar documentos de convocatoria (requiere autenticación)
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::post('/regenerate-convocatoria/{jobPostingId}', [DocumentRegenerationController::class, 'regenerateConvocatoria'])
                ->name('regenerate-convocatoria');
        });
    });
