<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'room_id',
        'voter_player_id',
        'target_player_id',
        'round_number',
    ];

    protected $casts = [
        'round_number' => 'integer',
        'created_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'voter_player_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'target_player_id');
    }
}
