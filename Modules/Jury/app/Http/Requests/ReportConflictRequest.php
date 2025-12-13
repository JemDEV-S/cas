<?php

namespace Modules\Jury\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Jury\Enums\{ConflictType, ConflictSeverity};

class ReportConflictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajustar con policies
    }

    public function rules(): array
    {
        return [
            'jury_member_id' => ['required', 'string', 'exists:jury_members,id'],
            'application_id' => ['nullable', 'string', 'exists:applications,id'],
            'job_posting_id' => ['nullable', 'string', 'exists:job_postings,id'],
            'applicant_id' => ['nullable', 'string', 'exists:users,id'],
            'conflict_type' => ['required', 'string', 'in:' . implode(',', ConflictType::values())],
            'severity' => ['nullable', 'string', 'in:' . implode(',', ConflictSeverity::values())],
            'description' => ['required', 'string', 'min:10'],
            'evidence_path' => ['nullable', 'string'],
            'additional_details' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'jury_member_id.required' => 'El jurado es requerido',
            'conflict_type.required' => 'El tipo de conflicto es requerido',
            'description.required' => 'La descripción es requerida',
            'description.min' => 'La descripción debe tener al menos 10 caracteres',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Asegurar que al menos uno de los contextos esté presente
        if (!$this->application_id && !$this->job_posting_id && !$this->applicant_id) {
            $this->merge([
                '_validation_error' => 'Debe proporcionar al menos un contexto: application_id, job_posting_id o applicant_id'
            ]);
        }
    }
}
