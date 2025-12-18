<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request para registro de usuario con validación de RENIEC
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para hacer esta petición
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        $rules = [
            'dni' => [
                'required',
                'string',
                'size:8',
                'unique:users,dni',
                'regex:/^[0-9]{8}$/',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],
            'last_name' => [
                'required',
                'string',
                'max:100',
            ],
            'gender' => [
                'required',
                'string',
                'in:MASCULINO,FEMENINO',
            ],
            'birth_date' => [
                'required',
                'date',
                'before:today',
                'after:1900-01-01',
            ],
            'address' => [
                'required',
                'string',
                'max:255',
            ],
            'district' => [
                'required',
                'string',
                'max:100',
            ],
            'province' => [
                'required',
                'string',
                'max:100',
            ],
            'department' => [
                'required',
                'string',
                'max:100',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{9}$/',
            ],
        ];

        // Si RENIEC está habilitado y requiere código verificador
        if ($this->shouldValidateWithReniec()) {
            $rules['codigo_verificador'] = [
                'required',
                'string',
                'size:1',
            ];
        }

        return $rules;
    }

    /**
     * Mensajes de error personalizados
     */
    public function messages(): array
    {
        return [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.size' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.unique' => 'Este DNI ya está registrado en el sistema.',
            'dni.regex' => 'El DNI debe contener solo números.',

            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',

            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'Los apellidos son obligatorios.',

            'gender.required' => 'El género es obligatorio.',
            'gender.in' => 'El género debe ser MASCULINO o FEMENINO.',

            'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
            'birth_date.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'birth_date.after' => 'La fecha de nacimiento debe ser posterior a 1900.',

            'address.required' => 'La dirección es obligatoria.',
            'district.required' => 'El distrito es obligatorio.',
            'province.required' => 'La provincia es obligatoria.',
            'department.required' => 'El departamento es obligatorio.',

            'phone.required' => 'El teléfono es obligatorio.',
            'phone.regex' => 'El teléfono debe tener exactamente 9 dígitos.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',

            'codigo_verificador.required' => 'El código verificador del DNI es obligatorio.',
            'codigo_verificador.size' => 'El código verificador debe ser de 1 carácter.',
        ];
    }

    /**
     * Nombres de atributos personalizados
     */
    public function attributes(): array
    {
        return [
            'dni' => 'DNI',
            'email' => 'correo electrónico',
            'first_name' => 'nombre',
            'last_name' => 'apellidos',
            'gender' => 'género',
            'birth_date' => 'fecha de nacimiento',
            'address' => 'dirección',
            'district' => 'distrito',
            'province' => 'provincia',
            'department' => 'departamento',
            'password' => 'contraseña',
            'phone' => 'teléfono',
            'codigo_verificador' => 'código verificador',
        ];
    }

    /**
     * Determinar si debe validar con RENIEC
     */
    public function shouldValidateWithReniec(): bool
    {
        return config('auth.reniec.enabled', false) &&
               config('auth.reniec.validation.check_digit.enabled', true);
    }

    /**
     * Obtener el DNI
     */
    public function getDni(): string
    {
        return $this->validated()['dni'];
    }

    /**
     * Obtener el código verificador (si está presente)
     */
    public function getCodigoVerificador(): ?string
    {
        return $this->validated()['codigo_verificador'] ?? null;
    }
}
