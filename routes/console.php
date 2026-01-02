<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar actualización automática de fases de convocatorias
Schedule::command('jobposting:update-phases')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Log::info('JobPosting phases updated successfully');
    })
    ->onFailure(function () {
        \Log::error('JobPosting phases update failed');
    });
