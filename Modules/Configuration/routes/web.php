<?php

use Illuminate\Support\Facades\Route;
use Modules\Configuration\Http\Controllers\ConfigurationController;

/*
|--------------------------------------------------------------------------
| Web Routes - Configuration Module
|--------------------------------------------------------------------------
*/

// Rutas de configuración del sistema
Route::prefix('configuration')->name('configuration.')->group(function () {

    // Todas las rutas requieren autenticación
    Route::middleware(['auth'])->group(function () {
        // Listar todos los grupos de configuración
        Route::get('/', [ConfigurationController::class, 'index'])->name('index');

        // Editar configuraciones de un grupo
        Route::get('/edit/{group?}', [ConfigurationController::class, 'edit'])->name('edit');

        // Actualizar configuraciones
        Route::put('/update/{group}', [ConfigurationController::class, 'update'])->name('update');

        // Resetear una configuración a su valor por defecto
        Route::get('/reset/{id}', [ConfigurationController::class, 'reset'])->name('reset');

        // Ver historial de cambios de una configuración
        Route::get('/history/{id}', [ConfigurationController::class, 'history'])->name('history');
    });

});
