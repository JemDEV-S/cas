<?php

namespace Modules\JobProfile\Observers;

use Modules\JobProfile\Entities\JobProfile;
use Modules\JobProfile\Entities\JobProfileHistory;

class JobProfileObserver
{
    /**
     * Handle the JobProfile "created" event.
     */
    public function created(JobProfile $jobProfile): void
    {
        JobProfileHistory::log(
            jobProfileId: $jobProfile->id,
            action: 'created',
            description: 'Perfil de puesto creado',
            toStatus: $jobProfile->status
        );
    }

    /**
     * Handle the JobProfile "updated" event.
     */
    public function updated(JobProfile $jobProfile): void
    {
        $changes = $jobProfile->getChanges();
        $original = $jobProfile->getOriginal();

        // Detectar cambio de estado
        if (isset($changes['status'])) {
            $fromStatus = $original['status'] ?? null;
            $toStatus = $changes['status'];

            $action = $this->getActionFromStatusChange($toStatus);
            $description = $this->getDescriptionFromStatusChange($fromStatus, $toStatus);

            JobProfileHistory::log(
                jobProfileId: $jobProfile->id,
                action: $action,
                fromStatus: $fromStatus,
                toStatus: $toStatus,
                description: $description,
                changes: $this->formatChanges($changes, $original)
            );
        } else {
            // Cambios regulares (sin cambio de estado)
            JobProfileHistory::log(
                jobProfileId: $jobProfile->id,
                action: 'updated',
                description: 'Perfil de puesto actualizado',
                changes: $this->formatChanges($changes, $original)
            );
        }
    }

    /**
     * Handle the JobProfile "deleted" event.
     */
    public function deleted(JobProfile $jobProfile): void
    {
        JobProfileHistory::log(
            jobProfileId: $jobProfile->id,
            action: 'deleted',
            description: 'Perfil de puesto eliminado (soft delete)',
            fromStatus: $jobProfile->status
        );
    }

    /**
     * Handle the JobProfile "restored" event.
     */
    public function restored(JobProfile $jobProfile): void
    {
        JobProfileHistory::log(
            jobProfileId: $jobProfile->id,
            action: 'restored',
            description: 'Perfil de puesto restaurado',
            toStatus: $jobProfile->status
        );
    }

    /**
     * Handle the JobProfile "force deleted" event.
     */
    public function forceDeleted(JobProfile $jobProfile): void
    {
        JobProfileHistory::log(
            jobProfileId: $jobProfile->id,
            action: 'force_deleted',
            description: 'Perfil de puesto eliminado permanentemente',
            fromStatus: $jobProfile->status
        );
    }

    /**
     * Obtiene la acción basada en el nuevo estado
     */
    protected function getActionFromStatusChange(string $toStatus): string
    {
        return match($toStatus) {
            'draft' => 'draft_updated',
            'in_review' => 'submitted',
            'modification_requested' => 'modification_requested',
            'approved' => 'approved',
            'rejected' => 'rejected',
            'active' => 'activated',
            'inactive' => 'deactivated',
            default => 'status_changed',
        };
    }

    /**
     * Obtiene la descripción del cambio de estado
     */
    protected function getDescriptionFromStatusChange(?string $fromStatus, string $toStatus): string
    {
        return match($toStatus) {
            'draft' => 'Perfil devuelto a borrador',
            'in_review' => 'Perfil enviado a revisión',
            'modification_requested' => 'Solicitud de modificación al perfil',
            'approved' => 'Perfil aprobado',
            'rejected' => 'Perfil rechazado',
            'active' => 'Perfil activado',
            'inactive' => 'Perfil desactivado',
            default => "Estado cambiado de {$fromStatus} a {$toStatus}",
        };
    }

    /**
     * Formatea los cambios para el historial
     */
    protected function formatChanges(array $changes, array $original): array
    {
        $formatted = [];

        foreach ($changes as $field => $newValue) {
            // Excluir campos técnicos
            if (in_array($field, ['updated_at', 'deleted_at'])) {
                continue;
            }

            $oldValue = $original[$field] ?? null;

            $formatted[$field] = [
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        return $formatted;
    }
}
