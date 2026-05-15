<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteCast implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Room $room) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('room.'.$this->room->code),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'players' => $this->room->players->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'vote_count' => (int) $p->pivot->vote_count,
            ])->values()->all(),
        ];
    }
}
