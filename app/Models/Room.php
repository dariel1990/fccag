<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'code',
        'host_player_id',
        'status',
        'category',
        'word',
        'impostor_word',
        'round_number',
        'total_rounds',
    ];

    protected $casts = [
        'round_number' => 'integer',
        'total_rounds' => 'integer',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'host_player_id');
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'room_players')
            ->withPivot([
                'is_impostor',
                'assigned_word',
                'is_eliminated',
                'vote_count',
                'joined_at',
            ]);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function roundResults(): HasMany
    {
        return $this->hasMany(RoundResult::class);
    }
}
