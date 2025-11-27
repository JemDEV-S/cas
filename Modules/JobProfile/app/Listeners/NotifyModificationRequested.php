<?php

namespace Modules\JobProfile\Listeners;

use Modules\JobProfile\Events\ProfileModificationRequested;
use Illuminate\Support\Facades\Log;

class NotifyModificationRequested
{
    /**
     * Handle the event.
     */
    public function handle(ProfileModificationRequested $event): void
    {
        try {
            $jobProfile = $event->jobProfile;

            // TODO: Implementar notificación real cuando el módulo Notification esté disponible

            Log::info('Modificación solicitada - Notificación pendiente', [
                'job_profile_id' => $jobProfile->id,
                'job_profile_code' => $jobProfile->code,
                'reviewed_by' => $event->reviewedBy,
                'requested_by' => $jobProfile->requested_by,
                'comments' => $event->comments,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al notificar modificación solicitada', [
                'job_profile_id' => $event->jobProfile->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
