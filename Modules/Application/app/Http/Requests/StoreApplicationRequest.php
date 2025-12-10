<?php

namespace Modules\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \Modules\Application\Entities\Application::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Datos principales
            'job_profile_vacancy_id' => ['required', 'uuid', 'exists:job_profile_vacancies,id'],
            'terms_accepted' => ['required', 'accepted'],

            // Datos personales
            'personal_data' => ['required', 'array'],
            'personal_data.full_name' => ['required', 'string', 'max:255'],
            'personal_data.dni' => ['required', 'string', 'size:8', 'regex:/^[0-9]{8}$/'],
            'personal_data.birth_date' => ['required', 'date', 'before:today'],
            'personal_data.address' => ['required', 'string', 'max:500'],
            'personal_data.mobile_phone' => ['required', 'string', 'max:20'],
            'personal_data.phone' => ['nullable', 'string', 'max:20'],
            'personal_data.email' => ['required', 'email', 'max:255'],

            // Formación académica (mínimo 1)
            'academics' => ['required', 'array', 'min:1'],
            'academics.*.institution_name' => ['required', 'string', 'max:255'],
            'academics.*.degree_type' => ['required', 'string', Rule::in(['SECUNDARIA', 'TECNICO', 'BACHILLER', 'TITULO', 'MAESTRIA', 'DOCTORADO'])],
            'academics.*.degree_title' => ['required', 'string', 'max:255'],
            'academics.*.career_field' => ['nullable', 'string', 'max:255'],
            'academics.*.issue_date' => ['required', 'date', 'before_or_equal:today'],

            // Experiencia laboral (opcional)
            'experiences' => ['nullable', 'array'],
            'experiences.*.organization' => ['required', 'string', 'max:255'],
            'experiences.*.position' => ['required', 'string', 'max:255'],
            'experiences.*.start_date' => ['required', 'date', 'before_or_equal:today'],
            'experiences.*.end_date' => ['required', 'date', 'after_or_equal:experiences.*.start_date', 'before_or_equal:today'],
            'experiences.*.is_specific' => ['boolean'],
            'experiences.*.is_public_sector' => ['boolean'],

            // Capacitaciones (opcional)
            'trainings' => ['nullable', 'array'],
            'trainings.*.institution' => ['required', 'string', 'max:255'],
            'trainings.*.course_name' => ['required', 'string', 'max:255'],
            'trainings.*.academic_hours' => ['nullable', 'integer', 'min:1'],
            'trainings.*.start_date' => ['nullable', 'date', 'before_or_equal:today'],
            'trainings.*.end_date' => ['nullable', 'date', 'after_or_equal:trainings.*.start_date', 'before_or_equal:today'],

            // Condiciones especiales (opcional)
            'special_conditions' => ['nullable', 'array'],
            'special_conditions.*.condition_type' => ['required', 'string', Rule::in(['DISABILITY', 'MILITARY', 'ATHLETE_NATIONAL', 'ATHLETE_INTL', 'TERRORISM'])],
            'special_conditions.*.issuing_entity' => ['nullable', 'string', 'max:255'],
            'special_conditions.*.document_number' => ['nullable', 'string', 'max:100'],
            'special_conditions.*.issue_date' => ['nullable', 'date', 'before_or_equal:today'],
            'special_conditions.*.expiry_date' => ['nullable', 'date', 'after:today'],
            'special_conditions.*.bonus_percentage' => ['required', 'numeric', 'min:0', 'max:100'],

            // Registros profesionales (opcional)
            'professional_registrations' => ['nullable', 'array'],
            'professional_registrations.*.registration_type' => ['required', 'string', Rule::in(['COLEGIATURA', 'OSCE_CERTIFICATION', 'DRIVER_LICENSE'])],
            'professional_registrations.*.issuing_entity' => ['nullable', 'string', 'max:255'],
            'professional_registrations.*.registration_number' => ['nullable', 'string', 'max:100'],
            'professional_registrations.*.issue_date' => ['nullable', 'date', 'before_or_equal:today'],
            'professional_registrations.*.expiry_date' => ['nullable', 'date', 'after:today'],

            // Conocimientos (opcional)
            'knowledge' => ['nullable', 'array'],
            'knowledge.*.knowledge_name' => ['required', 'string', 'max:255'],
            'knowledge.*.proficiency_level' => ['nullable', 'string', Rule::in(['BASICO', 'INTERMEDIO', 'AVANZADO', 'EXPERTO'])],

            // Notas adicionales
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'job_profile_vacancy_id.required' => 'Debe seleccionar una vacante para postular',
            'job_profile_vacancy_id.exists' => 'La vacante seleccionada no existe o no está disponible',
            'terms_accepted.accepted' => 'Debe aceptar los términos y condiciones',
            'personal_data.dni.regex' => 'El DNI debe contener exactamente 8 dígitos',
            'academics.min' => 'Debe registrar al menos una formación académica',
            'experiences.*.end_date.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio',
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'applicant_id' => $this->user()->id,
            'ip_address' => $this->ip(),
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'personal_data.full_name' => 'nombre completo',
            'personal_data.dni' => 'DNI',
            'personal_data.birth_date' => 'fecha de nacimiento',
            'personal_data.address' => 'dirección',
            'personal_data.mobile_phone' => 'teléfono móvil',
            'personal_data.email' => 'correo electrónico',
        ];
    }
}
