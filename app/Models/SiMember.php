<?php

namespace App\Models;

use App\Enums\SiActivityAssessment;
use App\Enums\SiAttendanceStatus;
use App\Enums\SiMemberSex;
use App\Enums\SiMemberStatus;
use App\Enums\SiSpiritualAssessment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiMember extends Model
{
    /** @use HasFactory<\Database\Factories\SiMemberFactory> */
    use HasFactory;

    protected $fillable = [
        'caregiver_id',
        'name',
        'sex',
        'ph_id',
        'address',
        'status',
        'spiritual_assessments',
        'activity_assessments',
        'enrolled_at',
        'exited_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sex' => SiMemberSex::class,
            'status' => SiMemberStatus::class,
            'spiritual_assessments' => 'array',
            'activity_assessments' => 'array',
            'enrolled_at' => 'date',
            'exited_at' => 'date',
        ];
    }

    public function caregiver(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'caregiver_id');
    }

    public function siActivities(): BelongsToMany
    {
        return $this->belongsToMany(SiActivity::class, 'si_activity_member')
            ->withTimestamps();
    }

    public function siAttendances(): HasMany
    {
        return $this->hasMany(SiAttendance::class);
    }

    /**
     * Calculate score for a specific category, optionally scoped to a date range.
     */
    public function categoryScore(int $siActivityCategoryId, ?Carbon $from = null, ?Carbon $to = null): float
    {
        $query = $this->siActivities()
            ->where('si_activity_category_id', $siActivityCategoryId);

        if ($from) {
            $query->where('conducted_at', '>=', $from->startOfDay());
        }

        if ($to) {
            $query->where('conducted_at', '<=', $to->endOfDay());
        }

        $assignedIds = $query->pluck('si_activities.id');

        if ($assignedIds->isEmpty()) {
            return 0.0;
        }

        $assigned = $assignedIds->count();

        $presentStatuses = [
            SiAttendanceStatus::Present->value,
            SiAttendanceStatus::GaveBirth->value,
            SiAttendanceStatus::ChildSick->value,
            SiAttendanceStatus::ChildUnderMedication->value,
        ];

        $present = $this->siAttendances()
            ->whereIn('si_activity_id', $assignedIds)
            ->whereIn('status', $presentStatuses)
            ->count();

        return $present / $assigned;
    }

    /**
     * Calculate overall weighted percentage across all active categories, optionally scoped to a date range.
     */
    public function overallPercentage(?Carbon $from = null, ?Carbon $to = null): float
    {
        $categories = SiActivityCategory::where('is_active', true)->get();
        $total = 0.0;

        foreach ($categories as $category) {
            $total += $this->categoryScore($category->id, $from, $to) * $category->weight;
        }

        return $total;
    }

    /**
     * Star rating (1–5) based on overall percentage, optionally scoped to a date range.
     */
    public function starRating(?Carbon $from = null, ?Carbon $to = null): int
    {
        $pct = $this->overallPercentage($from, $to);

        return match (true) {
            $pct >= 0.90 => 5,
            $pct >= 0.75 => 4,
            $pct >= 0.60 => 3,
            $pct >= 0.45 => 2,
            default => 1,
        };
    }

    /**
     * @return SiSpiritualAssessment[]
     */
    public function spiritualAssessments(): array
    {
        $stored = $this->spiritual_assessments ?? [];

        if (! empty($stored)) {
            return array_map(fn ($v) => SiSpiritualAssessment::from($v), $stored);
        }

        return [SiSpiritualAssessment::fromPercentage($this->overallPercentage() * 100)];
    }

    /**
     * @return SiActivityAssessment[]
     */
    public function activityAssessments(): array
    {
        $stored = $this->activity_assessments ?? [];

        if (! empty($stored)) {
            return array_map(fn ($v) => SiActivityAssessment::from($v), $stored);
        }

        return [SiActivityAssessment::fromPercentage($this->overallPercentage() * 100)];
    }

    public function spiritualAssessment(): SiSpiritualAssessment
    {
        return $this->spiritualAssessments()[0];
    }

    public function activityAssessment(): SiActivityAssessment
    {
        return $this->activityAssessments()[0];
    }
}
