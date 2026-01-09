<?php

namespace Modules\Application\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Application\Services\AutoGraderService;
use Modules\Application\Entities\Application;
use Illuminate\Support\Facades\Log;

class EvaluateApplicationBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutos
    public $backoff = 60; // Esperar 60 segundos entre reintentos

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $applicationIds,
        public string $userId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AutoGraderService $autoGrader): void
    {
        Log::info('Iniciando evaluación de lote', [
            'batch_size' => count($this->applicationIds),
            'user_id' => $this->userId
        ]);

        $startTime = microtime(true);
        $stats = [
            'total' => count($this->applicationIds),
            'evaluated' => 0,
            'eligible' => 0,
            'not_eligible' => 0,
            'errors' => 0,
        ];

        $applications = Application::whereIn('id', $this->applicationIds)
            ->with([
                'academics.career',
                'experiences',
                'trainings',
                'professionalRegistrations',
                'knowledge',
                'vacancy.jobProfileRequest.careers'
            ])
            ->get();

        foreach ($applications as $application) {
            try {
                $result = $autoGrader->evaluateEligibility($application);
                $autoGrader->applyAutoGrading($application, $this->userId);

                $stats['evaluated']++;

                if ($result['is_eligible']) {
                    $stats['eligible']++;
                } else {
                    $stats['not_eligible']++;
                }

                Log::info('Postulación evaluada', [
                    'application_id' => $application->id,
                    'application_code' => $application->code,
                    'applicant_name' => $application->full_name,
                    'result' => $result['is_eligible'] ? 'APTO' : 'NO_APTO',
                    'reasons' => $result['reasons'] ?? [],
                ]);

            } catch (\Exception $e) {
                $stats['errors']++;

                Log::error('Error evaluando postulación en lote', [
                    'application_id' => $application->id,
                    'application_code' => $application->code,
                    'applicant_name' => $application->full_name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Continuar con la siguiente postulación
                continue;
            }
        }

        $duration = round(microtime(true) - $startTime, 2);

        Log::info('Lote de evaluación completado', [
            'statistics' => $stats,
            'duration_seconds' => $duration,
            'applications_per_second' => round($stats['evaluated'] / max($duration, 1), 2),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de evaluación falló completamente', [
            'batch_size' => count($this->applicationIds),
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Aquí puedes agregar notificación al administrador
        // event(new EvaluationJobFailed($this->applicationIds, $exception));
    }
}
