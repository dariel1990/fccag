<?php

namespace App\Services;

use App\Events\GameEnded;
use App\Events\GameStarted;
use App\Events\PlayerJoined;
use App\Events\PlayerLeft;
use App\Events\RoundEnded;
use App\Events\VoteCast;
use App\Models\Player;
use App\Models\Room;
use App\Models\RoundResult;
use App\Models\Vote;
use App\Models\WordPack;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GameService
{
    private const MIN_PLAYERS = 3;

    private const MIN_ROUNDS = 1;

    private const MAX_ROUNDS = 10;

    public function createRoom(Player $host, int $totalRounds = 3): Room
    {
        $totalRounds = max(self::MIN_ROUNDS, min(self::MAX_ROUNDS, $totalRounds));

        return DB::transaction(function () use ($host, $totalRounds) {
            $room = Room::create([
                'code' => $this->generateUniqueCode(),
                'host_player_id' => $host->id,
                'status' => 'waiting',
                'total_rounds' => $totalRounds,
            ]);

            $room->players()->attach($host->id, [
                'joined_at' => now(),
            ]);

            return $room;
        });
    }

    public function joinRoom(string $code, Player $player): Room
    {
        $room = Room::where('code', strtoupper($code))->first();

        if (! $room) {
            throw ValidationException::withMessages([
                'code' => 'No room found with that code.',
            ]);
        }

        if ($room->status !== 'waiting') {
            throw ValidationException::withMessages([
                'code' => 'This game has already started.',
            ]);
        }

        if ($room->players()->where('player_id', $player->id)->exists()) {
            throw ValidationException::withMessages([
                'code' => 'You are already in this room.',
            ]);
        }

        $room->players()->attach($player->id, [
            'joined_at' => now(),
        ]);

        $room = $room->fresh();
        $room->load('players');

        PlayerJoined::dispatch($room);

        return $room;
    }

    public function leaveRoom(Room $room, Player $player): ?Room
    {
        if (! $room->players()->where('player_id', $player->id)->exists()) {
            return $room;
        }

        return DB::transaction(function () use ($room, $player) {
            $room->players()->detach($player->id);

            $remaining = $room->players()->get();

            if ($remaining->isEmpty()) {
                $room->delete();

                return null;
            }

            if ($room->host_player_id === $player->id) {
                $room->host_player_id = $remaining->first()->id;
                $room->save();
            }

            $room = $room->fresh();
            $room->load('players');

            PlayerLeft::dispatch($room);

            return $room;
        });
    }

    public function startGame(Room $room): void
    {
        if ($room->status !== 'waiting') {
            throw ValidationException::withMessages([
                'status' => 'Game has already started.',
            ]);
        }

        $players = $room->players()->get();

        if ($players->count() < self::MIN_PLAYERS) {
            throw ValidationException::withMessages([
                'players' => 'At least '.self::MIN_PLAYERS.' players are required to start.',
            ]);
        }

        $this->assignRound($room, $players);

        $room->load('players');

        GameStarted::dispatch($room);
    }

    public function startNextRound(Room $room): void
    {
        if ($room->status !== 'round_end') {
            throw ValidationException::withMessages([
                'status' => 'A round is not ready to advance.',
            ]);
        }

        if ($room->round_number >= $room->total_rounds) {
            throw ValidationException::withMessages([
                'rounds' => 'No more rounds remain.',
            ]);
        }

        $room->increment('round_number');
        $room->refresh();

        $players = $room->players()->get();
        $this->assignRound($room, $players);

        $room->load('players');

        GameStarted::dispatch($room);
    }

    public function openVoting(Room $room): void
    {
        if ($room->status !== 'playing') {
            throw ValidationException::withMessages([
                'status' => 'Voting can only be opened from the playing phase.',
            ]);
        }

        $room->update(['status' => 'voting']);

        $room->load('players');

        GameStarted::dispatch($room);
    }

    public function castVote(Room $room, Player $voter, Player $target): void
    {
        if ($voter->id === $target->id) {
            throw ValidationException::withMessages([
                'target' => 'You cannot vote for yourself.',
            ]);
        }

        $alreadyVoted = Vote::where('room_id', $room->id)
            ->where('voter_player_id', $voter->id)
            ->where('round_number', $room->round_number)
            ->exists();

        if ($alreadyVoted) {
            throw ValidationException::withMessages([
                'target' => 'You have already voted this round.',
            ]);
        }

        DB::transaction(function () use ($room, $voter, $target) {
            Vote::create([
                'room_id' => $room->id,
                'voter_player_id' => $voter->id,
                'target_player_id' => $target->id,
                'round_number' => $room->round_number,
            ]);

            DB::table('room_players')
                ->where('room_id', $room->id)
                ->where('player_id', $target->id)
                ->increment('vote_count');
        });

        $room->load('players');

        VoteCast::dispatch($room);
    }

    public function resolveVoting(Room $room): array
    {
        $room->load('players');
        $players = $room->players;

        $maxVotes = (int) $players->max(fn ($p) => $p->pivot->vote_count);
        $actualImpostor = $players->first(fn ($p) => (bool) $p->pivot->is_impostor);

        $eliminated = null;
        $outcome = 'tie';

        if ($maxVotes > 0) {
            $topPlayers = $players->filter(fn ($p) => (int) $p->pivot->vote_count === $maxVotes);

            if ($topPlayers->count() === 1) {
                $eliminated = $topPlayers->first();
                $room->players()->updateExistingPivot($eliminated->id, ['is_eliminated' => true]);
                $outcome = ((bool) $eliminated->pivot->is_impostor) ? 'caught' : 'escaped';
            }
        }

        RoundResult::create([
            'room_id' => $room->id,
            'round_number' => $room->round_number,
            'category' => (string) $room->category,
            'real_word' => (string) $room->word,
            'impostor_word' => $room->impostor_word,
            'impostor_player_id' => $actualImpostor?->id,
            'eliminated_player_id' => $eliminated?->id,
            'outcome' => $outcome,
        ]);

        $impostorForPayload = $eliminated && (bool) $eliminated->pivot->is_impostor
            ? $eliminated
            : $actualImpostor;

        $isFinalRound = $room->round_number >= $room->total_rounds;

        if ($isFinalRound) {
            $room->update(['status' => 'finished']);

            GameEnded::dispatch($room, $outcome, $impostorForPayload, $room->impostor_word, $room->word);
        } else {
            $room->update(['status' => 'round_end']);

            RoundEnded::dispatch(
                $room,
                $outcome,
                $impostorForPayload,
                (string) $room->impostor_word,
                (string) $room->word,
                $room->round_number,
                $room->total_rounds,
            );
        }

        return [
            'result' => $outcome,
            'impostor' => $impostorForPayload,
            'is_final' => $isFinalRound,
        ];
    }

    /**
     * Aggregate per-player stats across all completed rounds for the final scoreboard.
     *
     * @return array{
     *     rounds: array<int, array{round: int, category: string, real_word: string, impostor_word: string, impostor: ?array{id:int,name:string}, outcome: string}>,
     *     players: array<int, array{id:int, name:string, times_as_impostor:int, successful_bluffs:int, votes_received_as_impostor:int, correct_votes:int, total_votes_cast:int, votes_received_as_innocent:int}>,
     *     best_impostor: ?array{id:int, name:string, successful_bluffs:int, times_as_impostor:int},
     *     best_detective: ?array{id:int, name:string, correct_votes:int},
     *     most_suspicious: ?array{id:int, name:string, votes_received_as_innocent:int},
     * }
     */
    public function finalScoreboard(Room $room): array
    {
        $room->load(['players', 'roundResults', 'votes']);

        $rounds = $room->roundResults->keyBy('round_number');
        $impostorByRound = $rounds->mapWithKeys(fn ($r) => [$r->round_number => $r->impostor_player_id])->all();

        $players = $room->players;

        $stats = [];
        foreach ($players as $p) {
            $stats[$p->id] = [
                'id' => $p->id,
                'name' => $p->name,
                'times_as_impostor' => 0,
                'successful_bluffs' => 0,
                'votes_received_as_impostor' => 0,
                'votes_received_as_innocent' => 0,
                'correct_votes' => 0,
                'total_votes_cast' => 0,
            ];
        }

        foreach ($rounds as $round) {
            if (isset($stats[$round->impostor_player_id])) {
                $stats[$round->impostor_player_id]['times_as_impostor']++;
                if (in_array($round->outcome, ['escaped', 'tie'], true)) {
                    $stats[$round->impostor_player_id]['successful_bluffs']++;
                }
            }
        }

        // Aggregate votes
        foreach ($room->votes as $vote) {
            $impostorThisRound = $impostorByRound[$vote->round_number] ?? null;

            if (isset($stats[$vote->voter_player_id])) {
                $stats[$vote->voter_player_id]['total_votes_cast']++;
                if ($impostorThisRound !== null && $vote->target_player_id === $impostorThisRound) {
                    $stats[$vote->voter_player_id]['correct_votes']++;
                }
            }

            if (isset($stats[$vote->target_player_id])) {
                if ($impostorThisRound !== null && $vote->target_player_id === $impostorThisRound) {
                    $stats[$vote->target_player_id]['votes_received_as_impostor']++;
                } else {
                    $stats[$vote->target_player_id]['votes_received_as_innocent']++;
                }
            }
        }

        $statsArr = array_values($stats);

        $bestImpostor = $this->pickBestImpostor($statsArr);
        $bestDetective = $this->pickBestDetective($statsArr);
        $mostSuspicious = $this->pickMostSuspicious($statsArr);

        $playersById = $players->keyBy('id');

        $roundsPayload = $rounds->sortBy('round_number')->values()->map(function (RoundResult $r) use ($playersById) {
            $impostor = $playersById->get($r->impostor_player_id);

            return [
                'round' => $r->round_number,
                'category' => $r->category,
                'real_word' => $r->real_word,
                'impostor_word' => $r->impostor_word,
                'impostor' => $impostor ? ['id' => $impostor->id, 'name' => $impostor->name] : null,
                'outcome' => $r->outcome,
            ];
        })->all();

        return [
            'rounds' => $roundsPayload,
            'players' => $statsArr,
            'best_impostor' => $bestImpostor,
            'best_detective' => $bestDetective,
            'most_suspicious' => $mostSuspicious,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $stats
     * @return ?array{id:int, name:string, successful_bluffs:int, times_as_impostor:int}
     */
    private function pickBestImpostor(array $stats): ?array
    {
        $candidates = array_filter($stats, fn ($s) => $s['times_as_impostor'] > 0);
        if (empty($candidates)) {
            return null;
        }

        usort($candidates, function ($a, $b) {
            // higher successful_bluffs first
            if ($a['successful_bluffs'] !== $b['successful_bluffs']) {
                return $b['successful_bluffs'] <=> $a['successful_bluffs'];
            }
            // tiebreak: lower votes_received_as_impostor (better blending)
            if ($a['votes_received_as_impostor'] !== $b['votes_received_as_impostor']) {
                return $a['votes_received_as_impostor'] <=> $b['votes_received_as_impostor'];
            }

            // tiebreak: more rounds as impostor
            return $b['times_as_impostor'] <=> $a['times_as_impostor'];
        });

        $top = $candidates[0];
        if ($top['successful_bluffs'] === 0) {
            return null;
        }

        return [
            'id' => $top['id'],
            'name' => $top['name'],
            'successful_bluffs' => $top['successful_bluffs'],
            'times_as_impostor' => $top['times_as_impostor'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $stats
     * @return ?array{id:int, name:string, correct_votes:int}
     */
    private function pickBestDetective(array $stats): ?array
    {
        $candidates = array_filter($stats, fn ($s) => $s['correct_votes'] > 0);
        if (empty($candidates)) {
            return null;
        }

        usort($candidates, function ($a, $b) {
            if ($a['correct_votes'] !== $b['correct_votes']) {
                return $b['correct_votes'] <=> $a['correct_votes'];
            }

            // tiebreak: fewer total_votes_cast (more efficient)
            return $a['total_votes_cast'] <=> $b['total_votes_cast'];
        });

        $top = $candidates[0];

        return [
            'id' => $top['id'],
            'name' => $top['name'],
            'correct_votes' => $top['correct_votes'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $stats
     * @return ?array{id:int, name:string, votes_received_as_innocent:int}
     */
    private function pickMostSuspicious(array $stats): ?array
    {
        $candidates = array_filter($stats, fn ($s) => $s['votes_received_as_innocent'] > 0);
        if (empty($candidates)) {
            return null;
        }

        usort($candidates, fn ($a, $b) => $b['votes_received_as_innocent'] <=> $a['votes_received_as_innocent']);

        $top = $candidates[0];

        return [
            'id' => $top['id'],
            'name' => $top['name'],
            'votes_received_as_innocent' => $top['votes_received_as_innocent'],
        ];
    }

    /**
     * Pick category, words, an impostor, and apply word assignments + reset per-round pivot state.
     */
    private function assignRound(Room $room, Collection $players): void
    {
        $category = WordPack::query()
            ->select('category')
            ->distinct()
            ->inRandomOrder()
            ->value('category');

        $realWord = WordPack::where('category', $category)
            ->inRandomOrder()
            ->value('word');

        if (! $realWord) {
            throw ValidationException::withMessages([
                'word_packs' => 'Selected category does not have any words.',
            ]);
        }

        DB::transaction(function () use ($room, $players, $category, $realWord) {
            $impostor = $players->random();

            $room->update([
                'category' => $category,
                'word' => $realWord,
                'impostor_word' => null,
                'status' => 'playing',
            ]);

            foreach ($players as $player) {
                $isImpostor = $player->id === $impostor->id;

                $room->players()->updateExistingPivot($player->id, [
                    'is_impostor' => $isImpostor,
                    'assigned_word' => $isImpostor ? null : $realWord,
                    'is_eliminated' => false,
                    'vote_count' => 0,
                ]);
            }
        });
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (Room::where('code', $code)->exists());

        return $code;
    }
}
