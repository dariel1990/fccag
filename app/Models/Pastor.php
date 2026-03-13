<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Pastor extends Model
{
    /** @use HasFactory<\Database\Factories\PastorFactory> */
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'title',
        'role',
        'bio',
        'photo_path',
        'contact_number',
        'email',
        'date_started',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_started' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function getFullNameAttribute(): string
    {
        $title = $this->title ? "{$this->title} " : '';

        return "{$title}{$this->first_name} {$this->last_name}";
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }
}
