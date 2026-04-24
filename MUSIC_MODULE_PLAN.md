# Music Module — Implementation Plan

**Stack:** Laravel 12 · Inertia v2 · Vue 3 · Tailwind CSS v4 · Wayfinder · shadcn/ui  
**Module prefix:** `/music` | route name prefix: `music.`  
**Codebase reference branch:** `main`

---

## 1. How It Fits Into the Existing System

### Permissions
Add two new cases to `app/Enums/Module.php`:
```php
case Songs    = 'songs';
case Setlists = 'setlists';
```

Route protection uses the existing `permission` middleware:
```php
Route::prefix('music')->name('music.')->group(function (): void {
    Route::middleware('permission:songs')->group(...);
    Route::middleware('permission:setlists')->group(...);
});
```

### Navigation
In `resources/js/components/AppSidebar.vue`, add a new nav group (or sub-items to an existing one):
```typescript
{
    label: 'Music',
    items: [
        { title: 'Songs',     href: songsIndex().url,     icon: Music,     permission: 'songs' },
        { title: 'Setlists',  href: setlistsIndex().url,  icon: ListMusic,  permission: 'setlists' },
    ],
}
```

### Policies
Two policy classes following the existing `MinistryPolicy` pattern:
- `app/Policies/SongPolicy.php`
- `app/Policies/SetlistPolicy.php`

---

## 2. Complete User Flow

### A. Adding a Song with Chords & Lyrics

1. User navigates to **Music → Songs → + New Song** (opens `SongFormDialog`)
2. Fills in: Title, Artist, Original Key (dropdown: C–B chromatic), Tempo (BPM), Time Signature, Lyrics with Chords
3. **Chord notation format:** inline brackets — `[G]Amazing [Em]grace [C]how [G]sweet`
4. Save → `POST /music/songs` → redirects to `Songs.Index` with the new song visible

### B. Viewing & Transposing Keys

1. From `Songs.Index`, click a song → `Songs.Show`
2. Song renders with `ChordDisplay` component: chords appear above the lyric syllable they sit on
3. Key selector dropdown visible at top: current key highlighted
4. User selects a new key → **client-side transposition only** (no server call)
   - Transposition delta = (target key index − original key index + 12) % 12
   - All chords in the song are shifted by that delta
5. Selected display key is stored in component state (not persisted unless user explicitly saves a "preferred key" to the setlist entry)

### C. Creating a Setlist

