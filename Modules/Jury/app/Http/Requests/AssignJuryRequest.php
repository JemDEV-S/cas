<?php

namespace Modules\Jury\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Jury\Enums\JuryRole;

class AssignJuryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajustar con policies
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'string', 'exists:users,id'],
            'job_posting_id' => ['required', 'string', 'exists:job_postings,id'],
            'role_in_jury' => ['required', 'string', 'in:' . implode(',', JuryRole::values())],
            'dependency_scope_id' => ['nullable', 'string', 'exists:organizational_units,id'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'El usuario es requerido',
            'user_id.exists' => 'El usuario no existe',
            'job_posting_id.required' => 'La convocatoria es requerida',
            'job_posting_id.exists' => 'La convocatoria no existe',
            'role_in_jury.required' => 'El rol en el jurado es requerido',
            'role_in_jury.in' => 'El rol en el jurado no es válido',
            'dependency_scope_id.exists' => 'La dependencia no existe',
        ];
    }
}
