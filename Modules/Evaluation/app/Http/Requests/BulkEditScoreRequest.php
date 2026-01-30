<?php

namespace Modules\Evaluation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Evaluation\Entities\{Evaluation, EvaluationCriterion};

class BulkEditScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assign-evaluators');
    }

    public function rules(): array
    {
        return [
            'evaluation_id' => ['required', 'exists:evaluations,id'],
            'criterion_id' => ['required', 'exists:evaluation_criteria,id'],
            'score' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $evaluation = Evaluation::find($this->evaluation_id);
            $criterion = EvaluationCriterion::find($this->criterion_id);

            if ($evaluation && !in_array($evaluation->status->value, ['SUBMITTED', 'MODIFIED'])) {
                $validator->errors()->add('evaluation_id', 'Solo se pueden editar evaluaciones completadas.');
            }

            if ($criterion && !$criterion->validateScore($this->score)) {
                $validator->errors()->add('score', "El puntaje debe estar entre {$criterion->min_score} y {$criterion->max_score}");
            }
        });
    }

    public function messages(): array
    {
        return [
            'evaluation_id.required' => 'La evaluación es requerida',
            'evaluation_id.exists' => 'La evaluación no existe',
            'criterion_id.required' => 'El criterio es requerido',
            'criterion_id.exists' => 'El criterio no existe',
            'score.required' => 'El puntaje es requerido',
            'score.numeric' => 'El puntaje debe ser un número',
            'score.min' => 'El puntaje no puede ser negativo',
        ];
    }
}
