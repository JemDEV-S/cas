<?php

namespace Modules\JobProfile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización se maneja con Policies
    }

    public function rules(): array
    {
        return [
            // Información General
            'title' => 'required|string|max:255',
            'profile_name' => 'nullable|string|max:255',
            'organizational_unit_id' => 'required|uuid|exists:organizational_units,id',
            'position_code_id' => 'nullable|uuid|exists:position_codes,id',
            'work_regime' => ['required', 'string', Rule::in(['cas', '276', '728', '1057'])],
            'total_vacancies' => 'required|integer|min:1|max:100',
            'description' => 'nullable|string|max:2000',
            'mission' => 'nullable|string|max:2000',
            'justification' => 'required|string|min:10|max:2000',
            'working_conditions' => 'nullable|string|max:1000',
            'job_level' => 'nullable|string|max:100',
            'contract_type' => 'nullable|string|max:100',

            // Requisitos Académicos
            'education_level' => [
                'required',
                'string',
                Rule::in(['secondary', 'technical', 'bachelor', 'graduate', 'master', 'doctorate'])
            ],
            'career_field' => 'nullable|string|max:255',
            'title_required' => 'nullable|string|max:255',
            'colegiatura_required' => 'nullable|boolean',

            // Experiencia
            'general_experience_years' => 'nullable|numeric|min:0|max:50',
            'specific_experience_years' => 'nullable|numeric|min:0|max:50',
            'specific_experience_description' => 'nullable|string|max:1000',

            // Capacitación, conocimientos, competencias
            'required_courses' => 'nullable|array',
            'required_courses.*' => 'string|max:255',
            'knowledge_areas' => 'nullable|array',
            'knowledge_areas.*' => 'string|max:255',
            'required_competencies' => 'nullable|array',
            'required_competencies.*' => 'string|max:255',

            // Funciones del puesto
            'main_functions' => 'required|array|min:1',
            'main_functions.*' => 'required|string|max:500',

            // Salario
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',

            // Metadata
            'metadata' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título del puesto es obligatorio.',
            'organizational_unit_id.required' => 'Debe seleccionar una unidad organizacional.',
            'work_regime.required' => 'El régimen laboral es obligatorio.',
            'total_vacancies.required' => 'Debe especificar el número de vacantes.',
            'total_vacancies.min' => 'Debe haber al menos 1 vacante.',
            'justification.required' => 'La justificación es obligatoria.',
            'justification.min' => 'La justificación debe tener al menos 10 caracteres.',
            'education_level.required' => 'El nivel educativo es obligatorio.',
            'main_functions.required' => 'Debe especificar al menos una función principal.',
            'main_functions.min' => 'Debe especificar al menos una función principal.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título del puesto',
            'organizational_unit_id' => 'unidad organizacional',
            'work_regime' => 'régimen laboral',
            'total_vacancies' => 'total de vacantes',
            'justification' => 'justificación',
            'education_level' => 'nivel educativo',
            'main_functions' => 'funciones principales',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('colegiatura_required')) {
            $this->merge([
                'colegiatura_required' => $this->boolean('colegiatura_required'),
            ]);
        }

        if ($this->has('main_functions')) {
            $functions = array_filter($this->main_functions, fn($value) => !empty(trim($value ?? '')));
            $this->merge(['main_functions' => array_values($functions)]);
        }
    }
}
