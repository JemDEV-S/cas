<?php

namespace Modules\Application\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Application\Entities\Application;
use Modules\Application\Services\AutoGraderService;

class EvaluateApplicationBatch implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600; // 10 minutos
    public $backoff = 60; // Esperar 60 segundos entre reintentos

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $applicationIds,
        public string $userId,
        public string $jobPostingId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AutoGraderService $autoGrader): void
    {
        // Si el batch fue cancelado, salir
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        Log::info('Iniciando evaluación de lote', [
            'batch_size' => count($this->applicationIds),
            'user_id' => $this->userId,
            'job_posting_id' => $this->jobPostingId,
        ]);

        $applications = Application::whereIn('id', $this->applicationIds)
            ->with([
                'academics.career',
                'experiences',
                'trainings',
                'professionalRegistrations',
                'knowledge',
                'jobProfile.careers',
                'applicant',
            ])
            ->get();

        foreach ($applications as $application) {
            // Verificar cancelación en cada iteración
            if ($this->batch() && $this->batch()->cancelled()) {
                return;
            }

            try {
                // Usar el método integrado con módulo Evaluation
                $evaluation = $autoGrader->applyAutoGradingWithEvaluationModule(
                    $application,
                    $this->userId
                );

                // Actualizar estadísticas en cache
                $this->updateStats(
                    $evaluation->isCompleted() && $application->fresh()->is_eligible
                        ? 'eligible'
                        : 'not_eligible'
                );

                Log::info('Postulación evaluada', [
                    'application_id' => $application->id,
                    'application_code' => $application->code,
                    'result' => $application->fresh()->is_eligible ? 'APTO' : 'NO_APTO',
                ]);

            } catch (\Exception $e) {
                $this->updateStats('errors');

                Log::error('Error evaluando postulación en lote', [
                    'application_id' => $application->id,
                    'application_code' => $application->code ?? 'N/A',
                    'error' => $e->getMessage(),
                ]);

                // Continuar con la siguiente postulación
                continue;
            }
        }
    }

    /**
     * Actualizar estadísticas en cache para seguimiento del progreso
     */
    private function updateStats(string $type): void
    {
        $cacheKey = "evaluation_progress:{$this->jobPostingId}";

        $stats = Cache::get($cacheKey, [
            'eligible' => 0,
            'not_eligible' => 0,
            'errors' => 0,
            'processed' => 0,
        ]);

        $stats[$type]++;
        $stats['processed']++;

        Cache::put($cacheKey, $stats, now()->addHours(1));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de evaluación falló completamente', [
            'batch_size' => count($this->applicationIds),
            'user_id' => $this->userId,
            'job_posting_id' => $this->jobPostingId,
            'error' => $exception->getMessage(),
        ]);
    }
}
