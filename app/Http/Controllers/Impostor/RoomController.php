<?php

namespace App\Http\Controllers\Impostor;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Room;
use App\Services\GameService;
use App\Support\PlayerIdentity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RoomController extends Controller
{
    public function __construct(
        private readonly GameService $games,
        private readonly PlayerIdentity $identity,
    ) {}

    public function index(): Response
    {
        $player = $this->identity->current();

        return Inertia::render('impostor/Lobby', [
            'currentPlayer' => $player ? [
                'id' => $player->id,
                'name' => $player->name,
            ] : null,
        ]);
    }

    public function setName(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:32'],
        ]);

        $this->identity->register($data['name']);

        return back();
    }

    public function store(Request $request): RedirectResponse
    {
        $player = $this->identity->current();
        abort_unless($player !== null, 403, 'Set your name first.');

        $data = $request->validate([
            'total_rounds' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $room = $this->games->createRoom($player, (int) ($data['total_rounds'] ?? 3));

        return redirect()->route('impostor.rooms.show', ['code' => $room->code]);
    }

    public function join(Request $request): RedirectResponse
    {
        $player = $this->identity->current();
        abort_unless($player !== null, 403, 'Set your name first.');

        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $room = $this->games->joinRoom($data['code'], $player);

        return redirect()->route('impostor.rooms.show', ['code' => $room->code]);
    }

    public function show(string $code): Response|RedirectResponse
    {
        $player = $this->identity->current();

        if ($player === null) {
            return redirect()->route('impostor.lobby')->with('flash.error', 'Set your name to join this room.');
        }

        $room = $this->resolveRoom($code);

        $room->load('players');

        if (! $room->players->contains('id', $player->id)) {
            return redirect()->route('impostor.lobby')->with('flash.error', 'You are not a member of this room.');
        }

        $myPlayer = $room->players->firstWhere('id', $player->id);

        $isRevealState = in_array($room->status, ['round_end', 'finished', 'ended'], true);

        return Inertia::render('impostor/Room/Show', [
            'room' => [
                'id' => $room->id,
                'code' => $room->code,
                'host_player_id' => $room->host_player_id,
                'status' => $room->status,
                'category' => $room->category,
                'round_number' => $room->round_number,
                'total_rounds' => (int) $room->total_rounds,
                'players' => $room->players->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'is_eliminated' => (bool) $p->pivot->is_eliminated,
                    'vote_count' => (int) $p->pivot->vote_count,
                ])->values()->all(),
            ],
            'myPivot' => $myPlayer ? [
                'is_impostor' => (bool) $myPlayer->pivot->is_impostor,
                'assigned_word' => $myPlayer->pivot->assigned_word,
                'is_eliminated' => (bool) $myPlayer->pivot->is_eliminated,
            ] : null,
            'reveal' => $isRevealState ? [
                'real_word' => $room->word,
                'impostor_word' => $room->impostor_word,
                'players' => $room->players->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'is_impostor' => (bool) $p->pivot->is_impostor,
                    'assigned_word' => $p->pivot->assigned_word,
                    'vote_count' => (int) $p->pivot->vote_count,
                ])->values()->all(),
            ] : null,
            'scoreboard' => in_array($room->status, ['finished', 'ended'], true)
                ? $this->games->finalScoreboard($room)
                : null,
            'currentPlayer' => [
                'id' => $player->id,
                'name' => $player->name,
            ],
        ]);
    }

    public function leave(string $code): RedirectResponse
    {
        $room = $this->resolveRoom($code);
        $player = $this->identity->current();

        if (! $player) {
            return redirect()->route('impostor.lobby');
        }

        $this->games->leaveRoom($room, $player);

        return redirect()->route('impostor.lobby')->with('flash.success', 'You left the room.');
    }

    public function start(string $code): RedirectResponse
    {
        $room = $this->resolveRoom($code);
        $this->ensureHost($room);

        $this->games->startGame($room);

        return back();
    }

    public function openVoting(string $code): RedirectResponse
    {
        $room = $this->resolveRoom($code);
        $this->ensureHost($room);

        $this->games->openVoting($room);

        return back();
    }

    public function vote(Request $request, string $code): RedirectResponse
    {
        $player = $this->identity->current();
        abort_unless($player !== null, 403);

        $data = $request->validate([
            'target_player_id' => ['required', 'integer', 'exists:players,id'],
        ]);

        $room = $this->resolveRoom($code);
        $target = Player::findOrFail($data['target_player_id']);

        $this->games->castVote($room, $player, $target);

        return back();
    }

    public function resolve(string $code): RedirectResponse
    {
        $room = $this->resolveRoom($code);
        $this->ensureHost($room);

        $this->games->resolveVoting($room);

        return back();
    }

    public function nextRound(string $code): RedirectResponse
    {
        $room = $this->resolveRoom($code);
        $this->ensureHost($room);

        $this->games->startNextRound($room);

        return back();
    }

    private function resolveRoom(string $code): Room
    {
        return Room::where('code', strtoupper($code))->firstOrFail();
    }

    private function ensureHost(Room $room): void
    {
        $player = $this->identity->current();

        if ($player === null || $player->id !== $room->host_player_id) {
            throw new AccessDeniedHttpException('Only the host can perform this action.');
        }
    }
}
