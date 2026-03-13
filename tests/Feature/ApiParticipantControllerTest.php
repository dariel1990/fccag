<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Classification;
use App\Models\Department;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiParticipantControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('mobile-app')->plainTextToken;
    }

    public function test_index_returns_participants_list(): void
    {
        Participant::factory(3)->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson('/api/participants');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'first_name', 'last_name', 'full_name', 'gender', 'is_active'],
                ],
            ]);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/participants');

        $response->assertUnauthorized();
    }

    public function test_index_can_search_by_name(): void
    {
        Participant::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        Participant::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson('/api/participants?search=John');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.first_name', 'John');
    }

    public function test_index_can_filter_by_status(): void
    {
        Participant::factory(2)->create(['is_active' => true]);
        Participant::factory(1)->create(['is_active' => false]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson('/api/participants?is_active=true');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_index_can_filter_by_gender(): void
    {
        Participant::factory(2)->create(['gender' => 'male']);
        Participant::factory(1)->create(['gender' => 'female']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson('/api/participants?gender=female');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_index_can_filter_by_classification(): void
    {
        $classification = Classification::factory()->create();
        Participant::factory(2)->create(['classification_id' => $classification->id]);
        Participant::factory(1)->create(['classification_id' => null]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson("/api/participants?classification_id={$classification->id}");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_index_can_filter_by_department(): void
    {
        $department = Department::factory()->create();
        Participant::factory(2)->create(['department_id' => $department->id]);
        Participant::factory(1)->create(['department_id' => null]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson("/api/participants?department_id={$department->id}");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_index_can_filter_by_birth_month(): void
    {
        Participant::factory()->create(['birthday' => '1990-06-15']);
        Participant::factory()->create(['birthday' => '1985-06-22']);
        Participant::factory()->create(['birthday' => '1992-03-10']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson('/api/participants?birth_month=6');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_index_returns_filter_options(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson('/api/participants');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'filter_options' => [
                    'cell_groups',
                    'classifications',
                    'ministries',
                    'departments',
                ],
            ]);
    }

    public function test_show_returns_participant_with_attendance(): void
    {
        $participant = Participant::factory()->create();
        $activity = Activity::factory()->create();
        Attendance::factory()->create([
            'person_id' => $participant->id,
            'activity_id' => $activity->id,
            'is_present' => true,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson("/api/participants/{$participant->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'first_name', 'last_name', 'full_name', 'gender', 'date_joined', 'is_active'],
                'attendance_history',
            ])
            ->assertJsonPath('data.id', $participant->id)
            ->assertJsonCount(1, 'attendance_history');
    }

    public function test_show_requires_authentication(): void
    {
        $participant = Participant::factory()->create();

        $response = $this->getJson("/api/participants/{$participant->id}");

        $response->assertUnauthorized();
    }

    public function test_show_returns_404_for_nonexistent_participant(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson('/api/participants/99999');

        $response->assertNotFound();
    }

    public function test_store_creates_participant(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/participants', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'male',
            'birthday' => '1990-01-01',
            'contact_number' => '1234567890',
            'address' => '123 Main St',
            'date_joined' => '2024-01-01',
            'is_active' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.first_name', 'John')
            ->assertJsonPath('data.last_name', 'Doe');

        $this->assertDatabaseHas('people', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'male',
        ]);
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/participants', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'male',
            'date_joined' => '2024-01-01',
        ]);

        $response->assertUnauthorized();
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/participants', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'last_name', 'gender', 'date_joined']);
    }

    public function test_store_validates_gender(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/participants', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'invalid',
            'date_joined' => '2024-01-01',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['gender']);
    }

    public function test_update_modifies_participant(): void
    {
        $participant = Participant::factory()->create(['first_name' => 'Old']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->putJson("/api/participants/{$participant->id}", [
            'first_name' => 'New',
            'last_name' => $participant->last_name,
            'gender' => $participant->gender->value,
            'date_joined' => $participant->date_joined->format('Y-m-d'),
            'is_active' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.first_name', 'New');

        $this->assertDatabaseHas('people', [
            'id' => $participant->id,
            'first_name' => 'New',
            'is_active' => false,
        ]);
    }

    public function test_update_requires_authentication(): void
    {
        $participant = Participant::factory()->create();

        $response = $this->putJson("/api/participants/{$participant->id}", [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'gender' => 'male',
            'date_joined' => '2024-01-01',
        ]);

        $response->assertUnauthorized();
    }

    public function test_update_validates_fields(): void
    {
        $participant = Participant::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->putJson("/api/participants/{$participant->id}", [
            'first_name' => '',
            'gender' => 'invalid',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'gender']);
    }

    public function test_destroy_deletes_participant(): void
    {
        $participant = Participant::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->deleteJson("/api/participants/{$participant->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Person of God removed successfully']);

        $this->assertDatabaseMissing('people', [
            'id' => $participant->id,
        ]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $participant = Participant::factory()->create();

        $response = $this->deleteJson("/api/participants/{$participant->id}");

        $response->assertUnauthorized();
    }

    public function test_destroy_cascades_to_attendances(): void
    {
        $participant = Participant::factory()->create();
        Attendance::factory(3)->create(['person_id' => $participant->id]);

        $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->deleteJson("/api/participants/{$participant->id}");

        $this->assertDatabaseMissing('people', ['id' => $participant->id]);
        $this->assertDatabaseCount('attendances', 0);
    }
}
