<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoundResult extends Model
{
    protected $fillable = [
        'room_id',
        'round_number',
        'category',
        'real_word',
        'impostor_word',
        'impostor_player_id',
        'eliminated_player_id',
        'outcome',
    ];

    protected $casts = [
        'round_number' => 'integer',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function impostor(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'impostor_player_id');
    }

    public function eliminated(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'eliminated_player_id');
    }
}
