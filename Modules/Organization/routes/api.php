<?php

// use Illuminate\Support\Facades\Route;
// use Modules\Organization\Http\Controllers\Api\OrganizationalUnitApiController;

// Route::middleware(['auth:sanctum'])->prefix('v1/organizational-units')->name('api.organizational-units.')->group(function () {

//     // CRUD básico
//     Route::get('/', [OrganizationalUnitApiController::class, 'index'])->name('index');
//     Route::post('/', [OrganizationalUnitApiController::class, 'store'])->name('store');
//     Route::get('/{organizationalUnit}', [OrganizationalUnitApiController::class, 'show'])->name('show');
//     Route::put('/{organizationalUnit}', [OrganizationalUnitApiController::class, 'update'])->name('update');
//     Route::delete('/{organizationalUnit}', [OrganizationalUnitApiController::class, 'destroy'])->name('destroy');

//     // Operaciones de jerarquía
//     Route::get('/tree/full', [OrganizationalUnitApiController::class, 'tree'])->name('tree');
//     Route::get('/tree/flat', [OrganizationalUnitApiController::class, 'flatTree'])->name('flat-tree');
//     Route::get('/{organizationalUnit}/ancestors', [OrganizationalUnitApiController::class, 'ancestors'])->name('ancestors');
//     Route::get('/{organizationalUnit}/descendants', [OrganizationalUnitApiController::class, 'descendants'])->name('descendants');
//     Route::get('/{organizationalUnit}/children', [OrganizationalUnitApiController::class, 'children'])->name('children');
//     Route::get('/{organizationalUnit}/siblings', [OrganizationalUnitApiController::class, 'siblings'])->name('siblings');

//     // Operaciones especiales
//     Route::post('/{organizationalUnit}/move', [OrganizationalUnitApiController::class, 'move'])->name('move');
//     Route::get('/select/options', [OrganizationalUnitApiController::class, 'selectOptions'])->name('select-options');
//     Route::post('/search', [OrganizationalUnitApiController::class, 'search'])->name('search');

//     // Utilidades
//     Route::get('/statistics/all', [OrganizationalUnitApiController::class, 'statistics'])->name('statistics');
//     Route::get('/validate/tree', [OrganizationalUnitApiController::class, 'validate'])->name('validate');
//     Route::get('/export/tree', [OrganizationalUnitApiController::class, 'export'])->name('export');
//     Route::post('/rebuild/closure', [OrganizationalUnitApiController::class, 'rebuildClosure'])->name('rebuild-closure');
// });
