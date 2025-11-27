<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkAssignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('user.assign.organization');
    }

    public function rules(): array
    {
        return [
            'user_ids' => [
                'required',
                'array',
                'min:1',
                'max:100', // Límite de 100 usuarios por operación
            ],
            'user_ids.*' => [
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
        ];
    }

    public function messages(): array
    {
        return [
            'user_ids.required' => 'Debe seleccionar al menos un usuario',
            'user_ids.array' => 'Los usuarios deben ser un array',
            'user_ids.min' => 'Debe seleccionar al menos un usuario',
            'user_ids.max' => 'No puede asignar más de 100 usuarios a la vez',
            'user_ids.*.exists' => 'Uno o más usuarios seleccionados no existen',
            'organization_unit_id.required' => 'La unidad organizacional es obligatoria',
            'organization_unit_id.exists' => 'La unidad organizacional seleccionada no existe',
            'start_date.required' => 'La fecha de inicio es obligatoria',
            'start_date.after_or_equal' => 'La fecha de inicio debe ser hoy o posterior',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
        ];
    }
}