<?php

use Illuminate\Support\Facades\Route;
use Modules\Application\Http\Controllers\ApplicationController;
use Modules\Application\Http\Controllers\Api\IaJobController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('applications', ApplicationController::class)->names('application');
});

// Rutas para el agente IA (autenticación por Bearer token)
Route::middleware(['ia.token'])->prefix('ia')->group(function () {
    Route::get('jobs', [IaJobController::class, 'index']);
    Route::post('jobs/{id}/result', [IaJobController::class, 'storeResult']);
    Route::post('jobs/{id}/error', [IaJobController::class, 'storeError']);
    Route::get('stats', [IaJobController::class, 'stats']);
});
