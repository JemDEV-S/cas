<?php

use Illuminate\Support\Facades\Route;
use Modules\Application\Http\Controllers\ApplicationController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('applications', ApplicationController::class)->names('application');
});
