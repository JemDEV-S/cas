<?php

use Illuminate\Support\Facades\Route;
use Modules\Document\Http\Controllers\DocumentController;
use Modules\Document\Http\Controllers\DocumentSignatureController;

Route::middleware(['auth', 'verified'])->prefix('documents')->name('documents.')->group(function () {
    // Listado y visualización de documentos
    Route::get('/', [DocumentController::class, 'index'])->name('index');
    Route::get('/pending-signatures', [DocumentController::class, 'pendingSignatures'])->name('pending-signatures');
    Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
    Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download');
    Route::get('/{document}/view', [DocumentController::class, 'view'])->name('view');
    Route::post('/{document}/regenerate', [DocumentController::class, 'regenerate'])->name('regenerate');
    Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');

    // Firma digital
    Route::get('/{document}/sign', [DocumentSignatureController::class, 'index'])->name('sign');
    Route::post('/{document}/sign/cancel', [DocumentSignatureController::class, 'cancel'])->name('sign.cancel');
});
