<?php

namespace App\Support;

use App\Models\Player;
use Illuminate\Support\Str;

class PlayerIdentity
{
    private const SESSION_KEY = 'impostor.player_id';

    private const TOKEN_KEY = 'impostor.session_token';

    public function current(): ?Player
    {
        $playerId = session(self::SESSION_KEY);

        if (! $playerId) {
            return null;
        }

        $player = Player::find($playerId);

        if ($player) {
            $player->forceFill(['last_seen_at' => now()])->save();
        }

        return $player;
    }

    public function register(string $name): Player
    {
        $existing = $this->current();

        if ($existing) {
            return $this->setName($name);
        }

        $token = session(self::TOKEN_KEY) ?? Str::random(64);

        $player = Player::firstOrCreateForSession($token, $name);
        $player->forceFill(['name' => $name])->save();

        session([
            self::TOKEN_KEY => $token,
            self::SESSION_KEY => $player->id,
        ]);

        return $player;
    }

    public function setName(string $name): Player
    {
        $player = $this->current();

        if (! $player) {
            return $this->register($name);
        }

        $player->forceFill(['name' => $name])->save();

        return $player;
    }
}
