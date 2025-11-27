<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('user.update.assignment');
    }

    public function rules(): array
    {
        return [
            'start_date' => [
                'sometimes',
                'date',
            ],
            'end_date' => [
                'nullable',
                'date',
                'after:start_date',
            ],
            'is_primary' => [
                'sometimes',
                'boolean',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.date' => 'La fecha de inicio debe ser una fecha válida',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
        ];
    }
}