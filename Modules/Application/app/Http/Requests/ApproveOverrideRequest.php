<?php

namespace Modules\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveOverrideRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('eligibility.override');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'resolution_summary' => ['required', 'string', 'max:255'],
            'resolution_detail' => ['required', 'string', 'min:20', 'max:2000'],
            'resolution_type' => ['nullable', 'string', Rule::in(['CLAIM', 'CORRECTION', 'OTHER'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'resolution_summary.required' => 'Debe ingresar un resumen de la resolución',
            'resolution_summary.max' => 'El resumen no puede exceder 255 caracteres',
            'resolution_detail.required' => 'Debe ingresar el detalle de la resolución',
            'resolution_detail.min' => 'El detalle debe tener al menos 20 caracteres',
            'resolution_detail.max' => 'El detalle no puede exceder 2000 caracteres',
            'resolution_type.in' => 'El tipo de resolución debe ser CLAIM, CORRECTION u OTHER',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'resolution_summary' => 'resumen de resolución',
            'resolution_detail' => 'detalle de resolución',
            'resolution_type' => 'tipo de resolución',
        ];
    }
}
