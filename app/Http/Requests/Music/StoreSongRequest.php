<?php

namespace App\Http\Requests\Music;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSongRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['nullable', 'string', 'max:255'],
            'original_key' => ['required', 'string', 'in:C,C#,Db,D,D#,Eb,E,F,F#,Gb,G,G#,Ab,A,A#,Bb,B'],
            'tempo' => ['nullable', 'integer', 'min:20', 'max:300'],
            'time_signature' => ['nullable', 'string', 'max:10'],
            'lyrics_with_chords' => ['required', 'string'],
            'composer' => ['nullable', 'string', 'max:255'],
            'video_link' => ['nullable', 'string', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
        ];
    }
}
