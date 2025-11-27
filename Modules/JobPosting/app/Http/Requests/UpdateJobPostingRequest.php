<?php

namespace Modules\JobPosting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobPostingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'year' => 'sometimes|integer|min:2000|max:' . (now()->year + 1),
            'description' => 'nullable|string|max:5000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'metadata' => 'nullable|array',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'título',
            'year' => 'año',
            'description' => 'descripción',
            'start_date' => 'fecha de inicio',
            'end_date' => 'fecha de fin',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'El título de la convocatoria es obligatorio.',
            'title.max' => 'El título no puede exceder 255 caracteres.',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];
    }
}