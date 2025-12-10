<?php

namespace Modules\Evaluation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Evaluation\Enums\EvaluationStatusEnum;

class UpdateEvaluationRequest extends FormRequest
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
            'general_comments' => ['nullable', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', 'string', 'in:' . implode(',', EvaluationStatusEnum::values())],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'general_comments.max' => 'Los comentarios generales no pueden exceder 5000 caracteres',
            'internal_notes.max' => 'Las notas internas no pueden exceder 5000 caracteres',
            'status.in' => 'El estado proporcionado no es válido',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'general_comments' => 'comentarios generales',
            'internal_notes' => 'notas internas',
            'status' => 'estado',
        ];
    }
}