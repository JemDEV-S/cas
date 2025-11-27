<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;
use Modules\User\Http\Controllers\ProfileController;
use Modules\User\Http\Controllers\PreferenceController;
use Modules\User\Http\Controllers\AssignmentWebController;

// Profile Routes (authenticated user's own profile)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/profile/preferences', [PreferenceController::class, 'edit'])->name('profile.preferences');
    Route::put('/profile/preferences', [PreferenceController::class, 'update'])->name('profile.preferences.update');
});

// User Management Routes (admin functionality)
Route::middleware(['auth', 'verified'])->prefix('users')->name('users.')->group(function () {
    // Resource routes
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{user}', [UserController::class, 'show'])->name('show');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');

    // Additional routes
    Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
    Route::get('/export', [UserController::class, 'export'])->name('export');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::prefix('assignments')->name('assignments.')->group(function () {
        
        // Lista de asignaciones
        Route::get('/', [AssignmentWebController::class, 'index'])
            ->name('index')
            ->middleware('permission:user.view.assignments');
        
        // Formulario de nueva asignación
        Route::get('/create', [AssignmentWebController::class, 'create'])
            ->name('create')
            ->middleware('permission:user.assign.organization');
        
        // Guardar nueva asignación
        Route::post('/', [AssignmentWebController::class, 'store'])
            ->name('store')
            ->middleware('permission:user.assign.organization');
        
        // Ver detalle de asignación
        Route::get('/{assignment}', [AssignmentWebController::class, 'show'])
            ->name('show')
            ->middleware('permission:user.view.assignments');
        
        // Formulario de edición
        Route::get('/{assignment}/edit', [AssignmentWebController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:user.update.assignment');
        
        // Actualizar asignación
        Route::put('/{assignment}', [AssignmentWebController::class, 'update'])
            ->name('update')
            ->middleware('permission:user.update.assignment');
        
        // Eliminar asignación
        Route::delete('/{assignment}', [AssignmentWebController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:user.unassign.organization');
        
        // Formulario de asignación masiva
        Route::get('/bulk/create', [AssignmentWebController::class, 'bulkCreate'])
            ->name('bulk.create')
            ->middleware('permission:user.assign.organization');
        
        // Procesar asignación masiva
        Route::post('/bulk/store', [AssignmentWebController::class, 'bulkStore'])
            ->name('bulk.store')
            ->middleware('permission:user.assign.organization');
        
        // Formulario de transferencia
        Route::get('/transfer/create', [AssignmentWebController::class, 'transferCreate'])
            ->name('transfer.create')
            ->middleware('permission:user.transfer.organization');
        
        // Procesar transferencia
        Route::post('/transfer/store', [AssignmentWebController::class, 'transferStore'])
            ->name('transfer.store')
            ->middleware('permission:user.transfer.organization');
    });

    // Rutas relacionadas con usuarios
    Route::prefix('users')->name('users.')->group(function () {
        
        // Vista de asignaciones de un usuario
        Route::get('/{user}/assignments', [AssignmentWebController::class, 'userAssignments'])
            ->name('assignments')
            ->middleware('permission:user.view.assignments');
        
        // Cambiar unidad principal
        Route::post('/{user}/change-primary', [AssignmentWebController::class, 'changePrimary'])
            ->name('change-primary')
            ->middleware('permission:user.update.assignment');
    });

    // Rutas relacionadas con unidades organizacionales
    Route::prefix('organizational-units')->name('organizational-units.')->group(function () {
        
        // Vista de usuarios de una unidad
        Route::get('/{unit}/users', [AssignmentWebController::class, 'unitUsers'])
            ->name('users')
            ->middleware('permission:user.view.assignments');
    });
});
