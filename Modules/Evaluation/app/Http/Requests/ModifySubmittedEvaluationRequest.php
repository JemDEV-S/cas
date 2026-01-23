<?php

namespace Modules\Evaluation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModifySubmittedEvaluationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo administradores pueden modificar evaluaciones enviadas
        return auth()->user()->hasAnyRole(['Administrador General', 'Administrador de RRHH']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'modification_reason' => ['required', 'string', 'min:20', 'max:1000'],
            'general_comments' => ['nullable', 'string', 'max:5000'],
            'details' => ['nullable', 'array'],
            'details.*.criterion_id' => ['required', 'integer', 'exists:evaluation_criteria,id'],
            'details.*.score' => ['required', 'numeric', 'min:0'],
            'details.*.comments' => ['nullable', 'string', 'max:2000'],
            'details.*.evidence' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'modification_reason.required' => 'La razón de modificación es obligatoria',
            'modification_reason.min' => 'La razón debe tener al menos 20 caracteres',
            'modification_reason.max' => 'La razón no puede exceder 1000 caracteres',
            'details.*.criterion_id.required' => 'El criterio es requerido',
            'details.*.criterion_id.exists' => 'El criterio no existe',
            'details.*.score.required' => 'El puntaje es requerido',
            'details.*.score.numeric' => 'El puntaje debe ser un número',
            'details.*.score.min' => 'El puntaje no puede ser negativo',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'modification_reason' => 'razón de modificación',
            'general_comments' => 'comentarios generales',
            'details' => 'detalles',
        ];
    }
}