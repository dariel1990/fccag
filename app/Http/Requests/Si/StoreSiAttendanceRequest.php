<?php

namespace App\Http\Requests\Si;

use App\Enums\SiAttendanceStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiAttendanceRequest extends FormRequest
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
            'attendances' => ['required', 'array'],
            'attendances.*.si_member_id' => ['required', 'exists:si_members,id'],
            'attendances.*.status' => ['required', Rule::enum(SiAttendanceStatus::class)],
            'attendances.*.remarks' => ['nullable', 'string'],
            'attendances.*.recommendation' => ['nullable', 'string'],
        ];
    }
}
