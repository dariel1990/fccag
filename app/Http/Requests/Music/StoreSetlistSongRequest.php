<?php

namespace App\Http\Requests\Music;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSetlistSongRequest extends FormRequest
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
            'song_id' => ['required', 'integer', 'exists:songs,id'],
            'key_override' => ['nullable', 'string', 'in:C,C#,Db,D,D#,Eb,E,F,F#,Gb,G,G#,Ab,A,A#,Bb,B'],
            'notes' => ['nullable', 'string', 'max:500'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
