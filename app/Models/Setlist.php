<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Setlist extends Model
{
    /** @use HasFactory<\Database\Factories\SetlistFactory> */
    use HasFactory;

    protected $fillable = [
        'created_by',
        'title',
        'service_date',
        'theme',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, 'setlist_songs')
            ->withPivot(['order', 'key_override', 'notes'])
            ->orderBy('setlist_songs.order')
            ->withTimestamps();
    }
}
