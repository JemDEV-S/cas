<?php

namespace Modules\Configuration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConfigGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('configuration.create');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:config_groups,code',
                'regex:/^[A-Z_]+$/',
            ],
            'name' => [
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
