<?php

namespace Modules\Organization\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganizationalUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('organization.update.unit');
    }

    public function rules(): array
    {
        $unitId = $this->route('organization');

        return [
            'code' => ['required', 'string', 'max:20', 'unique:organizational_units,code,' . $unitId],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'max:50'],
            'parent_id' => ['nullable', 'uuid', 'exists:organizational_units,id'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'código',
            'name' => 'nombre',
            'description' => 'descripción',
            'type' => 'tipo',
            'parent_id' => 'unidad padre',
            'order' => 'orden',
            'is_active' => 'activo',
        ];
    }
}
