<?php

use Illuminate\Support\Facades\Route;
use Modules\Evaluation\Http\Controllers\{
    EvaluationController,
    EvaluatorAssignmentController,
    EvaluationCriterionController
};

/*
|--------------------------------------------------------------------------
| API Routes - Evaluation Module
|--------------------------------------------------------------------------
|
| Rutas API para el módulo de evaluaciones
|
*/

Route::middleware(['auth:sanctum'])->prefix('evaluation')->group(function () {
    
    // ========================================
    // EVALUATIONS - Gestión de Evaluaciones
    // ========================================
    Route::prefix('evaluations')->name('evaluations.')->group(function () {
        
        // Rutas públicas (evaluadores)
        Route::get('my-evaluations', [EvaluationController::class, 'myEvaluations'])
            ->name('my-evaluations');
        
        Route::get('stats', [EvaluationController::class, 'stats'])
            ->name('stats');
        
        // CRUD básico
        Route::get('/', [EvaluationController::class, 'index'])
            ->name('index');
        
        Route::post('/', [EvaluationController::class, 'store'])
            ->name('store')
            ->middleware('can:create-evaluations');
        
        Route::get('/{id}', [EvaluationController::class, 'show'])
            ->name('show');
        
        Route::put('/{id}', [EvaluationController::class, 'update'])
            ->name('update');
        
        Route::delete('/{id}', [EvaluationController::class, 'destroy'])
            ->name('destroy')
            ->middleware('can:delete-evaluations');
        
        // Acciones específicas
        Route::post('/{id}/details', [EvaluationController::class, 'saveDetail'])
            ->name('save-detail')
            ->middleware('throttle:60,1'); // Límite de 60 requests por minuto
        
        Route::post('/{id}/submit', [EvaluationController::class, 'submit'])
            ->name('submit');
        
        Route::post('/{id}/modify', [EvaluationController::class, 'modifySubmitted'])
            ->name('modify-submitted')
            ->middleware('role:Administrador General|Administrador de RRHH');
        
        Route::get('/{id}/history', [EvaluationController::class, 'history'])
            ->name('history');
    });
    
    // ========================================
    // EVALUATOR ASSIGNMENTS - Asignación de Evaluadores
    // ========================================
    Route::prefix('evaluator-assignments')->name('evaluator-assignments.')->group(function () {
        
        // Rutas públicas (evaluadores)
        Route::get('my-assignments', [EvaluatorAssignmentController::class, 'myAssignments'])
            ->name('my-assignments');
        
        // CRUD y listados
        Route::get('/', [EvaluatorAssignmentController::class, 'index'])
            ->name('index')
            ->middleware('can:view-assignments');
        
        Route::get('/{id}', [EvaluatorAssignmentController::class, 'show'])
            ->name('show');
        
        Route::get('by-evaluator/{evaluatorId}', [EvaluatorAssignmentController::class, 'byEvaluator'])
            ->name('by-evaluator')
            ->middleware('can:view-assignments');
        
        // Asignación manual
        Route::post('assign', [EvaluatorAssignmentController::class, 'assign'])
            ->name('assign')
            ->middleware('can:assign-evaluators');
        
        // Asignación automática
        Route::post('auto-assign', [EvaluatorAssignmentController::class, 'autoAssign'])
            ->name('auto-assign')
            ->middleware('can:assign-evaluators');
        
        // Reasignación
        Route::post('/{id}/reassign', [EvaluatorAssignmentController::class, 'reassign'])
            ->name('reassign')
            ->middleware('can:assign-evaluators');
        
        // Cancelar
        Route::post('/{id}/cancel', [EvaluatorAssignmentController::class, 'cancel'])
            ->name('cancel')
            ->middleware('can:assign-evaluators');
        
        // Estadísticas y carga de trabajo
        Route::get('stats', [EvaluatorAssignmentController::class, 'stats'])
            ->name('stats')
            ->middleware('can:view-assignments');
        
        Route::post('workload', [EvaluatorAssignmentController::class, 'workload'])
            ->name('workload')
            ->middleware('can:view-assignments');
    });
    
    // ========================================
    // EVALUATION CRITERIA - Criterios de Evaluación
    // ========================================
    Route::prefix('evaluation-criteria')->name('evaluation-criteria.')->group(function () {
        
        // Consultas públicas (para evaluadores)
        Route::get('by-phase/{phaseId}', [EvaluationCriterionController::class, 'byPhase'])
            ->name('by-phase');
        
        Route::get('for-evaluation', [EvaluationCriterionController::class, 'forEvaluation'])
            ->name('for-evaluation');
        
        // CRUD (solo administradores)
        Route::middleware('can:manage-criteria')->group(function () {
            Route::get('/', [EvaluationCriterionController::class, 'index'])
                ->name('index');
            
            Route::post('/', [EvaluationCriterionController::class, 'store'])
                ->name('store');
            
            Route::get('/{id}', [EvaluationCriterionController::class, 'show'])
                ->name('show');
            
            Route::put('/{id}', [EvaluationCriterionController::class, 'update'])
                ->name('update');
            
            Route::delete('/{id}', [EvaluationCriterionController::class, 'destroy'])
                ->name('destroy');
            
            Route::post('/{id}/toggle-active', [EvaluationCriterionController::class, 'toggleActive'])
                ->name('toggle-active');
            
            Route::post('reorder', [EvaluationCriterionController::class, 'reorder'])
                ->name('reorder');
        });
    });
}); 