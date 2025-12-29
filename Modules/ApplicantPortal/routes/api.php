<?php

use Illuminate\Support\Facades\Route;
use Modules\ApplicantPortal\Http\Controllers\ApplicantPortalController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('applicantportals', ApplicantPortalController::class)->names('applicantportal');
});
