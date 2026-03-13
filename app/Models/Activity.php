<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    /** @use HasFactory<\Database\Factories\ActivityFactory> */
    use HasFactory;

    protected $fillable = [
        'activity_type_id',
        'title',
        'description',
        'activity_date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
        ];
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class, 'attendances', 'activity_id', 'person_id')
            ->withPivot(['is_present', 'remarks'])
            ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
