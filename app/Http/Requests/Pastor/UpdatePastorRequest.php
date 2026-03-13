<?php

namespace App\Http\Requests\Pastor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePastorRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:50'],
            'role' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'date_started' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ];
    }
}
