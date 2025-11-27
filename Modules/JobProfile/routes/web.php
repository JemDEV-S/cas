<?php

use Illuminate\Support\Facades\Route;
use Modules\JobProfile\Http\Controllers\JobProfileController;
use Modules\JobProfile\Http\Controllers\PositionCodeController;
use Modules\JobProfile\Http\Controllers\ReviewController;
use Modules\JobProfile\Http\Controllers\CriterionController;
use Modules\JobProfile\Http\Controllers\VacancyController;

Route::middleware(['auth', 'verified'])->prefix('jobprofile')->name('jobprofile.')->group(function () {

    // Job Profiles CRUD
    Route::resource('profiles', JobProfileController::class)->except(['index']);
    Route::get('/', [JobProfileController::class, 'index'])->name('index');

    // Position Codes
    Route::prefix('positions')->name('positions.')->group(function () {
        Route::get('/', [PositionCodeController::class, 'index'])->name('index');
        Route::get('/create', [PositionCodeController::class, 'create'])->name('create');
        Route::post('/', [PositionCodeController::class, 'store'])->name('store');
        Route::get('/{id}', [PositionCodeController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PositionCodeController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PositionCodeController::class, 'update'])->name('update');
        Route::delete('/{id}', [PositionCodeController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/activate', [PositionCodeController::class, 'activate'])->name('activate');
        Route::post('/{id}/deactivate', [PositionCodeController::class, 'deactivate'])->name('deactivate');
    });

    // Review Process
    Route::prefix('review')->name('review.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::get('/{id}', [ReviewController::class, 'show'])->name('show');
        Route::post('/{id}/submit', [ReviewController::class, 'submit'])->name('submit');
        Route::post('/{id}/request-modification', [ReviewController::class, 'requestModification'])->name('request-modification');
        Route::post('/{id}/approve', [ReviewController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [ReviewController::class, 'reject'])->name('reject');
    });

    // Evaluation Criteria
    Route::prefix('criteria')->name('criteria.')->group(function () {
        Route::post('/', [CriterionController::class, 'store'])->name('store');
        Route::put('/{id}', [CriterionController::class, 'update'])->name('update');
        Route::delete('/{id}', [CriterionController::class, 'destroy'])->name('destroy');
    });

    // Vacancies
    Route::prefix('vacancies')->name('vacancies.')->group(function () {
        Route::get('/profile/{jobProfileId}', [VacancyController::class, 'index'])->name('index');
        Route::post('/profile/{jobProfileId}/generate', [VacancyController::class, 'generate'])->name('generate');
        Route::post('/{vacancyId}/declare-vacant', [VacancyController::class, 'declareVacant'])->name('declare-vacant');
    });
});
