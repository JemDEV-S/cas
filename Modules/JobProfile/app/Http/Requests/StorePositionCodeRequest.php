<?php

namespace Modules\JobProfile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePositionCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización se maneja con Policies
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9\-]+$/',
                Rule::unique('position_codes', 'code')->whereNull('deleted_at'),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'base_salary' => 'required|numeric|min:0.01|max:99999999.99',
            'essalud_percentage' => 'nullable|numeric|min:0|max:100',
            'contract_months' => 'nullable|integer|min:1|max:24',
            'is_active' => 'nullable|boolean',
            'metadata' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'El código es obligatorio.',
            'code.unique' => 'El código ya existe.',
            'code.regex' => 'El código solo puede contener letras mayúsculas, números y guiones.',
            'name.required' => 'El nombre del cargo es obligatorio.',
            'base_salary.required' => 'El salario base es obligatorio.',
            'base_salary.min' => 'El salario base debe ser mayor a cero.',
            'essalud_percentage.min' => 'El porcentaje de EsSalud no puede ser negativo.',
            'essalud_percentage.max' => 'El porcentaje de EsSalud no puede ser mayor a 100.',
            'contract_months.min' => 'La duración del contrato debe ser al menos 1 mes.',
            'contract_months.max' => 'La duración del contrato no puede exceder 24 meses.',
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'código',
            'name' => 'nombre',
            'description' => 'descripción',
            'base_salary' => 'salario base',
            'essalud_percentage' => 'porcentaje EsSalud',
            'contract_months' => 'meses de contrato',
            'is_active' => 'activo',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convertir el código a mayúsculas automáticamente
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper($this->code),
            ]);
        }

        // Establecer valores por defecto
        if (!$this->has('essalud_percentage')) {
            $this->merge(['essalud_percentage' => 9.0]);
        }

        if (!$this->has('contract_months')) {
            $this->merge(['contract_months' => 3]);
        }

        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }
}
