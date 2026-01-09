<?php

namespace Modules\Results\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Results\Entities\ResultPublication;
use Modules\Application\Entities\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendResultNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600; // 10 minutos

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ResultPublication $publication
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Iniciando envío de notificaciones de resultados', [
            'publication_id' => $this->publication->id,
            'phase' => $this->publication->phase->value,
        ]);

        try {
            // Obtener todas las postulaciones de la convocatoria
            $applications = Application::whereHas('vacancy.jobProfile.jobPosting',
                    fn($q) => $q->where('id', $this->publication->job_posting_id)
                )
                ->with(['applicant'])
                ->get();

            $notified = 0;
            $failed = 0;

            foreach ($applications as $application) {
                try {
                    if ($application->applicant && $application->applicant->email) {
                        // Aquí puedes usar tu sistema de notificaciones personalizado
                        // Ejemplo simple con Mail:
                        \Mail::to($application->applicant->email)
                            ->send(new \Modules\Results\Emails\ResultPublishedEmail(
                                $application,
                                $this->publication
                            ));

                        $notified++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    Log::error('Error enviando notificación individual', [
                        'application_id' => $application->id,
                        'applicant_email' => $application->applicant?->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Notificaciones de resultados completadas', [
                'publication_id' => $this->publication->id,
                'total_applications' => $applications->count(),
                'notified' => $notified,
                'failed' => $failed,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en envío masivo de notificaciones', [
                'publication_id' => $this->publication->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de notificaciones falló definitivamente', [
            'publication_id' => $this->publication->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
