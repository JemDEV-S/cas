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
        return $user->can('jobprofile.view.any');
    }

    /**
     * Determine if the user can view the job profile.
     */
    public function view(User $user, JobProfile $jobProfile): bool
    {
        // El usuario puede ver si tiene permiso general o si es el solicitante
        return $user->can('jobprofile.view')
            || $jobProfile->requested_by === $user->id;
    }

    /**
     * Determine if the user can create job profiles.
     */
    public function create(User $user): bool
    {
        return $user->can('jobprofile.create');
    }

    /**
     * Determine if the user can update the job profile.
     */
    public function update(User $user, JobProfile $jobProfile): bool
    {
        // Solo puede editar si tiene permiso y el perfil está en estado editable
        if (!$user->can('jobprofile.update')) {
            return false;
        }

        // Verificar que el perfil se puede editar
        if (!$jobProfile->canEdit()) {
            return false;
        }

        // El solicitante puede editar su propio perfil en draft o modification_requested
        if ($jobProfile->requested_by === $user->id) {
            return in_array($jobProfile->status, ['draft', 'modification_requested']);
        }

        return false;
    }

    /**
     * Determine if the user can delete the job profile.
     */
    public function delete(User $user, JobProfile $jobProfile): bool
    {
        // Solo el solicitante puede eliminar y solo en draft o rejected
        return $user->can('jobprofile.delete')
            && $jobProfile->requested_by === $user->id
            && in_array($jobProfile->status, ['draft', 'rejected']);
    }

    /**
     * Determine if the user can submit for review.
     */
    public function submitForReview(User $user, JobProfile $jobProfile): bool
    {
        return $user->can('jobprofile.submit')
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

        return $user->can('jobprofile.review')
            && $jobProfile->isInReview();
    }

    /**
     * Determine if the user can approve job profiles.
     */
    public function approve(User $user, JobProfile $jobProfile): bool
    {
        return $user->can('jobprofile.approve')
            && $jobProfile->requested_by !== $user->id
            && $jobProfile->canApprove();
    }

    /**
     * Determine if the user can reject job profiles.
     */
    public function reject(User $user, JobProfile $jobProfile): bool
    {
        return $user->can('jobprofile.reject')
            && $jobProfile->requested_by !== $user->id
            && $jobProfile->canReject();
    }

    /**
     * Determine if the user can request modifications.
     */
    public function requestModification(User $user, JobProfile $jobProfile): bool
    {
        return $user->can('jobprofile.review')
            && $jobProfile->requested_by !== $user->id
            && $jobProfile->canRequestModification();
    }
}
