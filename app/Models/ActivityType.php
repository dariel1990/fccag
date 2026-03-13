<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityType extends Model
{
    /** @use HasFactory<\Database\Factories\ActivityTypeFactory> */
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

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'activity_type_department')
            ->withTimestamps();
    }
}
