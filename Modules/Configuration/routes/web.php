<?php

use Illuminate\Support\Facades\Route;
use Modules\Configuration\Http\Controllers\ConfigurationController;

/*
|--------------------------------------------------------------------------
| Web Routes - Configuration Module
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->prefix('configuration')->name('configuration.')->group(function () {

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
