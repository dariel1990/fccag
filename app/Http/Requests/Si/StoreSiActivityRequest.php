<?php

namespace App\Http\Requests\Si;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSiActivityRequest extends FormRequest
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
            'si_activity_category_id' => ['required', 'exists:si_activity_categories,id'],
            'activity_id' => ['nullable', 'exists:activities,id'],
            'title' => ['required', 'string', 'max:255'],
            'speaker' => ['nullable', 'string', 'max:255'],
            'topic' => ['nullable', 'string', 'max:255'],
            'memory_verse' => ['nullable', 'string', 'max:255'],
            'conducted_at' => ['required', 'date'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['exists:si_members,id'],
        ];
    }
}
