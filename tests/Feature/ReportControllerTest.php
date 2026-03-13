<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Attendance;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ActivityType $activityType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->activityType = ActivityType::factory()->create();
    }

    public function test_quarterly_report_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.quarterly'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('reports/QuarterlyReport')
                ->has('report')
                ->has('summary')
                ->has('filters')
                ->has('availableYears')
            );
    }

    public function test_quarterly_report_with_filters(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.quarterly', ['year' => 2024, 'quarter' => 1]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.year', 2024)
                ->where('filters.quarter', 1)
            );
    }

    public function test_quarterly_report_shows_participant_data(): void
    {
        $participant = Participant::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'is_active' => true,
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

        $response = $this->actingAs($this->user)
            ->get(route('reports.quarterly', ['year' => 2024, 'quarter' => 1]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('report', 1)
                ->where('report.0.full_name', 'John Doe')
                ->where('report.0.attended', 1)
                ->where('report.0.percentage', 100)
            );
    }

    public function test_quarterly_report_summary_is_accurate(): void
    {
        Participant::factory()->create(['is_active' => true]);
        Participant::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.quarterly'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('summary.total_participants', 2)
            );
    }

    public function test_quarterly_report_only_shows_active_participants(): void
    {
        Participant::factory()->create(['is_active' => true]);
        Participant::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.quarterly'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('report', 1)
            );
    }
}
