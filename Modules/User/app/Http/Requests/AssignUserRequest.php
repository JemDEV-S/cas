<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request para asignar usuario a unidad organizacional
 */
class AssignUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('user.assign.organization');
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'uuid',
                'exists:users,id',
            ],
            'organization_unit_id' => [
                'required',
                'uuid',
                'exists:organizational_units,id',
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'end_date' => [
                'nullable',
                'date',
                'after:start_date',
            ],
            'is_primary' => [
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'El usuario es obligatorio',
            'user_id.exists' => 'El usuario seleccionado no existe',
            'organization_unit_id.required' => 'La unidad organizacional es obligatoria',
            'organization_unit_id.exists' => 'La unidad organizacional seleccionada no existe',
            'start_date.required' => 'La fecha de inicio es obligatoria',
            'start_date.after_or_equal' => 'La fecha de inicio debe ser hoy o posterior',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convertir string 'true'/'false' a boolean si viene como string
        if ($this->has('is_primary') && is_string($this->is_primary)) {
            $this->merge([
                'is_primary' => filter_var($this->is_primary, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}