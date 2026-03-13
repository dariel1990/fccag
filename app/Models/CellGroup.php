<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CellGroup extends Model
{
    /** @use HasFactory<\Database\Factories\CellGroupFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'leader',
        'description',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function people(): HasMany
    {
        return $this->hasMany(Participant::class, 'cell_group_id');
    }
}
