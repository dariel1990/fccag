<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Department extends Model
{
    /** @use HasFactory<\Database\Factories\DepartmentFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'photo_path',
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

    public function members(): HasMany
    {
        return $this->hasMany(Participant::class, 'department_id');
    }

    public function officers(): HasMany
    {
        return $this->hasMany(DepartmentOfficer::class);
    }

    public function currentOfficers(): HasMany
    {
        return $this->hasMany(DepartmentOfficer::class)->whereNull('ended_at');
    }

    public function activityTypes(): BelongsToMany
    {
        return $this->belongsToMany(ActivityType::class, 'activity_type_department')
            ->withTimestamps();
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }
}
