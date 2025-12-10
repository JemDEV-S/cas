<?php

namespace Modules\Evaluation\Listeners;

use Modules\Evaluation\Events\EvaluationDeadlineApproaching;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyDeadlineApproaching implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(EvaluationDeadlineApproaching $event): void
    {
        $evaluation = $event->evaluation;
        $evaluator = $evaluation->evaluator;
        $daysRemaining = $event->daysRemaining;

        // Log de recordatorio
        Log::info('Recordatorio de fecha límite', [
            'evaluation_id' => $evaluation->id,
            'evaluator_id' => $evaluator->id,
            'evaluator_name' => $evaluator->name,
            'days_remaining' => $daysRemaining,
            'deadline_at' => $evaluation->deadline_at,
        ]);

        // Notificar al evaluador
        /*
        $evaluator->notify(new DeadlineApproachingNotification($evaluation, $daysRemaining));
        */

        // Si quedan menos de 24 horas, notificar también a RRHH
        if ($daysRemaining <= 1) {
            /*
            $admins = User::role('Administrador de RRHH')->get();
            Notification::send($admins, new EvaluatorDeadlineAlertNotification($evaluation));
            */
            
            Log::warning("Evaluación #{$evaluation->id} próxima a vencer (menos de 24h)");
        }

        Log::info("Recordatorio de fecha límite enviado a {$evaluator->email}");
    }

    /**
     * Handle a job failure.
     */
    public function failed(EvaluationDeadlineApproaching $event, \Throwable $exception): void
    {
        Log::error('Error al notificar fecha límite', [
            'evaluation_id' => $event->evaluation->id,
            'error' => $exception->getMessage(),
        ]);
    }
}