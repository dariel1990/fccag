<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentOfficer extends Model
{
    /** @use HasFactory<\Database\Factories\DepartmentOfficerFactory> */
    use HasFactory;

    protected $fillable = [
        'department_id',
        'person_id',
        'role',
        'started_at',
        'ended_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'person_id');
    }

    public function getIsCurrentAttribute(): bool
    {
        return $this->ended_at === null;
    }
}
