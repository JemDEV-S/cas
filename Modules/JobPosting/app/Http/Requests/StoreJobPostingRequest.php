<?php

namespace Modules\JobPosting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobPostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'year' => 'required|integer|min:2000|max:' . (now()->year + 1),
            'description' => 'nullable|string|max:5000',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'metadata' => 'nullable|array',

            // Reglas para el cronograma automático
            'auto_schedule' => 'nullable|boolean',
            'schedule_start_date' => 'nullable|date|required_if:auto_schedule,true',
        ];
    }

    protected function prepareForValidation()
    {
        // Si el checkbox viene como 'on' o presente, lo pasamos a true
        // Verifica el 'name' de tu input HTML, en tu screenshot parece llamarse 'auto_schedule'
        // pero en el controlador anterior usabas 'create_schedule'.
        // He unificado a 'auto_schedule' para coincidir con el servicio.

        $this->merge([
            'auto_schedule' => $this->has('auto_schedule') || $this->has('create_schedule'),
        ]);
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título de la convocatoria es obligatorio.',
            'title.max' => 'El título no puede exceder 255 caracteres.',
            'year.integer' => 'El año debe ser un número válido.',
            'year.min' => 'El año debe ser 2000 o posterior.',
            'year.max' => 'El año no puede ser mayor a ' . (now()->year + 1) . '.',
            'start_date.after_or_equal' => 'La fecha de inicio no puede ser anterior a hoy.',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'year' => 'año',
            'schedule_start_date' => 'fecha de inicio del cronograma',
        ];
    }
}
