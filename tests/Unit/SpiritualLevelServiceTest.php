<?php

namespace Tests\Unit;

use App\Enums\SpiritualLevel;
use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Attendance;
use App\Models\Participant;
use App\Services\SpiritualLevelService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpiritualLevelServiceTest extends TestCase
{
    use RefreshDatabase;

    private SpiritualLevelService $service;

    private ActivityType $activityType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SpiritualLevelService;
        $this->activityType = ActivityType::factory()->create();
    }

    public function test_get_quarter_dates_returns_correct_dates(): void
    {
        $dates = $this->service->getQuarterDates(2024, 1);

        $this->assertEquals('2024-01-01', $dates['start']->format('Y-m-d'));
        $this->assertEquals('2024-03-31', $dates['end']->format('Y-m-d'));
    }

    public function test_get_quarter_dates_for_q2(): void
    {
        $dates = $this->service->getQuarterDates(2024, 2);

        $this->assertEquals('2024-04-01', $dates['start']->format('Y-m-d'));
        $this->assertEquals('2024-06-30', $dates['end']->format('Y-m-d'));
    }

    public function test_calculate_attendance_percentage_with_no_activities(): void
    {
        $participant = Participant::factory()->create();

        $percentage = $this->service->calculateAttendancePercentage($participant, 2024, 1);

        $this->assertEquals(0.0, $percentage);
    }

    public function test_calculate_attendance_percentage_with_perfect_attendance(): void
    {
        $participant = Participant::factory()->create();
        $activity = Activity::factory()->create([
            'activity_type_id' => $this->activityType->id,
            'activity_date' => '2024-01-15',
        ]);
        Attendance::factory()->create([
            'person_id' => $participant->id,
            'activity_id' => $activity->id,
            'is_present' => true,
        ]);

        $percentage = $this->service->calculateAttendancePercentage($participant, 2024, 1);

        $this->assertEquals(100.0, $percentage);
    }

    public function test_calculate_attendance_percentage_with_partial_attendance(): void
    {
        $participant = Participant::factory()->create();

        $activity1 = Activity::factory()->create([
            'activity_type_id' => $this->activityType->id,
            'activity_date' => '2024-01-10',
        ]);
        \App\Models\Attendance::factory()->create([
            'person_id' => $participant->id,
            'activity_id' => $activity1->id,
            'is_present' => true,
        ]);

        $activity2 = Activity::factory()->create([
            'activity_type_id' => $this->activityType->id,
            'activity_date' => '2024-01-20',
        ]);
        \App\Models\Attendance::factory()->create([
            'person_id' => $participant->id,
            'activity_id' => $activity2->id,
            'is_present' => false,
        ]);

        $percentage = $this->service->calculateAttendancePercentage($participant, 2024, 1);

        $this->assertEquals(50.0, $percentage);
    }

    public function test_get_spiritual_level_returns_mature_for_90_percent(): void
    {
        $participant = Participant::factory()->create();

        for ($i = 0; $i < 10; $i++) {
            $activity = Activity::factory()->create([
                'activity_type_id' => $this->activityType->id,
                'activity_date' => CarbonImmutable::create(2024, 1, $i + 1),
            ]);
            Attendance::factory()->create([
                'person_id' => $participant->id,
                'activity_id' => $activity->id,
                'is_present' => $i < 9,
            ]);
        }

        $level = $this->service->getSpiritualLevel($participant, 2024, 1);

        $this->assertEquals(SpiritualLevel::SpirituallyMature, $level);
    }

    public function test_get_spiritual_level_returns_growing_for_75_percent(): void
    {
        $participant = Participant::factory()->create();

        for ($i = 0; $i < 4; $i++) {
            $activity = Activity::factory()->create([
                'activity_type_id' => $this->activityType->id,
                'activity_date' => CarbonImmutable::create(2024, 1, $i + 1),
            ]);
            Attendance::factory()->create([
                'person_id' => $participant->id,
                'activity_id' => $activity->id,
                'is_present' => $i < 3,
            ]);
        }

        $level = $this->service->getSpiritualLevel($participant, 2024, 1);

        $this->assertEquals(SpiritualLevel::Growing, $level);
    }

    public function test_get_spiritual_level_returns_developing_for_60_percent(): void
    {
        $participant = Participant::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $activity = Activity::factory()->create([
                'activity_type_id' => $this->activityType->id,
                'activity_date' => CarbonImmutable::create(2024, 1, $i + 1),
            ]);
            Attendance::factory()->create([
                'person_id' => $participant->id,
                'activity_id' => $activity->id,
                'is_present' => $i < 3,
            ]);
        }

        $level = $this->service->getSpiritualLevel($participant, 2024, 1);

        $this->assertEquals(SpiritualLevel::Developing, $level);
    }

    public function test_get_spiritual_level_returns_needs_guidance_for_below_50(): void
    {
        $participant = Participant::factory()->create();

        for ($i = 0; $i < 4; $i++) {
            $activity = Activity::factory()->create([
                'activity_type_id' => $this->activityType->id,
                'activity_date' => CarbonImmutable::create(2024, 1, $i + 1),
            ]);
            Attendance::factory()->create([
                'person_id' => $participant->id,
                'activity_id' => $activity->id,
                'is_present' => false,
            ]);
        }

        $level = $this->service->getSpiritualLevel($participant, 2024, 1);

        $this->assertEquals(SpiritualLevel::NeedsGuidance, $level);
    }

    public function test_generate_quarterly_report_returns_correct_data(): void
    {
        $participant = Participant::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $activity = Activity::factory()->create([
            'activity_type_id' => $this->activityType->id,
            'activity_date' => '2024-01-15',
        ]);
        Attendance::factory()->create([
            'person_id' => $participant->id,
            'activity_id' => $activity->id,
            'is_present' => true,
        ]);

        $report = $this->service->generateQuarterlyReport(2024, 1);

        $this->assertCount(1, $report);
        $this->assertEquals('John Doe', $report[0]['full_name']);
        $this->assertEquals(1, $report[0]['total_activities']);
        $this->assertEquals(1, $report[0]['attended']);
        $this->assertEquals(100.0, $report[0]['percentage']);
        $this->assertEquals('Spiritually Mature', $report[0]['spiritual_level']);
    }

    public function test_get_quarterly_summary_returns_correct_counts(): void
    {
        Participant::factory()->create(['is_active' => true]);
        Participant::factory()->create(['is_active' => true]);
        Participant::factory()->create(['is_active' => false]);

        $summary = $this->service->getQuarterlySummary(2024, 1);

        $this->assertEquals(2, $summary['total_participants']);
    }
}
