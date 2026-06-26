<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\LoginController;
use Modules\Auth\Http\Controllers\RegisterController;
use Modules\Auth\Http\Controllers\KbaRecoveryController;
use Modules\Auth\Http\Controllers\RoleController;
use Modules\Auth\Http\Controllers\PermissionController;

// Guest Routes (Login, Register, Password Recovery via KBA)
Route::middleware('guest')->group(function () {
    // Login
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    // Register
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);

    // Password Recovery (KBA - preguntas basadas en hoja de vida)
    Route::prefix('password/recover')->name('password.recover.')->group(function () {
        Route::get('/', [KbaRecoveryController::class, 'showStartForm'])->name('start');
        Route::post('/', [KbaRecoveryController::class, 'start'])->name('start.submit');
        Route::get('/questions', [KbaRecoveryController::class, 'showQuestion'])->name('questions');
        Route::post('/answer', [KbaRecoveryController::class, 'submitAnswer'])->name('answer');
        Route::get('/reset/{token}', [KbaRecoveryController::class, 'showResetForm'])->name('reset');
        Route::post('/reset', [KbaRecoveryController::class, 'reset'])->name('reset.submit');
        Route::get('/locked', [KbaRecoveryController::class, 'showLocked'])->name('locked');
    });
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Roles Management
    Route::resource('roles', RoleController::class);
    Route::post('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('roles.toggle-status');

    // Permissions Management
    Route::resource('permissions', PermissionController::class)->only(['index', 'show']);
});
