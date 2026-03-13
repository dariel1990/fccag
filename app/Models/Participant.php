<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    /** @use HasFactory<\Database\Factories\ParticipantFactory> */
    use HasFactory;

    protected $table = 'people';

    protected $appends = ['full_name'];

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'birthday',
        'contact_number',
        'address',
        'cell_group_id',
        'classification_id',
        'department_id',
        'date_joined',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'birthday' => 'date',
            'date_joined' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'attendances', 'person_id', 'activity_id')
            ->withPivot(['is_present', 'remarks'])
            ->withTimestamps();
    }

    public function ministries(): BelongsToMany
    {
        return $this->belongsToMany(Ministry::class, 'ministry_person', 'person_id', 'ministry_id')
            ->withTimestamps();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function cellGroup(): BelongsTo
    {
        return $this->belongsTo(CellGroup::class, 'cell_group_id');
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class, 'classification_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
