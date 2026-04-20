<?php

namespace App\Models;

use App\Enums\SiAttendanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiAttendance extends Model
{
    /** @use HasFactory<\Database\Factories\SiAttendanceFactory> */
    use HasFactory;

    protected $fillable = [
        'si_activity_id',
        'si_member_id',
        'status',
        'remarks',
        'recommendation',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SiAttendanceStatus::class,
        ];
    }

    public function siActivity(): BelongsTo
    {
        return $this->belongsTo(SiActivity::class);
    }

    public function siMember(): BelongsTo
    {
        return $this->belongsTo(SiMember::class);
    }
}
