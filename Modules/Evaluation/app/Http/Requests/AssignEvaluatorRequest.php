<?php

namespace Modules\Evaluation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignEvaluatorRequest extends FormRequest
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
            'evaluator_id' => ['required', 'integer', 'exists:users,id'],
            'application_id' => ['required', 'integer', 'exists:applications,id'],
            'phase_id' => ['required', 'integer', 'exists:process_phases,id'],
            'job_posting_id' => ['required', 'integer', 'exists:job_postings,id'],
            'deadline_at' => ['nullable', 'date', 'after:now'],
            'workload_weight' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'evaluator_id.required' => 'El evaluador es requerido',
            'evaluator_id.exists' => 'El evaluador no existe',
            'application_id.required' => 'La postulación es requerida',
            'application_id.exists' => 'La postulación no existe',
            'phase_id.required' => 'La fase es requerida',
            'phase_id.exists' => 'La fase no existe',
            'job_posting_id.required' => 'La convocatoria es requerida',
            'job_posting_id.exists' => 'La convocatoria no existe',
            'deadline_at.date' => 'La fecha límite debe ser una fecha válida',
            'deadline_at.after' => 'La fecha límite debe ser posterior a hoy',
            'workload_weight.min' => 'El peso de carga debe ser al menos 1',
            'workload_weight.max' => 'El peso de carga no puede exceder 10',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'evaluator_id' => 'evaluador',
            'application_id' => 'postulación',
            'phase_id' => 'fase',
            'job_posting_id' => 'convocatoria',
            'deadline_at' => 'fecha límite',
            'workload_weight' => 'peso de carga',
        ];
    }
}