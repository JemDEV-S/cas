<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request para validar DNI con código verificador
 * POST /api/auth/validate-dni
 */
class ValidateDniRequest extends FormRequest
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
        return [
            'dni' => [
                'required',
                'string',
                'size:8',
                'regex:/^[0-9]{8}$/',
            ],
            'codigo_verificador' => [
                'required',
                'string',
                'size:1',
            ],
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    public function messages(): array
    {
        return [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.string' => 'El DNI debe ser una cadena de texto.',
            'dni.size' => 'El DNI debe contener exactamente 8 dígitos.',
            'dni.regex' => 'El DNI debe contener solo números.',

            'codigo_verificador.required' => 'El código verificador es obligatorio.',
            'codigo_verificador.string' => 'El código verificador debe ser una cadena de texto.',
            'codigo_verificador.size' => 'El código verificador debe ser de 1 carácter.',
        ];
    }

    /**
     * Nombres de atributos personalizados para los mensajes
     */
    public function attributes(): array
    {
        return [
            'dni' => 'DNI',
            'codigo_verificador' => 'código verificador',
        ];
    }
}
