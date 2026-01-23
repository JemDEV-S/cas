<?php

namespace Modules\Evaluation\Policies;

use App\Models\User;
use Modules\Evaluation\Entities\EvaluatorAssignment;
use Illuminate\Auth\Access\HandlesAuthorization;

class EvaluatorAssignmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any assignments.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'Administrador General',
            'Administrador de RRHH',
            'Jurado/Evaluador'
        ]);
    }

    /**
     * Determine if the user can view the assignment.
     */
    public function view(User $user, EvaluatorAssignment $assignment): bool
    {
        // Admin puede ver todas
        if ($user->hasAnyRole(['Administrador General', 'Administrador de RRHH'])) {
            return true;
        }

        // Evaluador solo puede ver las propias
        return $assignment->evaluator_id === $user->id;
    }

    /**
     * Determine if the user can create assignments.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'Administrador General',
            'Administrador de RRHH'
        ]);
    }

    /**
     * Determine if the user can assign evaluators.
     */
    public function assign(User $user): bool
    {
        return $user->hasAnyRole([
            'Administrador General',
            'Administrador de RRHH'
        ]);
    }

    /**
     * Determine if the user can auto-assign evaluators.
     */
    public function autoAssign(User $user): bool
    {
        return $user->hasAnyRole([
            'Administrador General',
            'Administrador de RRHH'
        ]);
    }

    /**
     * Determine if the user can reassign an assignment.
     */
    public function reassign(User $user, EvaluatorAssignment $assignment): bool
    {
        // Solo admin puede reasignar
        if (!$user->hasAnyRole(['Administrador General', 'Administrador de RRHH'])) {
            return false;
        }

        // Solo si está activa
        return $assignment->isActive();
    }

    /**
     * Determine if the user can cancel an assignment.
     */
    public function cancel(User $user, EvaluatorAssignment $assignment): bool
    {
        // Solo admin puede cancelar
        if (!$user->hasAnyRole(['Administrador General', 'Administrador de RRHH'])) {
            return false;
        }

        // Solo si está activa
        return $assignment->isActive();
    }

    /**
     * Determine if the user can view workload statistics.
     */
    public function viewWorkload(User $user): bool
    {
        return $user->hasAnyRole([
            'Administrador General',
            'Administrador de RRHH'
        ]);
    }

    /**
     * Determine if the user can view assignment statistics.
     */
    public function viewStats(User $user): bool
    {
        return $user->hasAnyRole([
            'Administrador General',
            'Administrador de RRHH'
        ]);
    }
}