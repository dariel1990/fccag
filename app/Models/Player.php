<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    protected $fillable = [
        'name',
        'session_token',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
        ];
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_players')
            ->withPivot(['is_impostor', 'assigned_word', 'is_eliminated', 'vote_count', 'joined_at']);
    }

    public function hostedRooms(): HasMany
    {
        return $this->hasMany(Room::class, 'host_player_id');
    }

    public static function firstOrCreateForSession(string $sessionToken, ?string $name = null): self
    {
        $player = self::firstOrCreate(
            ['session_token' => $sessionToken],
            ['name' => $name ?? 'Guest', 'last_seen_at' => now()],
        );

        $player->forceFill(['last_seen_at' => now()])->save();

        return $player;
    }
}
