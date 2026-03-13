<?php

namespace App\Http\Requests\Participant;

use App\Enums\Gender;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateParticipantRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'birthday' => ['nullable', 'date', 'before:today'],
            'contact_number' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'cell_group_id' => ['nullable', 'exists:cell_groups,id'],
            'classification_id' => ['nullable', 'exists:classifications,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'ministry_ids' => ['nullable', 'array'],
            'ministry_ids.*' => ['exists:ministries,id'],
            'date_joined' => ['required', 'date'],
            'is_active' => ['boolean'],
        ];
    }
}
