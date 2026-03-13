<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_displays_participants(): void
    {
        Participant::factory(5)->create();

        $response = $this->actingAs($this->user)
            ->get(route('participants.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('participants/Index')
                ->has('participants', 5)
            );
    }

    public function test_create_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('participants.create'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('participants/Create')
            );
    }

    public function test_participant_can_be_created(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('participants.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'gender' => 'male',
                'birthday' => '1990-01-01',
                'contact_number' => '1234567890',
                'address' => '123 Main St',
                'date_joined' => '2024-01-01',
                'is_active' => true,
            ]);

        $response->assertRedirect(route('participants.index'));

        $this->assertDatabaseHas('people', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'male',
        ]);
    }

    public function test_participant_requires_first_name(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('participants.store'), [
                'first_name' => '',
                'last_name' => 'Doe',
                'gender' => 'male',
                'date_joined' => '2024-01-01',
            ]);

        $response->assertSessionHasErrors('first_name');
    }

    public function test_participant_requires_valid_gender(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('participants.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'gender' => 'invalid',
                'date_joined' => '2024-01-01',
            ]);

        $response->assertSessionHasErrors('gender');
    }

    public function test_show_page_displays_participant(): void
    {
        $participant = Participant::factory()->create();
        $activity = Activity::factory()->create();
        Attendance::factory()->create([
            'person_id' => $participant->id,
            'activity_id' => $activity->id,
            'is_present' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('participants.show', $participant));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('participants/Show')
                ->where('participant.id', $participant->id)
                ->has('attendanceHistory')
            );
    }

    public function test_show_returns_json_for_ajax_request(): void
    {
        $participant = Participant::factory()->create();
        $activity = Activity::factory()->create();
        Attendance::factory()->create([
            'person_id' => $participant->id,
            'activity_id' => $activity->id,
            'is_present' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('participants.show', $participant));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'full_name', 'gender'],
                'attendance_history',
            ])
            ->assertJsonPath('data.id', $participant->id);
    }

    public function test_edit_page_is_displayed(): void
    {
        $participant = Participant::factory()->create();

        $response = $this->actingAs($this->user)
            ->get(route('participants.edit', $participant));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('participants/Edit')
                ->where('participant.id', $participant->id)
            );
    }

    public function test_participant_can_be_updated(): void
    {
        $participant = Participant::factory()->create(['first_name' => 'Old']);

        $response = $this->actingAs($this->user)
            ->put(route('participants.update', $participant), [
                'first_name' => 'New',
                'last_name' => $participant->last_name,
                'gender' => $participant->gender->value,
                'date_joined' => $participant->date_joined->format('Y-m-d'),
                'is_active' => false,
            ]);

        $response->assertRedirect(route('participants.index'));

        $this->assertDatabaseHas('people', [
            'id' => $participant->id,
            'first_name' => 'New',
            'is_active' => false,
        ]);
    }

    public function test_participant_can_be_deleted(): void
    {
        $participant = Participant::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete(route('participants.destroy', $participant));

        $response->assertRedirect(route('participants.index'));

        $this->assertDatabaseMissing('people', [
            'id' => $participant->id,
        ]);
    }

    public function test_deleting_participant_cascades_to_attendances(): void
    {
        $participant = Participant::factory()->create();
        Attendance::factory(3)->create(['person_id' => $participant->id]);

        $this->actingAs($this->user)
            ->delete(route('participants.destroy', $participant));

        $this->assertDatabaseCount('attendances', 0);
    }
}
