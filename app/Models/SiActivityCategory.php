<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiActivityCategory extends Model
{
    /** @use HasFactory<\Database\Factories\SiActivityCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'weight',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function siActivities(): HasMany
    {
        return $this->hasMany(SiActivity::class);
    }
}