1. User navigates to **Music → Setlists → + New Setlist** (opens `SetlistFormDialog`)
2. Fills in: Title, Service Date, Theme, Notes, Status (Draft/Published)
3. Save → redirects to `Setlists.Show`
4. On `Setlists.Show`: click **Add Song** → song picker modal (search + filter by key)
5. Each added song gets an entry in `setlist_songs` with `order` and optional `key_override`
6. Drag-and-drop reordering (using Vue's `@vueform/multiselect` style or a lightweight drag library)
7. Per-song key override: small key selector inline on each row

### D. Live Service Performance Mode

1. From `Setlists.Show`, click **Start Service** → opens `Setlists.Live` (full-screen)
2. Sidebar collapses; app shell hidden; only song content + navigation controls visible
3. Left/Right arrow (keyboard) or swipe (mobile) to move between songs in the setlist
4. Active song renders full `ChordDisplay` with large readable text
5. Key can still be changed per-song mid-service
6. **Exit** button returns to `Setlists.Show`

---

## 3. UI Structure

### Pages (in `resources/js/pages/music/`)

```
pages/music/
├── songs/
│   ├── Index.vue      ← List + search + filter by key; SongFormDialog for create/edit
│   └── Show.vue       ← Full song view with ChordDisplay + key transposer
├── setlists/
│   ├── Index.vue      ← List of setlists; SetlistFormDialog for create/edit
│   ├── Show.vue       ← Setlist detail: song list, reorder, per-song key override
│   └── Live.vue       ← Full-screen performance mode
```

### Components (in `resources/js/components/music/`)

| Component | Purpose |
|---|---|
| `SongFormDialog.vue` | Create/edit song — follows `ActivityTypeFormDialog` pattern exactly |
| `SetlistFormDialog.vue` | Create/edit setlist header |
| `ChordDisplay.vue` | Parses `[Chord]lyrics` notation → renders chord above lyric |
| `KeyTransposer.vue` | Dropdown to select displayed key; emits `key-change` event |
| `SetlistSongRow.vue` | Single row in setlist: song title, key override, drag handle, remove |
| `SongPickerDialog.vue` | Search + select songs to add to setlist |
| `PerformanceNav.vue` | Prev/Next controls + song counter for Live mode |

### Key Screen Descriptions

**`songs/Index.vue`**
- Search bar (title/artist)
- Filter by key (chip buttons: All, C, D, E, F, G, A, B + sharps)
- Table: Title, Artist, Key, Actions (Edit, Delete, View)
- "+ New Song" button top-right

**`songs/Show.vue`**
- Breadcrumb: Songs → Song Title
- Header: Title, Artist, BPM, Time Sig
- `KeyTransposer` sticky at top of chord sheet
- `ChordDisplay` renders full chord sheet
- Edit button (opens SongFormDialog)

**`setlists/Show.vue`**
- Setlist metadata (date, theme, status badge)
- Song list with `SetlistSongRow` (drag handle, key override, remove)
- "+ Add Song" button → `SongPickerDialog`
- "Start Service ▶" button → navigates to `Live`

**`setlists/Live.vue`**
- Fullscreen, dark background preferred
- Song title + current position (e.g., "3 / 8")
- `ChordDisplay` full width, large font
- `KeyTransposer` accessible but unobtrusive
- Keyboard: ← → for prev/next song; `Esc` to exit
- Touch: swipe left/right

---

## 4. Database Design

### Migrations (create in order)

#### `create_songs_table`
```php
Schema::create('songs', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
    $table->string('title');
    $table->string('artist')->nullable();
    $table->string('original_key', 3);       // C, C#, Db, D, ... B
    $table->integer('tempo')->nullable();     // BPM
    $table->string('time_signature', 10)->nullable(); // '4/4', '3/4', '6/8'
    $table->longText('lyrics_with_chords'); // [G]Amazing [Em]grace...
    $table->text('notes')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### `create_setlists_table`
```php
Schema::create('setlists', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
    $table->string('title');
    $table->date('service_date');
    $table->string('theme')->nullable();
    $table->text('notes')->nullable();
    $table->string('status', 20)->default('draft'); // draft | published | archived
    $table->timestamps();

    $table->index('service_date');
});
```

#### `create_setlist_songs_table`
```php
Schema::create('setlist_songs', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('setlist_id')->constrained()->cascadeOnDelete();
    $table->foreignId('song_id')->constrained()->cascadeOnDelete();
    $table->unsignedSmallInteger('order')->default(0);
    $table->string('key_override', 3)->nullable(); // overrides song's original_key
    $table->string('notes')->nullable();           // e.g. "skip chorus 2"
    $table->timestamps();

    $table->unique(['setlist_id', 'song_id']);
    $table->index(['setlist_id', 'order']);
});
```

### Relationships Summary

```
users ──< songs           (created_by)
users ──< setlists        (created_by)
setlists >──< songs       via setlist_songs
                          pivot: order, key_override, notes
```

### Models

**`Song`**
```php
protected $fillable = ['created_by', 'title', 'artist', 'original_key',
                        'tempo', 'time_signature', 'lyrics_with_chords', 'notes', 'is_active'];

protected function casts(): array {
    return ['is_active' => 'boolean', 'tempo' => 'integer'];
}

public function setlists(): BelongsToMany { ... via SetlistSong pivot }
public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
```

**`Setlist`**
```php
protected $fillable = ['created_by', 'title', 'service_date', 'theme', 'notes', 'status'];

protected function casts(): array {
    return ['service_date' => 'date'];
}

