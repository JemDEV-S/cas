<?php

use Illuminate\Support\Facades\Route;
use Modules\Results\Http\Controllers\ResultsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('results', ResultsController::class)->names('results');
});
