<?php

namespace Modules\Evaluation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutoAssignEvaluatorsRequest extends FormRequest
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
            'job_posting_id' => ['required', 'integer', 'exists:job_postings,id'],
            'phase_id' => ['required', 'integer', 'exists:process_phases,id'],
            'evaluator_ids' => ['required', 'array', 'min:1'],
            'evaluator_ids.*' => ['required', 'integer', 'exists:users,id', 'distinct'],
            'application_ids' => ['required', 'array', 'min:1'],
            'application_ids.*' => ['required', 'integer', 'exists:applications,id', 'distinct'],
            'deadline_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'job_posting_id.required' => 'La convocatoria es requerida',
            'phase_id.required' => 'La fase es requerida',
            'evaluator_ids.required' => 'Debe seleccionar al menos un evaluador',
            'evaluator_ids.array' => 'Los evaluadores deben ser un arreglo',
            'evaluator_ids.min' => 'Debe seleccionar al menos un evaluador',
            'evaluator_ids.*.exists' => 'Uno o más evaluadores no existen',
            'evaluator_ids.*.distinct' => 'No puede haber evaluadores duplicados',
            'application_ids.required' => 'Debe seleccionar al menos una postulación',
            'application_ids.array' => 'Las postulaciones deben ser un arreglo',
            'application_ids.min' => 'Debe seleccionar al menos una postulación',
            'application_ids.*.exists' => 'Una o más postulaciones no existen',
            'application_ids.*.distinct' => 'No puede haber postulaciones duplicadas',
            'deadline_at.after' => 'La fecha límite debe ser posterior a hoy',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'job_posting_id' => 'convocatoria',
            'phase_id' => 'fase',
            'evaluator_ids' => 'evaluadores',
            'application_ids' => 'postulaciones',
            'deadline_at' => 'fecha límite',
        ];
    }
}