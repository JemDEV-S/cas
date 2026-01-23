<?php

namespace Modules\Evaluation\Policies;

use Modules\User\Entities\User;
use Modules\Evaluation\Entities\Evaluation;
use Illuminate\Auth\Access\HandlesAuthorization;

class EvaluationPolicy
{
    use HandlesAuthorization;

    /**
     * Determinar si el usuario puede ver la evaluación
     */
    public function view(User $user, Evaluation $evaluation): bool
    {
        // Puede ver si es el evaluador
        return $user->id === $evaluation->evaluator_id;
    }

    /**
     * Determinar si el usuario puede actualizar la evaluación
     */
    public function update(User $user, Evaluation $evaluation): bool
    {
        // Puede actualizar si es el evaluador y la evaluación se puede editar
        return $user->id === $evaluation->evaluator_id && $evaluation->canEdit();
    }

    /**
     * Determinar si el usuario puede enviar (submit) la evaluación
     */
    public function submit(User $user, Evaluation $evaluation): bool
    {
        // Puede enviar si es el evaluador y la evaluación se puede editar
        return $user->id === $evaluation->evaluator_id && $evaluation->canEdit();
    }

    /**
     * Determinar si el usuario puede modificar una evaluación ya enviada
     */
    public function modifySubmitted(User $user, Evaluation $evaluation): bool
    {
        // Por ahora solo el evaluador puede modificar
        return $user->id === $evaluation->evaluator_id;
    }

    /**
     * Determinar si el usuario puede eliminar la evaluación
     */
    public function delete(User $user, Evaluation $evaluation): bool
    {
        // Por ahora nadie puede eliminar evaluaciones directamente
        return false;
    }
}
