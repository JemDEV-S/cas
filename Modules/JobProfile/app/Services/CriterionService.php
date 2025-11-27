<?php

namespace Modules\JobProfile\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Exceptions\BusinessRuleException;
use Modules\JobProfile\Entities\EvaluationCriterion;

class CriterionService
{
    /**
     * Obtiene todos los criterios de un código de posición
     */
    public function getByPositionCode(string $positionCodeId): Collection
    {
        return EvaluationCriterion::where('position_code_id', $positionCodeId)
            ->ordered()
            ->get();
    }

    /**
     * Obtiene criterios por fase de proceso
     */
    public function getByPhase(string $positionCodeId, string $phaseId): Collection
    {
        return EvaluationCriterion::where('position_code_id', $positionCodeId)
            ->where('process_phase_id', $phaseId)
            ->ordered()
            ->get();
    }

    /**
     * Crea un nuevo criterio de evaluación
     */
    public function create(array $data): EvaluationCriterion
    {
        return DB::transaction(function () use ($data) {
            // Validar que la suma de pesos no exceda 100
            $this->validateWeightSum(
                $data['position_code_id'],
                $data['process_phase_id'],
                $data['weight'],
                null
            );

            // Validar puntajes
            if ($data['min_score'] > $data['max_score']) {
                throw new BusinessRuleException('El puntaje mínimo no puede ser mayor que el máximo.');
            }

            // Si no se especifica orden, asignar el siguiente disponible
            if (!isset($data['order'])) {
                $data['order'] = $this->getNextOrder($data['position_code_id'], $data['process_phase_id']);
            }

            return EvaluationCriterion::create($data);
        });
    }

    /**
     * Actualiza un criterio de evaluación
     */
    public function update(string $id, array $data): EvaluationCriterion
    {
        return DB::transaction(function () use ($id, $data) {
            $criterion = EvaluationCriterion::findOrFail($id);

            // Validar suma de pesos si se está cambiando el peso
            if (isset($data['weight'])) {
                $this->validateWeightSum(
                    $criterion->position_code_id,
                    $criterion->process_phase_id,
                    $data['weight'],
                    $id
                );
            }

            // Validar puntajes
            $minScore = $data['min_score'] ?? $criterion->min_score;
            $maxScore = $data['max_score'] ?? $criterion->max_score;

            if ($minScore > $maxScore) {
                throw new BusinessRuleException('El puntaje mínimo no puede ser mayor que el máximo.');
            }

            $criterion->update($data);

            return $criterion->fresh();
        });
    }

    /**
     * Elimina un criterio
     */
    public function delete(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $criterion = EvaluationCriterion::findOrFail($id);
            return $criterion->delete();
        });
    }

    /**
     * Valida que la suma de pesos no exceda 100%
     */
    protected function validateWeightSum(
        string $positionCodeId,
        string $phaseId,
        float $newWeight,
        ?string $excludeId = null
    ): void {
        $query = EvaluationCriterion::where('position_code_id', $positionCodeId)
            ->where('process_phase_id', $phaseId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $currentSum = $query->sum('weight');
        $totalSum = $currentSum + $newWeight;

        if ($totalSum > 100) {
            throw new BusinessRuleException(
                "La suma de pesos excede 100%. Actual: {$currentSum}%, Nuevo: {$newWeight}%, Total: {$totalSum}%"
            );
        }
    }

    /**
     * Obtiene el siguiente número de orden disponible
     */
    protected function getNextOrder(string $positionCodeId, string $phaseId): int
    {
        $maxOrder = EvaluationCriterion::where('position_code_id', $positionCodeId)
            ->where('process_phase_id', $phaseId)
            ->max('order');

        return ($maxOrder ?? 0) + 1;
    }

    /**
     * Reordena criterios
     */
    public function reorder(array $criteriaOrder): bool
    {
        return DB::transaction(function () use ($criteriaOrder) {
            foreach ($criteriaOrder as $order => $criterionId) {
                EvaluationCriterion::where('id', $criterionId)
                    ->update(['order' => $order + 1]);
            }

            return true;
        });
    }

    /**
     * Valida que la suma de pesos sea exactamente 100%
     */
    public function validateTotalWeight(string $positionCodeId, string $phaseId): bool
    {
        $totalWeight = EvaluationCriterion::where('position_code_id', $positionCodeId)
            ->where('process_phase_id', $phaseId)
            ->sum('weight');

        $tolerance = 0.01; // Tolerancia de 0.01% por redondeo

        if (abs($totalWeight - 100) > $tolerance) {
            throw new BusinessRuleException(
                "La suma de pesos debe ser exactamente 100%. Actual: {$totalWeight}%"
            );
        }

        return true;
    }
}
