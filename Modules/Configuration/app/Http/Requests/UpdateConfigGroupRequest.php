<?php

namespace Modules\Configuration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConfigGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('configuration.update');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $groupId = $this->route('configGroup') ?? $this->route('id');

        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('config_groups', 'code')->ignore($groupId),
                'regex:/^[A-Z_]+$/',
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'icon' => [
                'nullable',
                'string',
                'max:50',
            ],
            'order' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'code' => 'código',
            'name' => 'nombre',
            'description' => 'descripción',
            'icon' => 'icono',
            'order' => 'orden',
            'is_active' => 'activo',
        ];
    }

    /**
     * Get custom messages.
     */
    public function messages(): array
    {
        return [
            'code.regex' => 'El código debe contener solo letras mayúsculas y guiones bajos.',
        ];
    }
}
