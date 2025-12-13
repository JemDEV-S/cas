<?php

use Illuminate\Support\Facades\Route;
use Modules\Jury\Http\Controllers\{
    JuryMemberController,
    JuryAssignmentController,
    JuryConflictController
};

/*
 * Rutas API del módulo Jury
 */
Route::middleware(['auth:sanctum'])->prefix('jury')->name('api.jury.')->group(function () {

    // Jury Members API
    Route::prefix('members')->name('members.')->group(function () {
        Route::get('/', [JuryMemberController::class, 'index'])->name('index');
        Route::post('/', [JuryMemberController::class, 'store'])->name('store');
        Route::get('{id}', [JuryMemberController::class, 'show'])->name('show');
        Route::put('{id}', [JuryMemberController::class, 'update'])->name('update');
        Route::delete('{id}', [JuryMemberController::class, 'destroy'])->name('destroy');
        Route::post('{id}/toggle-active', [JuryMemberController::class, 'toggleActive'])->name('toggle-active');
        Route::post('{id}/mark-unavailable', [JuryMemberController::class, 'markUnavailable'])->name('mark-unavailable');
        Route::post('{id}/mark-available', [JuryMemberController::class, 'markAvailable'])->name('mark-available');
        Route::post('{id}/complete-training', [JuryMemberController::class, 'completeTraining'])->name('complete-training');
        Route::get('{id}/statistics', [JuryMemberController::class, 'statistics'])->name('statistics');
    });

    Route::get('members-workload-summary', [JuryMemberController::class, 'workloadSummary'])->name('members.workload-summary');
    Route::get('members-available', [JuryMemberController::class, 'availableForAssignment'])->name('members.available');

    // Jury Assignments API
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', [JuryAssignmentController::class, 'index'])->name('index');
        Route::post('/', [JuryAssignmentController::class, 'store'])->name('store');
        Route::get('{id}', [JuryAssignmentController::class, 'show'])->name('show');
        Route::delete('{id}', [JuryAssignmentController::class, 'destroy'])->name('destroy');
        Route::post('{id}/replace', [JuryAssignmentController::class, 'replace'])->name('replace');
        Route::post('{id}/excuse', [JuryAssignmentController::class, 'excuse'])->name('excuse');
        Route::post('auto-assign', [JuryAssignmentController::class, 'autoAssign'])->name('auto-assign');
    });

    Route::get('assignments/job-posting/{jobPostingId}', [JuryAssignmentController::class, 'byJobPosting'])->name('assignments.by-job-posting');
    Route::get('assignments/job-posting/{jobPostingId}/workload', [JuryAssignmentController::class, 'workloadStatistics'])->name('assignments.workload');
    Route::post('assignments/job-posting/{jobPostingId}/balance', [JuryAssignmentController::class, 'balanceWorkload'])->name('assignments.balance');
    Route::get('assignments/job-posting/{jobPostingId}/available-evaluators', [JuryAssignmentController::class, 'availableEvaluators'])->name('assignments.available-evaluators');

    // Jury Conflicts API
    Route::prefix('conflicts')->name('conflicts.')->group(function () {
        Route::get('/', [JuryConflictController::class, 'index'])->name('index');
        Route::post('/', [JuryConflictController::class, 'store'])->name('store');
        Route::get('{id}', [JuryConflictController::class, 'show'])->name('show');
        Route::post('{id}/move-to-review', [JuryConflictController::class, 'moveToReview'])->name('move-to-review');
        Route::post('{id}/confirm', [JuryConflictController::class, 'confirm'])->name('confirm');
        Route::post('{id}/dismiss', [JuryConflictController::class, 'dismiss'])->name('dismiss');
        Route::post('{id}/resolve', [JuryConflictController::class, 'resolve'])->name('resolve');
        Route::post('{id}/excuse', [JuryConflictController::class, 'excuse'])->name('excuse');
        Route::post('auto-detect', [JuryConflictController::class, 'autoDetect'])->name('auto-detect');
    });

    Route::get('conflicts-statistics', [JuryConflictController::class, 'statistics'])->name('conflicts.statistics');
});
