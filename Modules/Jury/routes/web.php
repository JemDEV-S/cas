<?php

use Illuminate\Support\Facades\Route;
use Modules\Jury\Http\Controllers\{
    JuryMemberController,
    JuryAssignmentController,
    JuryConflictController
};

/*
 * Rutas Web del módulo Jury
 * Requieren autenticación
 */
Route::middleware(['auth', 'verified'])->group(function () {

    // Jury Members
    Route::resource('jury-members', JuryMemberController::class)->names('jury-members');

    Route::prefix('jury-members')->name('jury-members.')->group(function () {
        Route::get('{id}/statistics', [JuryMemberController::class, 'statistics'])->name('statistics');
        Route::get('workload-summary', [JuryMemberController::class, 'workloadSummary'])->name('workload-summary');
        Route::get('available-for-assignment', [JuryMemberController::class, 'availableForAssignment'])->name('available-for-assignment');
    });

    // Jury Assignments
    Route::resource('jury-assignments', JuryAssignmentController::class)->names('jury-assignments');

    Route::prefix('jury-assignments')->name('jury-assignments.')->group(function () {
        Route::post('{id}/replace', [JuryAssignmentController::class, 'replace'])->name('replace');
        Route::post('{id}/excuse', [JuryAssignmentController::class, 'excuse'])->name('excuse');
        Route::post('auto-assign', [JuryAssignmentController::class, 'autoAssign'])->name('auto-assign');
        Route::get('job-posting/{jobPostingId}', [JuryAssignmentController::class, 'byJobPosting'])->name('by-job-posting');
        Route::get('job-posting/{jobPostingId}/workload', [JuryAssignmentController::class, 'workloadStatistics'])->name('workload-statistics');
        Route::post('job-posting/{jobPostingId}/balance', [JuryAssignmentController::class, 'balanceWorkload'])->name('balance-workload');
        Route::get('job-posting/{jobPostingId}/available-evaluators', [JuryAssignmentController::class, 'availableEvaluators'])->name('available-evaluators');
    });

    // Jury Conflicts
    Route::resource('jury-conflicts', JuryConflictController::class)->names('jury-conflicts');

    Route::prefix('jury-conflicts')->name('jury-conflicts.')->group(function () {
        Route::post('{id}/move-to-review', [JuryConflictController::class, 'moveToReview'])->name('move-to-review');
        Route::post('{id}/confirm', [JuryConflictController::class, 'confirm'])->name('confirm');
        Route::post('{id}/dismiss', [JuryConflictController::class, 'dismiss'])->name('dismiss');
        Route::post('{id}/resolve', [JuryConflictController::class, 'resolve'])->name('resolve');
        Route::post('{id}/excuse', [JuryConflictController::class, 'excuse'])->name('excuse');
        Route::post('auto-detect', [JuryConflictController::class, 'autoDetect'])->name('auto-detect');
        Route::get('statistics', [JuryConflictController::class, 'statistics'])->name('statistics');
    });
});
