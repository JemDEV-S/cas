<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request para consultar DNI sin código verificador
 * GET /api/auth/consultar-dni/{dni}
 */
class ConsultDniRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para hacer esta petición
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Preparar los datos antes de la validación
     * Toma el parámetro de ruta y lo agrega a los datos a validar
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'dni' => $this->route('dni'),
        ]);
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
        ];
    }

    /**
     * Nombres de atributos personalizados para los mensajes
     */
    public function attributes(): array
    {
        return [
            'dni' => 'DNI',
        ];
    }

    /**
     * Obtener el DNI validado
     */
    public function getDni(): string
    {
        return $this->validated()['dni'];
    }
}
