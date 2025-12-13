<?php

namespace Modules\Jury\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJuryMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajustar con policies
    }

    public function rules(): array
    {
        $juryMemberId = $this->route('id');

        return [
            'user_id' => ['sometimes', 'string', 'exists:users,id', Rule::unique('jury_members', 'user_id')->ignore($juryMemberId)],
            'specialty' => ['nullable', 'string', 'max:255'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:50'],
            'professional_title' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'is_available' => ['boolean'],
            'unavailability_reason' => ['nullable', 'string'],
            'unavailable_from' => ['nullable', 'date'],
            'unavailable_until' => ['nullable', 'date', 'after:unavailable_from'],
            'training_completed' => ['boolean'],
            'training_certificate_path' => ['nullable', 'string'],
            'max_concurrent_assignments' => ['nullable', 'integer', 'min:1', 'max:100'],
            'preferred_areas' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
