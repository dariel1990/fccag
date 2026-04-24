<?php

namespace App\Http\Controllers\Music;

use App\Http\Controllers\Controller;
use App\Http\Requests\Music\StoreSongRequest;
use App\Http\Requests\Music\UpdateSongRequest;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SongController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('music/songs/Index', [
            'songs' => Song::query()
                ->when(request('search'), fn ($q, $s) => $q->where('title', 'like', "%{$s}%")->orWhere('artist', 'like', "%{$s}%"))
                ->when(request('key'), fn ($q, $k) => $q->where('original_key', $k))
                ->where('is_active', true)
                ->latest()
                ->get(['id', 'title', 'artist', 'composer', 'original_key', 'tempo', 'time_signature', 'lyrics_with_chords', 'composer', 'video_link', 'notes', 'is_active']),
            'filters' => request()->only(['search', 'key']),
        ]);
    }

    public function show(Song $song): Response
    {
        return Inertia::render('music/songs/Show', [
            'song' => $song->only(['id', 'title', 'artist', 'composer', 'original_key', 'tempo', 'time_signature', 'lyrics_with_chords', 'video_link', 'notes', 'is_active']),
        ]);
    }

    public function store(StoreSongRequest $request): RedirectResponse
    {
        Song::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return to_route('music.songs.index');
    }

    public function update(UpdateSongRequest $request, Song $song): RedirectResponse
    {
        $song->update($request->validated());

        return redirect()->back();
    }

    public function destroy(Song $song): RedirectResponse
    {
        $song->delete();

        return to_route('music.songs.index');
    }
}
