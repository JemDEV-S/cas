<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        // Puede actualizar su propio perfil o si tiene permiso general
        if ($this->user()->id === $user->id) {
            return $this->user()->can('user.update.own');
        }

        return $this->user()->can('user.update.user');
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;
        $rules = [
            'dni' => ['sometimes', 'required', 'string', 'size:8', Rule::unique('users', 'dni')->ignore($userId), 'regex:/^[0-9]{8}$/'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['sometimes', 'required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ];

        // Solo añadir reglas de contraseña si se proporciona una
        if ($this->filled('password')) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
            $rules['current_password'] = ['required', 'string'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'dni' => 'DNI',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'first_name' => 'nombres',
            'last_name' => 'apellidos',
            'phone' => 'teléfono',
            'is_active' => 'activo',
            'roles' => 'roles',
        ];
    }

    public function messages(): array
    {
        return [
            'dni.regex' => 'El DNI debe contener exactamente 8 dígitos numéricos.',
            'dni.size' => 'El DNI debe tener exactamente 8 caracteres.',
            'current_password.required' => 'Debes ingresar tu contraseña actual para cambiarla.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ];
    }
}
