<?php

namespace Modules\Evaluation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveEvaluationDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'criterion_id' => ['required', 'integer', 'exists:evaluation_criteria,id'],
            'score' => ['required', 'numeric', 'min:0'],
            'comments' => ['nullable', 'string', 'max:2000'],
            'evidence' => ['nullable', 'string', 'max:5000'],
            'change_reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->criterion_id) {
                $criterion = \Modules\Evaluation\Entities\EvaluationCriterion::find($this->criterion_id);
                
                if ($criterion) {
                    // Validar que el score esté dentro del rango del criterio
                    if ($this->score < $criterion->min_score || $this->score > $criterion->max_score) {
                        $validator->errors()->add(
                            'score',
                            "El puntaje debe estar entre {$criterion->min_score} y {$criterion->max_score}"
                        );
                    }

                    // Validar comentario requerido
                    if ($criterion->requires_comment && empty($this->comments)) {
                        $validator->errors()->add(
                            'comments',
                            "Este criterio requiere comentarios obligatorios"
                        );
                    }

                    // Validar evidencia requerida
                    if ($criterion->requires_evidence && empty($this->evidence)) {
                        $validator->errors()->add(
                            'evidence',
                            "Este criterio requiere evidencia obligatoria"
                        );
                    }
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'criterion_id.required' => 'El criterio es requerido',
            'criterion_id.exists' => 'El criterio no existe',
            'score.required' => 'El puntaje es requerido',
            'score.numeric' => 'El puntaje debe ser un número',
            'score.min' => 'El puntaje no puede ser negativo',
            'comments.max' => 'Los comentarios no pueden exceder 2000 caracteres',
            'evidence.max' => 'La evidencia no puede exceder 5000 caracteres',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'criterion_id' => 'criterio',
            'score' => 'puntaje',
            'comments' => 'comentarios',
            'evidence' => 'evidencia',
            'change_reason' => 'razón del cambio',
        ];
    }
}