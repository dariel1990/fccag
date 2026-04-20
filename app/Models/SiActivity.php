<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiActivity extends Model
{
    /** @use HasFactory<\Database\Factories\SiActivityFactory> */
    use HasFactory;

    protected $fillable = [
        'si_activity_category_id',
        'activity_id',
        'title',
        'speaker',
        'topic',
        'memory_verse',
        'conducted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'conducted_at' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SiActivityCategory::class, 'si_activity_category_id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function assignedMembers(): BelongsToMany
    {
        return $this->belongsToMany(SiMember::class, 'si_activity_member')
            ->withTimestamps();
    }

    public function siAttendances(): HasMany
    {
        return $this->hasMany(SiAttendance::class);
    }
}
