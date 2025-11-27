<?php

namespace Modules\JobProfile\Listeners;

use Modules\JobProfile\Events\JobProfileApproved;
use Illuminate\Support\Facades\Log;

class NotifyProfileApproved
{
    /**
     * Handle the event.
     */
    public function handle(JobProfileApproved $event): void
    {
        try {
            $jobProfile = $event->jobProfile;

            // TODO: Implementar notificación real cuando el módulo Notification esté disponible
            // Por ahora solo registramos en log

            Log::info('Perfil aprobado - Notificación pendiente', [
                'job_profile_id' => $jobProfile->id,
                'job_profile_code' => $jobProfile->code,
                'approved_by' => $event->approvedBy,
                'requested_by' => $jobProfile->requested_by,
            ]);

            // Ejemplo de cómo sería la notificación:
            // $user = User::find($jobProfile->requested_by);
            // $user->notify(new JobProfileApprovedNotification($jobProfile));

        } catch (\Exception $e) {
            Log::error('Error al notificar aprobación de perfil', [
                'job_profile_id' => $event->jobProfile->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
