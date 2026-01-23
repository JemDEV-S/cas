<?php

namespace Modules\Evaluation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitEvaluationRequest extends FormRequest
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
            'confirm' => ['required', 'boolean', 'accepted'],
            'general_comments' => ['nullable', 'string', 'max:5000'],
            'disqualified' => ['nullable', 'boolean'],
            'disqualification_reason' => ['nullable', 'string', 'max:2000'],
            'disqualification_type' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'confirm.required' => 'Debe confirmar el envío de la evaluación',
            'confirm.accepted' => 'Debe aceptar que la evaluación no podrá ser modificada después del envío',
            'general_comments.max' => 'Los comentarios generales no pueden exceder 5000 caracteres',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'confirm' => 'confirmación',
            'general_comments' => 'comentarios generales',
        ];
    }
}