<?php

namespace Modules\Evaluation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Evaluation\Enums\EvaluationStatusEnum;

class StoreEvaluationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el controller con policies
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'application_id' => ['required', 'integer', 'exists:applications,id'],
            'evaluator_id' => ['required', 'integer', 'exists:users,id'],
            'phase_id' => ['required', 'integer', 'exists:process_phases,id'],
            'job_posting_id' => ['required', 'integer', 'exists:job_postings,id'],
            'deadline_at' => ['nullable', 'date', 'after:now'],
            'is_anonymous' => ['nullable', 'boolean'],
            'is_collaborative' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'application_id.required' => 'La postulación es requerida',
            'application_id.exists' => 'La postulación no existe',
            'evaluator_id.required' => 'El evaluador es requerido',
            'evaluator_id.exists' => 'El evaluador no existe',
            'phase_id.required' => 'La fase es requerida',
            'phase_id.exists' => 'La fase no existe',
            'job_posting_id.required' => 'La convocatoria es requerida',
            'job_posting_id.exists' => 'La convocatoria no existe',
            'deadline_at.date' => 'La fecha límite debe ser una fecha válida',
            'deadline_at.after' => 'La fecha límite debe ser posterior a hoy',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'application_id' => 'postulación',
            'evaluator_id' => 'evaluador',
            'phase_id' => 'fase',
            'job_posting_id' => 'convocatoria',
            'deadline_at' => 'fecha límite',
            'is_anonymous' => 'evaluación anónima',
            'is_collaborative' => 'evaluación colaborativa',
        ];
    }
}