<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleRole extends Model
{
    /** @use HasFactory<\Database\Factories\ScheduleRoleFactory> */
    use HasFactory;

    protected $fillable = [
        'team',
        'name',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function scopeForTeam(Builder $query, string $team): Builder
    {
        return $query->where('team', $team);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ScheduleAssignment::class);
    }
}
