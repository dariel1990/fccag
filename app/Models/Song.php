<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Song extends Model
{
    /** @use HasFactory<\Database\Factories\SongFactory> */
    use HasFactory;

    protected $fillable = [
        'created_by',
        'title',
        'artist',
        'original_key',
        'tempo',
        'time_signature',
        'lyrics_with_chords',
        'composer',
        'video_link',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'tempo' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function setlists(): BelongsToMany
    {
        return $this->belongsToMany(Setlist::class, 'setlist_songs')
            ->withPivot(['order', 'key_override', 'notes'])
            ->withTimestamps();
    }
}
