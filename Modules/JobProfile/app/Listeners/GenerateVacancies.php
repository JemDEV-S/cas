<?php

namespace Modules\JobProfile\Listeners;

use Modules\JobProfile\Events\JobProfileApproved;
use Modules\JobProfile\Events\VacanciesGenerated;
use Modules\JobProfile\Services\VacancyService;
use Illuminate\Support\Facades\Log;

class GenerateVacancies
{
    public function __construct(
        protected VacancyService $vacancyService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(JobProfileApproved $event): void
    {
        try {
            // Generar vacantes automáticamente
            $vacancies = $this->vacancyService->generateVacancies($event->jobProfile);

            // Disparar evento de vacantes generadas
            event(new VacanciesGenerated($event->jobProfile, $vacancies));

            Log::info('Vacantes generadas automáticamente', [
                'job_profile_id' => $event->jobProfile->id,
                'total_vacancies' => count($vacancies),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al generar vacantes', [
                'job_profile_id' => $event->jobProfile->id,
                'error' => $e->getMessage(),
            ]);

            // No lanzar excepción para no interrumpir el flujo
            // Las vacantes pueden generarse manualmente después
        }
    }
}
