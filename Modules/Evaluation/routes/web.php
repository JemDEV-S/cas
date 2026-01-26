<?php

use Illuminate\Support\Facades\Route;
use Modules\Evaluation\Http\Controllers\{
    EvaluationController,
    EvaluatorAssignmentController,
    EvaluationCriterionController,
    AutomaticEvaluationController
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

        // Crear evaluación desde una asignación
        Route::get('create', [EvaluationController::class, 'create'])->name('create');
        Route::post('create', [EvaluationController::class, 'store'])->name('store');

        // Ver evaluación específica
        Route::get('{id}', [EvaluationController::class, 'show'])->name('show');

        // Eliminar evaluación completada
        Route::delete('{id}', [EvaluationController::class, 'destroy'])->name('destroy');

        // Formulario de evaluación
        Route::get('{id}/evaluate', [EvaluationController::class, 'evaluate'])->name('evaluate');

        // Ver CV del postulante (iframe)
        Route::get('{id}/view-cv', [EvaluationController::class, 'viewCV'])->name('view-cv');

        // Guardar detalles de evaluación (AJAX)
        Route::post('{id}/details', [EvaluationController::class, 'saveDetail'])->name('save-detail');

        // Enviar evaluación
        Route::post('{id}/submit', [EvaluationController::class, 'submit'])->name('submit');

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

            // Vistas
            Route::get('/', [EvaluatorAssignmentController::class, 'index'])->name('index');
            Route::get('{id}', [EvaluatorAssignmentController::class, 'show'])->name('show');

            // Acciones
            Route::post('/', [EvaluatorAssignmentController::class, 'store'])->name('store');
            Route::post('auto-assign', [EvaluatorAssignmentController::class, 'autoAssign'])->name('auto-assign');
            Route::delete('{id}', [EvaluatorAssignmentController::class, 'destroy'])->name('destroy');

            // AJAX Endpoints (retornan JSON)
            Route::get('available-evaluators', [EvaluatorAssignmentController::class, 'availableEvaluators'])
                ->name('available-evaluators')
                ->withoutMiddleware('can:assign-evaluators'); // Permitir a todos ver evaluadores
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

    // ========================================
    // AUTOMATIC EVALUATIONS - Evaluaciones Automáticas (Fase 4)
    // ========================================
    Route::prefix('automatic-evaluations')
        ->name('evaluation.automatic.')
        ->group(function () {

            // Listado de convocatorias para evaluación automática
            Route::get('/', [AutomaticEvaluationController::class, 'index'])
                ->name('index')
                ->can('viewAny', \Modules\Evaluation\Policies\AutomaticEvaluationPolicy::class);

            // Ver detalles de convocatoria
            Route::get('{id}', [AutomaticEvaluationController::class, 'show'])
                ->name('show')
                ->can('viewAny', \Modules\Evaluation\Policies\AutomaticEvaluationPolicy::class);

            // Ejecutar evaluación automática
            Route::post('{id}/execute', [AutomaticEvaluationController::class, 'execute'])
                ->name('execute')
                ->can('execute', \Modules\Evaluation\Policies\AutomaticEvaluationPolicy::class);

            // Página de progreso de evaluación
            Route::get('{id}/progress', [AutomaticEvaluationController::class, 'progress'])
                ->name('progress')
                ->can('viewAny', \Modules\Evaluation\Policies\AutomaticEvaluationPolicy::class);

            // Endpoint AJAX para obtener estado del progreso
            Route::get('{id}/progress/status', [AutomaticEvaluationController::class, 'getProgress'])
                ->name('progress.status')
                ->can('viewAny', \Modules\Evaluation\Policies\AutomaticEvaluationPolicy::class);

            // Ver detalles de evaluación de una postulación
            Route::get('application/{id}', [AutomaticEvaluationController::class, 'viewApplicationEvaluation'])
                ->name('application')
                ->can('viewAny', \Modules\Evaluation\Policies\AutomaticEvaluationPolicy::class);
        });
});
