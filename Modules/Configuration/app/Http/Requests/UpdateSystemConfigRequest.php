<?php

namespace Modules\Configuration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemConfigRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('configuration.update');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'value' => [
                'required',
            ],
            'change_reason' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'value' => 'valor',
            'change_reason' => 'razón del cambio',
        ];
    }

    /**
     * Get custom messages.
     */
    public function messages(): array
    {
        return [
            'value.required' => 'El valor de la configuración es obligatorio.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Agregar el usuario que realiza el cambio
        $this->merge([
            'changed_by' => $this->user()->id ?? null,
        ]);
    }
}
