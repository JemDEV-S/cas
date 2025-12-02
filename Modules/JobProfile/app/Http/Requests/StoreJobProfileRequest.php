<?php

namespace Modules\JobProfile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\JobProfile\Enums\EducationLevelEnum;

class StoreJobProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización se maneja con Policies
    }

    public function rules(): array
    {
        return [
            // Información General
            'title' => 'nullable|string|max:255', // Se genera automáticamente
            'profile_name' => 'required|string|max:255',
            'organizational_unit_id' => 'required|uuid|exists:organizational_units,id',
            'position_code_id' => 'nullable|uuid|exists:position_codes,id',
            'work_regime' => ['required', 'string', Rule::in(['cas', '276', '728', '1057'])],
            'total_vacancies' => 'required|integer|min:1|max:100',
            'description' => 'nullable|string|max:2000',
            'mission' => 'nullable|string|max:2000',
            'justification' => 'required|string|min:1|max:2000',
            'working_conditions' => 'nullable|string|max:1000',
            'job_level' => 'nullable|string|max:100',
            'contract_type' => 'nullable|string|max:100',

            // Requisitos Académicos
            'education_level' => [
                'nullable',
                'string',
                Rule::in(EducationLevelEnum::values())
            ],
            'education_levels' => 'required|array|min:1',
            'education_levels.*' => [
                'required',
                'string',
                Rule::in(EducationLevelEnum::values())
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

            // Información del Contrato
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after_or_equal:contract_start_date',
            'work_location' => 'nullable|string|max:255',
            'selection_process_name' => 'nullable|string|max:255',

            // Metadata
            'metadata' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'profile_name.required' => 'El nombre del puesto es obligatorio.',
            'profile_name.max' => 'El nombre del puesto no puede exceder 255 caracteres.',

            'organizational_unit_id.required' => 'Debe seleccionar una unidad organizacional.',
            'organizational_unit_id.exists' => 'La unidad organizacional seleccionada no es válida.',

            'work_regime.required' => 'El régimen laboral es obligatorio.',
            'work_regime.in' => 'El régimen laboral seleccionado no es válido.',

            'total_vacancies.required' => 'Debe especificar el número de vacantes.',
            'total_vacancies.min' => 'Debe haber al menos 1 vacante.',
            'total_vacancies.max' => 'No puede exceder 100 vacantes.',

            'justification.required' => 'La justificación es obligatoria.',
            'justification.min' => 'La justificación debe tener al menos 10 caracteres.',
            'justification.max' => 'La justificación no puede exceder 2000 caracteres.',

            'education_level.in' => 'El nivel educativo seleccionado no es válido.',

            'education_levels.required' => 'Debe seleccionar al menos un nivel educativo.',
            'education_levels.min' => 'Debe seleccionar al menos un nivel educativo.',
            'education_levels.*.required' => 'Todos los niveles educativos son obligatorios.',
            'education_levels.*.in' => 'Uno o más niveles educativos seleccionados no son válidos.',

            'general_experience_years.min' => 'La experiencia general no puede ser negativa.',
            'general_experience_years.max' => 'La experiencia general no puede exceder 50 años.',

            'specific_experience_years.min' => 'La experiencia específica no puede ser negativa.',
            'specific_experience_years.max' => 'La experiencia específica no puede exceder 50 años.',

            'main_functions.required' => 'Debe especificar al menos una función principal.',
            'main_functions.min' => 'Debe especificar al menos una función principal.',
            'main_functions.*.required' => 'Todas las funciones deben tener descripción.',
            'main_functions.*.max' => 'Las funciones no pueden exceder 500 caracteres.',

            'salary_max.gte' => 'El salario máximo debe ser mayor o igual al salario mínimo.',

            'contract_end_date.after_or_equal' => 'La fecha de fin del contrato debe ser posterior a la fecha de inicio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título del puesto',
            'profile_name' => 'nombre del perfil',
            'organizational_unit_id' => 'unidad organizacional',
            'position_code_id' => 'código de posición',
            'work_regime' => 'régimen laboral',
            'total_vacancies' => 'total de vacantes',
            'description' => 'descripción',
            'mission' => 'misión',
            'justification' => 'justificación',
            'education_level' => 'nivel educativo',
            'education_levels' => 'niveles educativos',
            'career_field' => 'área de estudios',
            'title_required' => 'título requerido',
            'colegiatura_required' => 'colegiatura requerida',
            'general_experience_years' => 'experiencia general',
            'specific_experience_years' => 'experiencia específica',
            'specific_experience_description' => 'detalle de experiencia',
            'main_functions' => 'funciones principales',
            'salary_min' => 'salario mínimo',
            'salary_max' => 'salario máximo',
            'contract_start_date' => 'fecha de inicio del contrato',
            'contract_end_date' => 'fecha de fin del contrato',
            'work_location' => 'lugar de prestación del servicio',
            'selection_process_name' => 'nombre del proceso de selección',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convertir checkbox a boolean
        if ($this->has('colegiatura_required')) {
            $this->merge([
                'colegiatura_required' => $this->boolean('colegiatura_required'),
            ]);
        }

        // Limpiar arrays vacíos
        if ($this->has('main_functions')) {
            $functions = array_filter($this->main_functions, fn($value) => !empty(trim($value ?? '')));
            $this->merge(['main_functions' => array_values($functions)]);
        }

        if ($this->has('required_courses')) {
            $courses = array_filter($this->required_courses ?? [], fn($value) => !empty(trim($value ?? '')));
            $this->merge(['required_courses' => array_values($courses)]);
        }

        if ($this->has('knowledge_areas')) {
            $areas = array_filter($this->knowledge_areas ?? [], fn($value) => !empty(trim($value ?? '')));
            $this->merge(['knowledge_areas' => array_values($areas)]);
        }

        if ($this->has('required_competencies')) {
            $competencies = array_filter($this->required_competencies ?? [], fn($value) => !empty(trim($value ?? '')));
            $this->merge(['required_competencies' => array_values($competencies)]);
        }

        // Valores por defecto
        if (!$this->has('general_experience_years')) {
            $this->merge(['general_experience_years' => 0]);
        }

        if (!$this->has('specific_experience_years')) {
            $this->merge(['specific_experience_years' => 0]);
        }
    }
}
