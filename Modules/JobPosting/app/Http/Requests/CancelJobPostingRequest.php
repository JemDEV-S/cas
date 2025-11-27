<?php

namespace Modules\JobPosting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelJobPostingRequest extends FormRequest
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
            'cancellation_reason' => 'required|string|min:10|max:1000',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'cancellation_reason' => 'motivo de cancelación',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cancellation_reason.required' => 'Debe especificar el motivo de la cancelación.',
            'cancellation_reason.min' => 'El motivo debe tener al menos 10 caracteres.',
            'cancellation_reason.max' => 'El motivo no puede exceder 1000 caracteres.',
        ];
    }
}