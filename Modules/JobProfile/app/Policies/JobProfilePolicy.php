<?php

namespace Modules\JobProfile\Policies;

use Modules\User\Entities\User;
use Modules\JobProfile\Entities\JobProfile;

class JobProfilePolicy
{
    /**
     * Determine if the user can view any job profiles.
     */
    public function viewAny(User $user): bool
    {
        // Puede ver listado si tiene cualquiera de los dos permisos
        return $user->hasPermission('jobprofile.view.profiles')
            || $user->hasPermission('jobprofile.view.own');
    }

    /**
     * Determine if the user can view the job profile.
     */
    public function view(User $user, JobProfile $jobProfile): bool
    {
        // El usuario puede ver si tiene permiso general o si es el solicitante
        return $user->hasPermission('jobprofile.view.profile')
            || ($user->hasPermission('jobprofile.view.own') && $jobProfile->requested_by === $user->id);
    }

    /**
     * Determine if the user can create job profiles.
     */
    public function create(User $user): bool
    {
        // Verificar permisos básicos
        if (!$user->hasPermission('jobprofile.create.profile')) {
            return false;
        }

        // Verificar rango de fechas configurado
        return $this->isWithinCreationDateRange();
    }

    /**
     * Verificar si la fecha actual está dentro del rango permitido
     */
    protected function isWithinCreationDateRange(): bool
    {
        $configService = app(\Modules\Configuration\Services\ConfigService::class);

        $startDate = $configService->get('JOB_PROFILE_CREATION_START_DATE');
        $endDate = $configService->get('JOB_PROFILE_CREATION_END_DATE');
        $now = now();

        // Si no hay fechas configuradas, permitir la creación
        if (!$startDate && !$endDate) {
            return true;
        }

        // Convertir las fechas a Carbon si son strings
        $startDate = $startDate ? \Carbon\Carbon::parse($startDate)->startOfDay() : null;
        $endDate = $endDate ? \Carbon\Carbon::parse($endDate)->endOfDay() : null;

        // Verificar fecha de inicio
        if ($startDate && $now->lt($startDate)) {
            return false;
        }

        // Verificar fecha de fin
        if ($endDate && $now->gt($endDate)) {
            return false;
        }

        return true;
    }

    /**
     * Obtener mensaje de error cuando está fuera del rango
     */
    public function getCreationDateRangeMessage(): ?string
    {
        $configService = app(\Modules\Configuration\Services\ConfigService::class);

        $startDate = $configService->get('JOB_PROFILE_CREATION_START_DATE');
        $endDate = $configService->get('JOB_PROFILE_CREATION_END_DATE');

        if (!$startDate && !$endDate) {
            return null;
        }

        $startDate = $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : null;
        $endDate = $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : null;

        if ($startDate && $endDate) {
            return "La creación de perfiles solo está permitida entre el {$startDate} y el {$endDate}.";
        } elseif ($startDate) {
            return "La creación de perfiles solo está permitida a partir del {$startDate}.";
        } elseif ($endDate) {
            return "La creación de perfiles solo está permitida hasta el {$endDate}.";
        }

        return null;
    }

    /**
     * Determine if the user can update the job profile.
     */
    public function update(User $user, JobProfile $jobProfile): bool
    {
        // Admin RRHH puede editar cualquier perfil
        if ($user->hasPermission('jobprofile.update.any')) {
            return $jobProfile->canEdit();
        }

        // El solicitante puede editar su propio perfil solo en draft o modification_requested
        if ($user->hasPermission('jobprofile.update.own') && $jobProfile->requested_by === $user->id) {
            return in_array($jobProfile->status, ['draft', 'modification_requested'])
                && $jobProfile->canEdit();
        }

        return false;
    }

    /**
     * Determine if the user can delete the job profile.
     */
    public function delete(User $user, JobProfile $jobProfile): bool
    {
        // Admin RRHH puede eliminar cualquier perfil editable
        if ($user->hasPermission('jobprofile.delete.any')) {
            return $jobProfile->canEdit() || $jobProfile->isRejected();
        }

        // El solicitante puede eliminar su propio perfil si está editable o rechazado
        return $user->hasPermission('jobprofile.delete.profile')
            && $jobProfile->requested_by === $user->id
            && ($jobProfile->canEdit() || $jobProfile->isRejected());
    }

    /**
     * Determine if the user can submit for review.
     */
    public function submitForReview(User $user, JobProfile $jobProfile): bool
    {
        return $user->hasPermission('jobprofile.submit.profile')
            && $jobProfile->requested_by === $user->id
            && $jobProfile->canSubmitForReview();
    }

    /**
     * Determine if the user can review job profiles.
     */
    public function review(User $user, JobProfile $jobProfile): bool
    {
        // No puede revisar su propio perfil
        if ($jobProfile->requested_by === $user->id) {
            return false;
        }

        return $user->hasPermission('jobprofile.review.profile')
            && $jobProfile->isInReview();
    }

    /**
     * Determine if the user can approve job profiles.
     */
    public function approve(User $user, JobProfile $jobProfile): bool
    {
        return $user->hasPermission('jobprofile.approve.profile')
            && $jobProfile->requested_by !== $user->id
            && $jobProfile->canApprove();
    }

    /**
     * Determine if the user can reject job profiles.
     */
    public function reject(User $user, JobProfile $jobProfile): bool
    {
        return $user->hasPermission('jobprofile.reject.profile')
            && $jobProfile->requested_by !== $user->id
            && $jobProfile->canReject();
    }

    /**
     * Determine if the user can request modifications.
     */
    public function requestModification(User $user, JobProfile $jobProfile): bool
    {
        return $user->hasPermission('jobprofile.request.modification')
            && $jobProfile->requested_by !== $user->id
            && $jobProfile->canRequestModification();
    }

    /**
     * Determine if the user can update during review.
     * Permite al revisor editar el perfil directamente durante la revisión.
     */
    public function updateDuringReview(User $user, JobProfile $jobProfile): bool
    {
        // No puede editar su propio perfil
        if ($jobProfile->requested_by === $user->id) {
            return false;
        }

        // Debe tener permiso de revisión y el perfil debe estar en revisión
        return $user->hasPermission('jobprofile.review.profile')
            && $jobProfile->isInReview();
    }
}
