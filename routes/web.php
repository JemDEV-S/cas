<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        // Redirigir postulantes a su portal dedicado
        $userRole = auth()->user()->roles->first()?->slug;
        if ($userRole === 'applicant') {
            return redirect()->route('applicant.dashboard');
        }

        return view('dashboard');
    })->name('dashboard');
});
