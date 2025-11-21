<?php

use Illuminate\Support\Facades\Route;
use Modules\JobProfile\Http\Controllers\JobProfileController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('jobprofiles', JobProfileController::class)->names('jobprofile');
});
