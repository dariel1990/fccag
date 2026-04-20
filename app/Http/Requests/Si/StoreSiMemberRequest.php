<?php

namespace App\Http\Requests\Si;

use App\Enums\SiMemberSex;
use App\Enums\SiMemberStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiMemberRequest extends FormRequest
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
            'caregiver_id' => ['nullable', 'exists:people,id'],
            'caregiver' => ['required_without:caregiver_id', 'array'],
            'caregiver.first_name' => ['required_without:caregiver_id', 'string', 'max:255'],
            'caregiver.last_name' => ['required_without:caregiver_id', 'string', 'max:255'],
            'caregiver.contact_number' => ['nullable', 'string', 'max:255'],
            'caregiver.address' => ['nullable', 'string', 'max:1000'],
            'name' => ['required', 'string', 'max:255'],
            'sex' => ['required', Rule::enum(SiMemberSex::class)],
            'ph_id' => ['nullable', 'string', 'max:255', 'unique:si_members,ph_id'],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::enum(SiMemberStatus::class)],
            'enrolled_at' => ['required', 'date'],
            'exited_at' => ['nullable', 'date', 'after_or_equal:enrolled_at'],
        ];
    }
}
