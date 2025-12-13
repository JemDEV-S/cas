<?php

namespace Modules\Jury\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Jury\Enums\{MemberType, JuryRole};

class AssignJuryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajustar con policies
    }

    public function rules(): array
    {
        return [
            'jury_member_id' => ['required', 'string', 'exists:jury_members,id'],
            'job_posting_id' => ['required', 'string', 'exists:job_postings,id'],
            'member_type' => ['required', 'string', 'in:' . implode(',', MemberType::values())],
            'role_in_jury' => ['nullable', 'string', 'in:' . implode(',', JuryRole::values())],
            'order' => ['nullable', 'integer', 'min:0'],
            'assignment_resolution' => ['nullable', 'string', 'max:255'],
            'resolution_date' => ['nullable', 'date'],
            'max_evaluations' => ['nullable', 'integer', 'min:1'],
            'available_from' => ['nullable', 'date'],
            'available_until' => ['nullable', 'date', 'after:available_from'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'jury_member_id.required' => 'El jurado es requerido',
            'jury_member_id.exists' => 'El jurado no existe',
            'job_posting_id.required' => 'La convocatoria es requerida',
            'job_posting_id.exists' => 'La convocatoria no existe',
            'member_type.required' => 'El tipo de miembro es requerido',
            'member_type.in' => 'El tipo de miembro no es válido',
        ];
    }
}
