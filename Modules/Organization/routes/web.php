<?php

use Illuminate\Support\Facades\Route;
use Modules\Organization\Http\Controllers\OrganizationalUnitController;

Route::middleware(['auth', 'verified'])->prefix('organizational-units')->name('organizational-units.')->group(function () {
    // Search
    Route::get('/search', [OrganizationalUnitController::class, 'search'])->name('search');

    // Tree view
    Route::get('/tree', [OrganizationalUnitController::class, 'tree'])->name('tree');

    // Resource routes
    Route::get('/', [OrganizationalUnitController::class, 'index'])->name('index');
    Route::get('/create', [OrganizationalUnitController::class, 'create'])->name('create');
    Route::post('/', [OrganizationalUnitController::class, 'store'])->name('store');
    Route::get('/{organizationalUnit}', [OrganizationalUnitController::class, 'show'])->name('show');
    Route::get('/{organizationalUnit}/edit', [OrganizationalUnitController::class, 'edit'])->name('edit');
    Route::put('/{organizationalUnit}', [OrganizationalUnitController::class, 'update'])->name('update');
    Route::delete('/{organizationalUnit}', [OrganizationalUnitController::class, 'destroy'])->name('destroy');
});
