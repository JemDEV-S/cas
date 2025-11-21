<?php

use Illuminate\Support\Facades\Route;
use Modules\JobProfile\Http\Controllers\JobProfileController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('jobprofiles', JobProfileController::class)->names('jobprofile');
});
