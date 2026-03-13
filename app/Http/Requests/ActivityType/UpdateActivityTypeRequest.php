<?php

namespace App\Http\Requests\ActivityType;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateActivityTypeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', Rule::unique('activity_types', 'name')->ignore($this->route('activity_type'))],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'department_ids' => ['nullable', 'array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
        ];
    }
}
