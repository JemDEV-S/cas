<?php

namespace Modules\Evaluation\Listeners;

use Modules\Evaluation\Events\EvaluationModified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogEvaluationModified implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(EvaluationModified $event): void
    {
        $evaluation = $event->evaluation;

        // Log crítico de modificación (auditoría)
        Log::warning('Evaluación modificada después de envío', [
            'evaluation_id' => $evaluation->id,
            'modified_by' => $event->modifiedBy,
            'reason' => $event->reason,
            'original_evaluator_id' => $evaluation->evaluator_id,
            'application_id' => $evaluation->application_id,
            'old_total_score' => $evaluation->getOriginal('total_score'),
            'new_total_score' => $evaluation->total_score,
            'modified_at' => $evaluation->modified_at,
        ]);

        // Notificar al evaluador original de la modificación
        /*
        $originalEvaluator = $evaluation->evaluator;
        $originalEvaluator->notify(new EvaluationModifiedNotification($evaluation, $event->reason));
        */

        // Notificar a supervisores/auditores
        /*
        $supervisors = User::role('Administrador General')->get();
        Notification::send($supervisors, new EvaluationModifiedAuditNotification($evaluation));
        */

        Log::info("Auditoría de modificación registrada para evaluación #{$evaluation->id}");
    }

    /**
     * Handle a job failure.
     */
    public function failed(EvaluationModified $event, \Throwable $exception): void
    {
        Log::error('Error al registrar modificación de evaluación', [
            'evaluation_id' => $event->evaluation->id,
            'error' => $exception->getMessage(),
        ]);
    }
}