<?php

use Illuminate\Support\Facades\Route;
use Modules\Evaluation\Http\Controllers\{
    EvaluationController,
    EvaluatorAssignmentController,
    EvaluationCriterionController
};

/*
|--------------------------------------------------------------------------
| Web Routes - Evaluation Module
|--------------------------------------------------------------------------
|
| Rutas web para el módulo de evaluaciones
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    
    // ========================================
    // EVALUATIONS - Gestión de Evaluaciones
    // ========================================
    Route::prefix('evaluations')->name('evaluation.')->group(function () {
        
        // Dashboard del evaluador
        Route::get('/', [EvaluationController::class, 'index'])->name('index');
        
        Route::get('my-evaluations', [EvaluationController::class, 'myEvaluations'])
            ->name('my-evaluations');
        
        // Ver evaluación específica
        Route::get('{id}', [EvaluationController::class, 'show'])->name('show');
        
        // Formulario de evaluación
        Route::get('{id}/evaluate', [EvaluationController::class, 'evaluate'])->name('evaluate');
        
        // Ver historial
        Route::get('{id}/history', [EvaluationController::class, 'history'])->name('history');
    });
    
    // ========================================
    // EVALUATOR ASSIGNMENTS - Asignación (Solo Admin)
    // ========================================
    Route::prefix('evaluator-assignments')
        ->name('evaluator-assignments.')
        ->middleware('can:assign-evaluators')
        ->group(function () {
            
            Route::get('/', [EvaluatorAssignmentController::class, 'index'])->name('index');
            Route::get('{id}', [EvaluatorAssignmentController::class, 'show'])->name('show');
        });
    
    // ========================================
    // EVALUATION CRITERIA - Criterios (Solo Admin)
    // ========================================
    Route::prefix('evaluation-criteria')
        ->name('evaluation-criteria.')
        ->middleware('can:manage-criteria')
        ->group(function () {
            
            Route::get('/', [EvaluationCriterionController::class, 'index'])->name('index');
            Route::get('create', [EvaluationCriterionController::class, 'create'])->name('create');
            Route::post('/', [EvaluationCriterionController::class, 'store'])->name('store');
            Route::get('{id}', [EvaluationCriterionController::class, 'show'])->name('show');
            Route::get('{id}/edit', [EvaluationCriterionController::class, 'edit'])->name('edit');
            Route::put('{id}', [EvaluationCriterionController::class, 'update'])->name('update');
            Route::delete('{id}', [EvaluationCriterionController::class, 'destroy'])->name('destroy');
        });
});