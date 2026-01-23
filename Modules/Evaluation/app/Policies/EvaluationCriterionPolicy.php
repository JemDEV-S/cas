<?php

namespace Modules\Evaluation\Policies;

use App\Models\User;
use Modules\Evaluation\Entities\EvaluationCriterion;
use Illuminate\Auth\Access\HandlesAuthorization;

class EvaluationCriterionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any criteria.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver criterios
        return true;
    }

    /**
     * Determine if the user can view the criterion.
     */
    public function view(User $user, EvaluationCriterion $criterion): bool
    {
        // Todos pueden ver criterios activos
        return $criterion->is_active || $user->hasAnyRole(['Administrador General', 'Administrador de RRHH']);
    }

    /**
     * Determine if the user can create criteria.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'Administrador General',
            'Administrador de RRHH'
        ]);
    }

    /**
     * Determine if the user can update the criterion.
     */
    public function update(User $user, EvaluationCriterion $criterion): bool
    {
        // Solo admin puede actualizar
        if (!$user->hasAnyRole(['Administrador General', 'Administrador de RRHH'])) {
            return false;
        }

        // No se pueden editar criterios del sistema
        return !$criterion->is_system;
    }

    /**
     * Determine if the user can delete the criterion.
     */
    public function delete(User $user, EvaluationCriterion $criterion): bool
    {
        // Solo admin puede eliminar
        if (!$user->hasAnyRole(['Administrador General', 'Administrador de RRHH'])) {
            return false;
        }

        // No se pueden eliminar criterios del sistema
        if ($criterion->is_system) {
            return false;
        }

        // No se puede eliminar si tiene evaluaciones asociadas
        return !$criterion->details()->exists();
    }

    /**
     * Determine if the user can toggle active status.
     */
    public function toggleActive(User $user, EvaluationCriterion $criterion): bool
    {
        // Solo admin puede cambiar estado
        if (!$user->hasAnyRole(['Administrador General', 'Administrador de RRHH'])) {
            return false;
        }

        // No se puede desactivar criterios del sistema
        return !$criterion->is_system;
    }

    /**
     * Determine if the user can reorder criteria.
     */
    public function reorder(User $user): bool
    {
        return $user->hasAnyRole([
            'Administrador General',
            'Administrador de RRHH'
        ]);
    }
}