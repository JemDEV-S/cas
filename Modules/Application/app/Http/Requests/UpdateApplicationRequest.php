<?php

namespace Modules\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Application\Entities\Application;

class UpdateApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $application = $this->route('application');
        return $this->user()->can('update', $application);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Datos personales (solo si está en estado editable)
            'personal_data' => ['sometimes', 'array'],
            'personal_data.full_name' => ['sometimes', 'string', 'max:255'],
            'personal_data.address' => ['sometimes', 'string', 'max:500'],
            'personal_data.mobile_phone' => ['sometimes', 'string', 'max:20'],
            'personal_data.phone' => ['nullable', 'string', 'max:20'],
            'personal_data.email' => ['sometimes', 'email', 'max:255'],

            // Formación académica
            'academics' => ['sometimes', 'array', 'min:1'],
            'academics.*.institution_name' => ['required_with:academics', 'string', 'max:255'],
            'academics.*.degree_type' => ['required_with:academics', 'string', Rule::in(['SECUNDARIA', 'TECNICO', 'BACHILLER', 'TITULO', 'MAESTRIA', 'DOCTORADO'])],
            'academics.*.degree_title' => ['required_with:academics', 'string', 'max:255'],
            'academics.*.career_field' => ['nullable', 'string', 'max:255'],
            'academics.*.issue_date' => ['required_with:academics', 'date', 'before_or_equal:today'],

            // Experiencia laboral
            'experiences' => ['sometimes', 'array'],
            'experiences.*.organization' => ['required_with:experiences', 'string', 'max:255'],
            'experiences.*.position' => ['required_with:experiences', 'string', 'max:255'],
            'experiences.*.start_date' => ['required_with:experiences', 'date', 'before_or_equal:today'],
            'experiences.*.end_date' => ['required_with:experiences', 'date', 'after_or_equal:experiences.*.start_date', 'before_or_equal:today'],
            'experiences.*.is_specific' => ['boolean'],
            'experiences.*.is_public_sector' => ['boolean'],

            // Capacitaciones
            'trainings' => ['sometimes', 'array'],
            'trainings.*.institution' => ['required_with:trainings', 'string', 'max:255'],
            'trainings.*.course_name' => ['required_with:trainings', 'string', 'max:255'],
            'trainings.*.academic_hours' => ['nullable', 'integer', 'min:1'],
            'trainings.*.start_date' => ['nullable', 'date', 'before_or_equal:today'],
            'trainings.*.end_date' => ['nullable', 'date', 'after_or_equal:trainings.*.start_date', 'before_or_equal:today'],

            // Condiciones especiales
            'special_conditions' => ['sometimes', 'array'],
            'special_conditions.*.condition_type' => ['required_with:special_conditions', 'string', Rule::in(['DISABILITY', 'MILITARY', 'ATHLETE_NATIONAL', 'ATHLETE_INTL', 'TERRORISM'])],
            'special_conditions.*.issuing_entity' => ['nullable', 'string', 'max:255'],
            'special_conditions.*.document_number' => ['nullable', 'string', 'max:100'],
            'special_conditions.*.issue_date' => ['nullable', 'date', 'before_or_equal:today'],
            'special_conditions.*.expiry_date' => ['nullable', 'date', 'after:today'],
            'special_conditions.*.bonus_percentage' => ['required_with:special_conditions', 'numeric', 'min:0', 'max:100'],

            // Registros profesionales
            'professional_registrations' => ['sometimes', 'array'],
            'professional_registrations.*.registration_type' => ['required_with:professional_registrations', 'string', Rule::in(['COLEGIATURA', 'OSCE_CERTIFICATION', 'DRIVER_LICENSE'])],
            'professional_registrations.*.issuing_entity' => ['nullable', 'string', 'max:255'],
            'professional_registrations.*.registration_number' => ['nullable', 'string', 'max:100'],
            'professional_registrations.*.issue_date' => ['nullable', 'date', 'before_or_equal:today'],
            'professional_registrations.*.expiry_date' => ['nullable', 'date', 'after:today'],

            // Conocimientos
            'knowledge' => ['sometimes', 'array'],
            'knowledge.*.knowledge_name' => ['required_with:knowledge', 'string', 'max:255'],
            'knowledge.*.proficiency_level' => ['nullable', 'string', Rule::in(['BASICO', 'INTERMEDIO', 'AVANZADO', 'EXPERTO'])],

            // Notas
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'academics.min' => 'Debe registrar al menos una formación académica',
            'experiences.*.end_date.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio',
        ];
    }
}
