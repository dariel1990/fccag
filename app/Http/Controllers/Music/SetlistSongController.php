<?php

namespace App\Http\Controllers\Music;

use App\Http\Controllers\Controller;
use App\Http\Requests\Music\StoreSetlistSongRequest;
use App\Models\Setlist;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SetlistSongController extends Controller
{
    public function store(StoreSetlistSongRequest $request, Setlist $setlist): RedirectResponse
    {
        $validated = $request->validated();

        $maxOrder = $setlist->songs()->max('setlist_songs.order') ?? -1;

        $setlist->songs()->attach($validated['song_id'], [
            'order' => $validated['order'] ?? ($maxOrder + 1),
            'key_override' => $validated['key_override'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return to_route('music.setlists.show', $setlist);
    }

    public function update(Request $request, Setlist $setlist, Song $song): RedirectResponse
    {
        $request->validate([
            'key_override' => ['nullable', 'string', 'in:C,C#,Db,D,D#,Eb,E,F,F#,Gb,G,G#,Ab,A,A#,Bb,B'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $setlist->songs()->updateExistingPivot($song->id, [
            'key_override' => $request->key_override,
            'notes' => $request->notes,
        ]);

        return to_route('music.setlists.show', $setlist);
    }

    public function destroy(Setlist $setlist, Song $song): RedirectResponse
    {
        $setlist->songs()->detach($song->id);

        return to_route('music.setlists.show', $setlist);
    }

    public function reorder(Request $request, Setlist $setlist): RedirectResponse
    {
        $request->validate([
            'songs' => ['required', 'array'],
            'songs.*.id' => ['required', 'integer', 'exists:songs,id'],
            'songs.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->songs as $item) {
            $setlist->songs()->updateExistingPivot($item['id'], ['order' => $item['order']]);
        }

        return to_route('music.setlists.show', $setlist);
    }
}
