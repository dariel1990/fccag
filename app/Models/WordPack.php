<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WordPack extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'category',
        'word',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
