<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classification extends Model
{
    /** @use HasFactory<\Database\Factories\ClassificationFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    public function people(): HasMany
    {
        return $this->hasMany(Participant::class, 'classification_id');
    }
}
