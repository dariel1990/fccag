<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleAssignment extends Model
{
    /** @use HasFactory<\Database\Factories\ScheduleAssignmentFactory> */
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'schedule_role_id',
        'music_member_id',
        'notes',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(ScheduleRole::class, 'schedule_role_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(MusicMember::class, 'music_member_id');
    }
}
