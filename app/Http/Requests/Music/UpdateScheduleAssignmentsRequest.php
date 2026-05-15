<?php

namespace App\Http\Requests\Music;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleAssignmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'assignments' => ['present', 'array'],
            'assignments.*.schedule_role_id' => ['required', 'integer', 'exists:schedule_roles,id'],
            'assignments.*.music_member_id' => ['nullable', 'integer', 'exists:music_members,id'],
            'assignments.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
