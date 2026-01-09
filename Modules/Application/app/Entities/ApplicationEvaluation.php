<?php

namespace Modules\Application\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationEvaluation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'application_id',
        'is_eligible',
        'ineligibility_reasons',
        'academics_evaluation',
        'general_experience_evaluation',
        'specific_experience_evaluation',
        'professional_registry_evaluation',
        'osce_certification_evaluation',
        'driver_license_evaluation',
        'required_courses_evaluation',
        'technical_knowledge_evaluation',
        'algorithm_version',
        'evaluated_by',
        'evaluated_at',
    ];

    protected $casts = [
        'is_eligible' => 'boolean',
        'academics_evaluation' => 'array',
        'general_experience_evaluation' => 'array',
        'specific_experience_evaluation' => 'array',
        'professional_registry_evaluation' => 'array',
        'osce_certification_evaluation' => 'array',
        'driver_license_evaluation' => 'array',
        'required_courses_evaluation' => 'array',
        'technical_knowledge_evaluation' => 'array',
        'evaluated_at' => 'datetime',
    ];

    /**
     * Relación con la postulación
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Relación con el evaluador
     */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'evaluated_by');
    }

    /**
     * Obtener todas las razones de no elegibilidad
     */
    public function getIneligibilityReasonsArrayAttribute(): array
    {
        if (empty($this->ineligibility_reasons)) {
            return [];
        }

        return explode("\n", $this->ineligibility_reasons);
    }

    /**
     * Verificar si pasó un criterio específico
     */
    public function passedCriteria(string $criteria): bool
    {
        $evaluation = $this->{$criteria . '_evaluation'};
        return $evaluation['passed'] ?? false;
    }

    /**
     * Obtener resumen de la evaluación
     */
    public function getSummary(): array
    {
        return [
            'is_eligible' => $this->is_eligible,
            'total_criteria' => $this->getTotalCriteria(),
            'passed_criteria' => $this->getPassedCriteria(),
            'failed_criteria' => $this->getFailedCriteria(),
        ];
    }

    /**
     * Obtener total de criterios evaluados
     */
    private function getTotalCriteria(): int
    {
        $criteria = [
            'academics_evaluation',
            'general_experience_evaluation',
            'specific_experience_evaluation',
            'professional_registry_evaluation',
            'osce_certification_evaluation',
            'driver_license_evaluation',
            'required_courses_evaluation',
            'technical_knowledge_evaluation',
        ];

        $count = 0;
        foreach ($criteria as $criterion) {
            if (!empty($this->$criterion)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Obtener criterios aprobados
     */
    private function getPassedCriteria(): int
    {
        $criteria = [
            'academics_evaluation',
            'general_experience_evaluation',
            'specific_experience_evaluation',
            'professional_registry_evaluation',
            'osce_certification_evaluation',
            'driver_license_evaluation',
            'required_courses_evaluation',
            'technical_knowledge_evaluation',
        ];

        $count = 0;
        foreach ($criteria as $criterion) {
            if (!empty($this->$criterion) && ($this->$criterion['passed'] ?? false)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Obtener criterios no aprobados
     */
    private function getFailedCriteria(): array
    {
        $criteria = [
            'academics' => 'academics_evaluation',
            'general_experience' => 'general_experience_evaluation',
            'specific_experience' => 'specific_experience_evaluation',
            'professional_registry' => 'professional_registry_evaluation',
            'osce_certification' => 'osce_certification_evaluation',
            'driver_license' => 'driver_license_evaluation',
            'required_courses' => 'required_courses_evaluation',
            'technical_knowledge' => 'technical_knowledge_evaluation',
        ];

        $failed = [];
        foreach ($criteria as $name => $field) {
            if (!empty($this->$field) && !($this->$field['passed'] ?? false)) {
                $failed[] = [
                    'criteria' => $name,
                    'reason' => $this->$field['reason'] ?? 'No especificado',
                ];
            }
        }

        return $failed;
    }
}
