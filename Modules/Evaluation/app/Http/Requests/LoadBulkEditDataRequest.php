<?php

namespace Modules\Evaluation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoadBulkEditDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assign-evaluators');
    }

    public function rules(): array
    {
        return [
            'job_posting_id' => ['required', 'exists:job_postings,uuid'],
            'phase_id' => ['required', 'exists:process_phases,uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'job_posting_id.required' => 'Debe seleccionar una convocatoria',
            'job_posting_id.exists' => 'La convocatoria seleccionada no existe',
            'phase_id.required' => 'Debe seleccionar una fase',
            'phase_id.exists' => 'La fase seleccionada no existe',
        ];
    }
}
