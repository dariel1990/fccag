<?php

namespace App\Events;

use App\Models\Player;
use App\Models\Room;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoundEnded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Room $room,
        public string $outcome,
        public ?Player $impostor,
        public string $impostorWord,
        public string $realWord,
        public int $roundNumber,
        public int $totalRounds,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('room.'.$this->room->code),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'outcome' => $this->outcome,
            'impostor' => $this->impostor ? [
                'id' => $this->impostor->id,
                'name' => $this->impostor->name,
            ] : null,
            'impostor_word' => $this->impostorWord,
            'real_word' => $this->realWord,
            'round_number' => $this->roundNumber,
            'total_rounds' => $this->totalRounds,
        ];
    }
}
