<?php

namespace App\Http\Controllers\Music;

use App\Http\Controllers\Controller;
use App\Http\Requests\Music\StoreSetlistRequest;
use App\Http\Requests\Music\UpdateSetlistRequest;
use App\Models\Setlist;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SetlistController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('music/setlists/Index', [
            'setlists' => Setlist::query()
                ->withCount('songs')
                ->latest('service_date')
                ->get(['id', 'title', 'service_date', 'theme', 'status']),
        ]);
    }

    public function show(Setlist $setlist): Response
    {
        return Inertia::render('music/setlists/Show', [
            'setlist' => [
                ...$setlist->only(['id', 'title', 'service_date', 'theme', 'notes', 'status']),
                'songs' => $setlist->songs()
                    ->get()
                    ->map(fn ($song) => [
                        'id' => $song->id,
                        'title' => $song->title,
                        'artist' => $song->artist,
                        'composer' => $song->composer,
                        'original_key' => $song->original_key,
                        'tempo' => $song->tempo,
                        'time_signature' => $song->time_signature,
                        'lyrics_with_chords' => $song->lyrics_with_chords,
                        'video_link' => $song->video_link,
                        'notes' => $song->notes,
                        'is_active' => $song->is_active,
                        'pivot_order' => $song->pivot->order,
                        'pivot_key_override' => $song->pivot->key_override,
                        'pivot_notes' => $song->pivot->notes,
                    ]),
            ],
            'availableSongs' => Song::query()
                ->where('is_active', true)
                ->orderBy('title')
                ->get(['id', 'title', 'artist', 'original_key']),
        ]);
    }

    public function live(Setlist $setlist): Response
    {
        return Inertia::render('music/setlists/Live', [
            'setlist' => [
                ...$setlist->only(['id', 'title', 'service_date', 'theme']),
                'songs' => $setlist->songs()
                    ->get()
                    ->map(fn ($song) => [
                        'id' => $song->id,
                        'title' => $song->title,
                        'artist' => $song->artist,
                        'original_key' => $song->original_key,
                        'display_key' => $song->pivot->key_override ?? $song->original_key,
                        'lyrics_with_chords' => $song->lyrics_with_chords,
                        'tempo' => $song->tempo,
                        'pivot_notes' => $song->pivot->notes,
                    ]),
            ],
        ]);
    }

    public function store(StoreSetlistRequest $request): RedirectResponse
    {
        $setlist = Setlist::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return to_route('music.setlists.show', $setlist);
    }

    public function update(UpdateSetlistRequest $request, Setlist $setlist): RedirectResponse
    {
        $setlist->update($request->validated());

        return to_route('music.setlists.show', $setlist);
    }

    public function destroy(Setlist $setlist): RedirectResponse
    {
        $setlist->delete();

        return to_route('music.setlists.index');
    }
}
