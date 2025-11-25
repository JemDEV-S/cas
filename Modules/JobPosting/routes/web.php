<?php

use Illuminate\Support\Facades\Route;
use Modules\JobPosting\Http\Controllers\JobPostingController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('jobpostings', JobPostingController::class)->names('jobposting');
});