public function songs(): BelongsToMany {
    return $this->belongsToMany(Song::class, 'setlist_songs')
        ->withPivot(['order', 'key_override', 'notes'])
        ->orderBy('setlist_songs.order')
        ->withTimestamps();
}
public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
```

---

## 5. Backend Logic

### Controllers (in `app/Http/Controllers/Music/`)

#### `SongController`
```
index()   → Inertia::render('music/songs/Index', songs paginated + key filter)
show()    → Inertia::render('music/songs/Show', song)
store()   → StoreSongRequest → Song::create() → to_route('music.songs.index')
update()  → UpdateSongRequest → $song->update() → to_route('music.songs.index')
destroy() → $song->delete() → to_route('music.songs.index')
```

#### `SetlistController`
```
index()   → Inertia::render('music/setlists/Index', setlists with song count)
show()    → Inertia::render('music/setlists/Show', setlist with ordered songs + pivot)
live()    → Inertia::render('music/setlists/Live', setlist with full song data)
store()   → StoreSetlistRequest → Setlist::create() → to_route('music.setlists.show', $setlist)
update()  → UpdateSetlistRequest → $setlist->update() → to_route('music.setlists.show', $setlist)
destroy() → $setlist->delete() → to_route('music.setlists.index')
```

#### `SetlistSongController`
```
store()   → attach song to setlist with order + key_override
update()  → update pivot (key_override, notes)
destroy() → detach song from setlist
reorder() → PATCH /music/setlists/{setlist}/songs/reorder → accepts [{id, order}] array
```

### Routes (`routes/web.php` addition)
```php
Route::prefix('music')->name('music.')->group(function (): void {
    Route::middleware('permission:songs')->group(function (): void {
        Route::resource('songs', Music\SongController::class)->except(['create', 'edit']);
    });

    Route::middleware('permission:setlists')->group(function (): void {
        Route::resource('setlists', Music\SetlistController::class)->except(['create', 'edit']);
        Route::get('setlists/{setlist}/live', [Music\SetlistController::class, 'live'])->name('setlists.live');
        Route::post('setlists/{setlist}/songs', [Music\SetlistSongController::class, 'store'])->name('setlist-songs.store');
        Route::patch('setlists/{setlist}/songs/{song}', [Music\SetlistSongController::class, 'update'])->name('setlist-songs.update');
        Route::delete('setlists/{setlist}/songs/{song}', [Music\SetlistSongController::class, 'destroy'])->name('setlist-songs.destroy');
        Route::patch('setlists/{setlist}/songs/reorder', [Music\SetlistSongController::class, 'reorder'])->name('setlist-songs.reorder');
    });
});
```

### Form Requests
- `StoreSongRequest` / `UpdateSongRequest` — validate title, original_key (enum), tempo (int), lyrics_with_chords (required string)
- `StoreSetlistRequest` / `UpdateSetlistRequest` — validate title, service_date (date), status (in:draft,published,archived)
- `StoreSetlistSongRequest` — validate song_id (exists), key_override (nullable, in chromatic scale), order

### Key Transposition Logic (Client-side Vue composable)

**`resources/js/composables/useChordTransposer.ts`**
```typescript
const CHROMATIC = ['C','C#','D','D#','E','F','F#','G','G#','A','A#','B'];
// Also handle flats: Db=C#, Eb=D#, Gb=F#, Ab=G#, Bb=A#

