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
        return $user->hasPermission('jobprofile.create.profile');
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
        // Solo el solicitante puede eliminar y solo en draft o rejected
        return $user->hasPermission('jobprofile.delete.profile')
            && $jobProfile->requested_by === $user->id
            && in_array($jobProfile->status, ['draft', 'rejected']);
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
}
