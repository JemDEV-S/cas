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
        // Obtenemos el modelo del usuario que estamos intentando editar desde la ruta
        $targetUser = $this->route('user');

        $rules = [
            // Campos requeridos (sin 'sometimes' para asegurar que siempre se envíen)
            'dni' => ['required', 'string', 'size:8', Rule::unique('users', 'dni')->ignore($targetUser->id), 'regex:/^[0-9]{8}$/'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($targetUser->id)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ];

        // Solo añadir reglas de contraseña si se proporciona una nueva contraseña
        if ($this->filled('password')) {
            // Reglas estándar para la NUEVA contraseña
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];

            // Solo si el usuario conectado está actualizando su PROPIO perfil,
            // le exigimos la contraseña actual por seguridad.
            if ($this->user()->id === $targetUser->id) {
                $rules['current_password'] = ['required', 'string', 'current_password'];
            }
            // Si un admin está editando a otro usuario, no se requiere current_password
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'dni' => 'DNI',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'current_password' => 'contraseña actual',
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
            'current_password.current_password' => 'La contraseña actual es incorrecta.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ];
    }
}
