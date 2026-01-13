<?php

namespace Modules\Evaluation\Policies;

use App\Models\User;
use Modules\JobPosting\Entities\JobPosting;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy para gestionar permisos de evaluaciones automáticas
 */
class AutomaticEvaluationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view the automatic evaluations interface.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('evaluations.view.automatic') ||
               $user->hasPermission('evaluations.execute.automatic') ||
               $user->hasPermission('evaluations.manage.all') ||
               $user->isAdmin();
    }

    /**
     * Determine if the user can execute automatic evaluations.
     */
    public function execute(User $user): bool
    {
        return $user->hasPermission('evaluations.execute.automatic') ||
               $user->hasPermission('evaluations.manage.all') ||
               $user->isAdmin();
    }

    /**
     * Determine if the user can re-execute automatic evaluations (force mode).
     */
    public function reexecute(User $user): bool
    {
        return $user->hasPermission('evaluations.reexecute.automatic') ||
               $user->hasPermission('evaluations.manage.all') ||
               $user->isAdmin();
    }

    /**
     * Determine if the user can view evaluation criteria.
     */
    public function viewCriteria(User $user): bool
    {
        return $user->hasPermission('evaluations.view.criteria') ||
               $user->hasPermission('evaluations.manage.criteria') ||
               $user->hasPermission('evaluations.manage.all') ||
               $user->isAdmin();
    }

    /**
     * Determine if the user can manage evaluation criteria.
     */
    public function manageCriteria(User $user): bool
    {
        return $user->hasPermission('evaluations.manage.criteria') ||
               $user->hasPermission('evaluations.manage.all') ||
               $user->isAdmin();
    }

    /**
     * Determine if the user can execute evaluations for a specific job posting.
     *
     * Aquí puedes agregar lógica adicional, por ejemplo:
     * - Verificar que el usuario sea parte del comité de la convocatoria
     * - Verificar que la convocatoria esté en la fase correcta
     */
    public function executeForJobPosting(User $user, JobPosting $jobPosting): bool
    {
        // Verificar permiso base
        if (!$this->execute($user)) {
            return false;
        }

        // Lógica adicional: verificar estado de la convocatoria
        // Por ejemplo, solo permitir si está publicada
        if (!$jobPosting->is_published) {
            return false;
        }

        // Puedes agregar más validaciones aquí, como:
        // - Verificar que la fase 3 (registro) haya terminado
        // - Verificar que el usuario sea coordinador de la convocatoria
        // etc.

        return true;
    }
}
