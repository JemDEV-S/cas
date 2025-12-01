<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('user.transfer.organization');
    }

    public function rules(): array
    {
        return [
            'from_unit_id' => [
                'required',
                'uuid',
                'exists:organizational_units,id',
                'different:to_unit_id',
            ],
            'to_unit_id' => [
                'required',
                'uuid',
                'exists:organizational_units,id',
            ],
            'transfer_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'from_unit_id.required' => 'La unidad origen es obligatoria',
            'from_unit_id.exists' => 'La unidad origen seleccionada no existe',
            'from_unit_id.different' => 'La unidad origen y destino deben ser diferentes',
            'to_unit_id.required' => 'La unidad destino es obligatoria',
            'to_unit_id.exists' => 'La unidad destino seleccionada no existe',
            'transfer_date.required' => 'La fecha de transferencia es obligatoria',
            'transfer_date.after_or_equal' => 'La fecha de transferencia debe ser hoy o posterior',
        ];
    }
}