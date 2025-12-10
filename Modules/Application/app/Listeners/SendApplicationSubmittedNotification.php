<?php

namespace Modules\Application\Listeners;

use Modules\Application\Events\ApplicationSubmitted;
use Illuminate\Support\Facades\Log;

class SendApplicationSubmittedNotification
{
    /**
     * Handle the event.
     */
    public function handle(ApplicationSubmitted $event): void
    {
        $application = $event->application;

        // TODO: Implementar envío de notificación por email
        // Ejemplo:
        // Notification::send($application->applicant, new ApplicationSubmittedNotification($application));

        Log::info('Postulación creada', [
            'application_id' => $application->id,
            'code' => $application->code,
            'applicant_id' => $application->applicant_id,
        ]);
    }
}
