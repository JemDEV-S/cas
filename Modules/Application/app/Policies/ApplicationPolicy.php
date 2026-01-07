<?php

namespace Modules\Application\Policies;

use modules\User\Entities\User;
use Modules\Application\Entities\Application;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationPolicy
{
    use HandlesAuthorization;

    /**
     * Determinar si el usuario puede ver cualquier postulación
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'application.view.all',
            'application.view.own'
        ]);
    }

    /**
     * Determinar si el usuario puede ver esta postulación
     */
    public function view(User $user, Application $application): bool
    {
        // Admin puede ver todas
        if ($user->hasPermissionTo('application.view.all')) {
            return true;
        }

        // El postulante puede ver su propia postulación
        if ($user->id === $application->applicant_id) {
            return true;
        }

        return false;
    }

    /**
     * Determinar si el usuario puede crear postulaciones
     */
    public function create(User $user): bool
    {
        // Verificar que tenga el rol de postulante
        if (!$user->hasRole('APPLICANT')) {
            return false;
        }

        // Verificar límite de postulaciones activas
        $activeApplicationsCount = $user->applications()
            ->whereNotIn('status', ['DESISTIDA', 'RECHAZADA'])
            ->count();

        $maxApplications = config('application.max_applications_per_user', 5);

        return $activeApplicationsCount < $maxApplications;
    }

    /**
     * Determinar si el usuario puede actualizar esta postulación
     */
    public function update(User $user, Application $application): bool
    {
        // Solo el postulante puede actualizar su propia postulación
        if ($user->id !== $application->applicant_id) {
            return false;
        }

        // Solo si está en estado editable
        return $application->isEditable();
    }

    /**
     * Determinar si el usuario puede eliminar esta postulación
     */
    public function delete(User $user, Application $application): bool
    {
        // Admin puede eliminar cualquiera
        if ($user->hasPermissionTo('application.delete.all')) {
            return true;
        }

        // El postulante puede eliminar la suya si está en estado PRESENTADA
        return $user->id === $application->applicant_id
            && $application->status === 'PRESENTADA';
    }

    /**
     * Determinar si el usuario puede desistir de esta postulación
     */
    public function withdraw(User $user, Application $application): bool
    {
        // Solo el postulante puede desistir de su propia postulación
        if ($user->id !== $application->applicant_id) {
            return false;
        }

        // Solo puede desistir en ciertos estados
        return in_array($application->status, [
            \Modules\Application\Enums\ApplicationStatus::SUBMITTED,
            \Modules\Application\Enums\ApplicationStatus::IN_REVIEW,
            \Modules\Application\Enums\ApplicationStatus::ELIGIBLE
        ]);
    }

    /**
     * Determinar si el usuario puede evaluar elegibilidad
     */
    public function evaluate(User $user, Application $application): bool
    {
        // Solo usuarios con permiso de evaluación
        return $user->hasPermissionTo('application.evaluate');
    }

    /**
     * Determinar si el usuario puede ver el historial
     */
    public function viewHistory(User $user, Application $application): bool
    {
        // Admin puede ver historial de cualquiera
        if ($user->hasPermissionTo('application.view.all')) {
            return true;
        }

        // El postulante puede ver su propio historial
        return $user->id === $application->applicant_id;
    }

    /**
     * Determinar si el usuario puede gestionar documentos
     */
    public function manageDocuments(User $user, Application $application): bool
    {
        // El postulante puede gestionar documentos de su postulación si está editable
        if ($user->id === $application->applicant_id && $application->isEditable()) {
            return true;
        }

        // Admin puede gestionar documentos de cualquiera
        return $user->hasPermissionTo('application.documents.manage');
    }

    /**
     * Determinar si el usuario puede verificar documentos
     */
    public function verifyDocuments(User $user, Application $application): bool
    {
        return $user->hasPermissionTo('application.documents.verify');
    }
}