function transposeChord(chord: string, semitones: number): string { ... }
function transposeLyrics(lyrics: string, fromKey: string, toKey: string): string {
    const delta = (CHROMATIC.indexOf(toKey) - CHROMATIC.indexOf(fromKey) + 12) % 12;
    return lyrics.replace(/\[([^\]]+)\]/g, (_, chord) => `[${transposeChord(chord, delta)}]`);
}
```

Used in `ChordDisplay.vue` — receives `originalKey`, `displayKey`, `lyricsWithChords` as props.

---

## 6. Real-World Usage Considerations

### Live Service Mode
- `Setlists.Live` hides the entire app shell (nav, sidebar) — pass a `layout: false` or use a dedicated `LiveLayout`
- Font size: large default (`text-2xl` for lyrics, `text-sm` for chords above), with a font-size toggle (+/-)
- **Keyboard shortcuts:** `ArrowLeft`/`ArrowRight` for prev/next, `Escape` to exit, number keys 1-9 to jump to song
- Keep screen awake: use the `navigator.wakeLock` API (Web API, no package needed)
- Auto-scroll option: a slow scroll for long songs (optional toggle)

### Mobile Responsiveness
- `songs/Index.vue`: table collapses to card layout on mobile
- `songs/Show.vue`: chord sheet scrolls vertically; KeyTransposer stays sticky at top
- `setlists/Live.vue`: swipe gestures (Vue touch events or `@vueuse/core` `useSwipe`)
- Font size in Live mode: bigger defaults on mobile (e.g., `text-3xl`)

### Offline Fallback
- Full offline is complex; recommend a **pragmatic approach:** 
  - On `Setlists.Show`, add an "Export to PDF" button → server-side PDF generation (Laravel DomPDF) with all songs + chords pre-transposed
  - This gives worship leaders a printable/offline backup
  - No PWA/service worker complexity needed at this stage

### Performance
- Songs index: paginate (15/page) + search debounce
- `Setlists.Live` eager loads all songs with `lyrics_with_chords` in one query — no lazy loading mid-service
- Chord transposition is pure JS — no network calls; instantaneous

---

## Implementation Phases

### Phase 0: Documentation Discovery ✅ (completed above)
- Confirmed Module enum pattern, permission middleware, route prefix pattern (SI module)
- Confirmed shadcn dialog CRUD pattern from ActivityTypeFormDialog
- Confirmed Vue page structure and Wayfinder route import pattern
- Confirmed migration/model/factory/seeder patterns

### Phase 1: Database & Backend Foundation
**Files to create:**
1. `php artisan make:migration create_songs_table`
2. `php artisan make:migration create_setlists_table`
3. `php artisan make:migration create_setlist_songs_table`
4. `php artisan make:model Song --factory`
5. `php artisan make:model Setlist --factory`
6. `app/Enums/Module.php` — add `Songs` and `Setlists` cases
7. `app/Policies/SongPolicy.php` (copy MinistryPolicy pattern)
8. `app/Policies/SetlistPolicy.php`

**Verify:** `php artisan migrate` succeeds; `php artisan test --compact` green.

### Phase 2: Controllers & Routes
**Files to create:**
1. `php artisan make:request Music/StoreSongRequest`
2. `php artisan make:request Music/UpdateSongRequest`
3. `php artisan make:request Music/StoreSetlistRequest`
4. `php artisan make:request Music/UpdateSetlistRequest`
5. `php artisan make:request Music/StoreSetlistSongRequest`
6. `php artisan make:controller Music/SongController`
7. `php artisan make:controller Music/SetlistController`
8. `php artisan make:controller Music/SetlistSongController`
9. Edit `routes/web.php` — add music route group

**Verify:** `php artisan route:list --name=music` shows all expected routes.  
**Run Pint:** `vendor/bin/pint --dirty --format agent`

### Phase 3: Vue Pages & Components (Songs)
**Files to create:**
1. `resources/js/composables/useChordTransposer.ts` — transposition logic + tests
2. `resources/js/components/music/ChordDisplay.vue` — renders `[Chord]lyrics` notation
3. `resources/js/components/music/KeyTransposer.vue` — key selector dropdown
4. `resources/js/components/music/SongFormDialog.vue` — create/edit (copy ActivityTypeFormDialog)
5. `resources/js/pages/music/songs/Index.vue` — list + search + filter
6. `resources/js/pages/music/songs/Show.vue` — chord sheet view

**Verify:** Create a song via the UI; verify chords transpose correctly client-side.

### Phase 4: Vue Pages & Components (Setlists)
**Files to create:**
1. `resources/js/components/music/SetlistFormDialog.vue`
2. `resources/js/components/music/SongPickerDialog.vue`
3. `resources/js/components/music/SetlistSongRow.vue`
4. `resources/js/pages/music/setlists/Index.vue`
5. `resources/js/pages/music/setlists/Show.vue`

**Verify:** Create a setlist; add songs; reorder; change per-song key override.

### Phase 5: Live Performance Mode
**Files to create:**
1. `resources/js/components/music/PerformanceNav.vue`
2. `resources/js/pages/music/setlists/Live.vue`
   - Dedicated full-screen layout (no app shell)
   - Keyboard navigation
   - `navigator.wakeLock` to keep screen on
   - Swipe support via `@vueuse/core`

**Verify:** Navigate through a full setlist in Live mode; verify keyboard shortcuts work; test on mobile viewport.

### Phase 6: Navigation & Permissions
1. Add `Songs`/`Setlists` cases to `app/Enums/Module.php`
2. Add nav items to `AppSidebar.vue`
3. Update `UserPermissions` value object if needed to recognize new modules
4. Add music permissions to superadmin seeder or admin assignment UI

**Verify:** Nav items appear only for users with correct permissions.

### Phase 7: Tests
Following the existing PHPUnit pattern:
- `tests/Feature/Music/SongControllerTest.php` — CRUD, auth, permission gate
- `tests/Feature/Music/SetlistControllerTest.php` — CRUD, adding songs, reorder
- `tests/Unit/Music/ChordTransposerTest.php` — transposition edge cases (sharps, flats, minor chords)

**Run:** `php artisan test --compact tests/Feature/Music/ tests/Unit/Music/`

---

## Anti-Patterns to Avoid

- **Don't** store pre-transposed chord sheets — transpose client-side only
- **Don't** use Policies inside `authorize()` in Form Requests — the middleware handles module-level access; policies handle record-level access
- **Don't** paginate `Setlists.Live` — load all songs eagerly before the service starts
- **Don't** use `DB::` for pivot operations — use `$setlist->songs()->attach/sync/updateExistingPivot()`
- **Don't** skip Wayfinder — all route references in Vue use `@/actions/App/Http/Controllers/Music/...`
