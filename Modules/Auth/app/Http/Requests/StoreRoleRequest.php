<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'unique:roles,slug', 'regex:/^[a-z0-9\-]+$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id']
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'slug' => 'identificador',
            'description' => 'descripción',
            'is_active' => 'estado',
            'permissions' => 'permisos',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'El identificador solo puede contener letras minúsculas, números y guiones.',
            'slug.unique' => 'El identificador ya está en uso.',
        ];
    }
}
