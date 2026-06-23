<?php

use Illuminate\Support\Facades\Route;
use Modules\ApplicantPortal\Http\Controllers\DashboardController;
use Modules\ApplicantPortal\Http\Controllers\ApplicationController;
use Modules\ApplicantPortal\Http\Controllers\JobPostingController;
use Modules\ApplicantPortal\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Applicant Portal Routes
|--------------------------------------------------------------------------
|
| Portal web para postulantes. Todas las rutas requieren autenticación
| y el rol 'applicant'. Prefijo: /portal
|
*/

Route::prefix('portal')->middleware(['auth', 'role:applicant'])->name('applicant.')->group(function () {

    // Dashboard Principal
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // ========================================
    // Convocatorias (Job Postings)
    // ========================================
    Route::prefix('convocatorias')->name('job-postings.')->group(function () {
        // Listar convocatorias activas
        Route::get('/', [JobPostingController::class, 'index'])
            ->name('index');

        // Ver detalle de convocatoria
        Route::get('/{id}', [JobPostingController::class, 'show'])
            ->name('show');

        // Formulario de postulación a un perfil específico
        Route::get('/{postingId}/postular/{profileId}', [JobPostingController::class, 'apply'])
            ->name('apply');

        // Enviar postulación
        Route::post('/{postingId}/postular/{profileId}', [JobPostingController::class, 'storeApplication'])
            ->name('apply.store');
    });

    // ========================================
    // Mis Postulaciones (Applications)
    // ========================================
    Route::prefix('postulaciones')->name('applications.')->group(function () {
        // Listar mis postulaciones
        Route::get('/', [ApplicationController::class, 'index'])
            ->name('index');

        // Ver detalle de postulación
        Route::get('/{id}', [ApplicationController::class, 'show'])
            ->name('show');

        // Desistir de postulación
        Route::post('/{id}/desistir', [ApplicationController::class, 'withdraw'])
            ->name('withdraw');

        // Descargar documento de postulación
        Route::get('/{id}/documentos/{documentId}', [ApplicationController::class, 'downloadDocument'])
            ->name('download-document');

        // Descargar ficha de postulación PDF
        Route::get('/{id}/ficha-pdf', [ApplicationController::class, 'downloadPdf'])
            ->name('download-pdf');

        // Enviar postulación
        Route::post('/{id}/enviar', [ApplicationController::class, 'submit'])
            ->name('submit');

        // Subir CV documentado
        Route::get('/{id}/subir-cv', [ApplicationController::class, 'showUploadCvForm'])
            ->name('upload-cv.form');
        Route::post('/{id}/subir-cv', [ApplicationController::class, 'uploadCv'])
            ->name('upload-cv');

        // Ver CV documentado (abre en nueva pestaña)
        Route::get('/{id}/ver-cv', [ApplicationController::class, 'viewCv'])
            ->name('view-cv');
    });

    // ========================================
    // Mi Perfil (Profile)
    // ========================================
    Route::prefix('perfil')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::put('/actualizar', [ProfileController::class, 'update'])->name('update');
        Route::put('/contrasena', [ProfileController::class, 'updatePassword'])->name('update-password');
    });
});
