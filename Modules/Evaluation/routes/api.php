<?php

// Agregar a tus rutas API o web (dentro del grupo auth)

use Modules\Application\Entities\Application;

// Endpoint para cargar postulaciones por convocatoria (AJAX)
Route::get('/api/applications', function(Request $request) {
    $query = Application::query()
        ->with('applicant:id,name,dni,email');

    if ($request->has('job_posting_id')) {
        $query->where('job_profile_vacancy_id', $request->job_posting_id);
    }

    if ($request->has('search')) {
        $search = $request->search;
        $query->whereHas('applicant', function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('dni', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    $applications = $query->get()->map(function($app) {
        return [
            'id' => $app->id,
            'code' => $app->code,
            'full_name' => $app->applicant->name ?? 'N/A',
            'dni' => $app->applicant->dni ?? '',
            'email' => $app->applicant->email ?? '',
            'status' => $app->status->value ?? '',
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $applications
    ]);
})->middleware(['auth'])->name('api.applications.index');
