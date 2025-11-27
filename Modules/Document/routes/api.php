<?php

use Illuminate\Support\Facades\Route;
use Modules\Document\Http\Controllers\DocumentSignatureController;

// Rutas API para FIRMA PERÚ (sin autenticación por tokens específicos)
Route::prefix('documents')->name('api.documents.')->group(function () {
    // Endpoints para FIRMA PERÚ
    Route::post('/signature-params', [DocumentSignatureController::class, 'getSignatureParams'])->name('signature-params');
    Route::get('/{document}/download-for-signature', [DocumentSignatureController::class, 'downloadForSignature'])->name('download-for-signature');
    Route::post('/{document}/upload-signed', [DocumentSignatureController::class, 'uploadSigned'])->name('upload-signed');
    Route::get('/signature-stamp', [DocumentSignatureController::class, 'getSignatureStamp'])->name('signature-stamp');
});
