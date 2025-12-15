<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\ReniecValidationController;

/*
|--------------------------------------------------------------------------
| API Routes - Auth Module
|--------------------------------------------------------------------------
|
| Rutas de API para el módulo de autenticación
|
*/

// Rutas públicas (sin autenticación)
Route::prefix('auth')->name('auth.')->group(function () {

    // Validación de DNI con RENIEC
    Route::post('/validate-dni', [ReniecValidationController::class, 'validateDni'])
        ->name('validate-dni');

    // Consulta de DNI (solo lectura)
    Route::get('/consultar-dni/{dni}', [ReniecValidationController::class, 'consultarDni'])
        ->name('consultar-dni');

    // Estado del servicio RENIEC
    Route::get('/reniec/status', [ReniecValidationController::class, 'checkStatus'])
        ->name('reniec.status');
});

// Rutas protegidas (con autenticación)
Route::middleware(['auth:sanctum'])->prefix('auth')->name('auth.')->group(function () {
    // Aquí puedes agregar rutas que requieran autenticación
});
