<?php

namespace App\Http\Requests\Si;

use App\Enums\SiMemberSex;
use App\Enums\SiMemberStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSiMemberRequest extends FormRequest
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
        $hasNewCaregiver = $this->filled('caregiver.first_name');

        return [
            'caregiver_id' => [$hasNewCaregiver ? 'nullable' : 'required', 'exists:people,id'],
            'caregiver' => ['nullable', 'array'],
            'caregiver.first_name' => [$hasNewCaregiver ? 'required' : 'nullable', 'string', 'max:255'],
            'caregiver.last_name' => [$hasNewCaregiver ? 'required' : 'nullable', 'string', 'max:255'],
            'caregiver.contact_number' => ['nullable', 'string', 'max:255'],
            'caregiver.address' => ['nullable', 'string', 'max:1000'],
            'name' => ['required', 'string', 'max:255'],
            'sex' => ['required', Rule::enum(SiMemberSex::class)],
            'ph_id' => ['nullable', 'string', 'max:255', Rule::unique('si_members', 'ph_id')->ignore($this->route('member'))],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::enum(SiMemberStatus::class)],
            'enrolled_at' => ['required', 'date'],
            'exited_at' => ['nullable', 'date', 'after_or_equal:enrolled_at'],
        ];
    }
}
