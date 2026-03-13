<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ministry extends Model
{
    /** @use HasFactory<\Database\Factories\MinistryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class, 'ministry_person', 'ministry_id', 'person_id')
            ->withTimestamps();
    }
}
