<?php

namespace Tests\Feature\Si;

use App\Enums\SiMemberSex;
use App\Enums\SiMemberStatus;
use App\Models\Participant;
use App\Models\SiMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiMemberTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->withFullAccess()->create();
    }

    public function test_index_displays_members(): void
    {
        SiMember::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->get(route('si.members.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('si/members/Index')
                ->has('members', 3)
            );
    }

    public function test_index_includes_caregivers(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('si.members.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('si/members/Index')
                ->has('caregivers')
            );
    }

    public function test_member_can_be_enrolled_with_existing_caregiver(): void
    {
        $caregiver = Participant::factory()->create();

        $response = $this->actingAs($this->user)
            ->post(route('si.members.store'), [
                'caregiver_id' => $caregiver->id,
                'name' => 'Baby Test',
                'sex' => SiMemberSex::Female->value,
                'ph_id' => '999',
                'address' => 'Test Address',
                'status' => SiMemberStatus::Active->value,
                'enrolled_at' => '2025-07-01',
            ]);

        $response->assertRedirect(route('si.members.index'));

        $this->assertDatabaseHas('si_members', [
            'name' => 'Baby Test',
            'caregiver_id' => $caregiver->id,
        ]);
    }

    public function test_member_can_be_enrolled_with_new_caregiver(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('si.members.store'), [
                'caregiver' => [
                    'first_name' => 'Maria',
                    'last_name' => 'Santos',
                ],
                'name' => 'Baby Santos',
                'sex' => SiMemberSex::Male->value,
                'status' => SiMemberStatus::Active->value,
                'enrolled_at' => '2025-07-01',
            ]);

        $response->assertRedirect(route('si.members.index'));

        $this->assertDatabaseHas('si_members', ['name' => 'Baby Santos']);
        $this->assertDatabaseHas('people', ['first_name' => 'Maria', 'last_name' => 'Santos']);
    }

    public function test_member_requires_name_and_sex(): void
    {
        $caregiver = Participant::factory()->create();

        $response = $this->actingAs($this->user)
            ->post(route('si.members.store'), [
                'caregiver_id' => $caregiver->id,
                'enrolled_at' => '2025-07-01',
            ]);

        $response->assertSessionHasErrors(['name', 'sex']);
    }

    public function test_member_details_returns_json(): void
    {
        $member = SiMember::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson(route('si.members.details', $member));

        $response->assertOk()
            ->assertJsonPath('member.id', $member->id)
            ->assertJsonStructure(['member', 'category_scores', 'overall_percentage', 'star_rating', 'activities', 'categories']);
    }

    public function test_member_can_be_updated_and_redirects(): void
    {
        $member = SiMember::factory()->create(['name' => 'Before']);
        $caregiver = Participant::factory()->create();

        $response = $this->actingAs($this->user)
            ->put(route('si.members.update', $member), [
                'caregiver_id' => $caregiver->id,
                'name' => 'After',
                'sex' => SiMemberSex::Female->value,
                'status' => SiMemberStatus::Active->value,
                'enrolled_at' => '2025-07-01',
            ]);

        $response->assertRedirect(route('si.members.index'));
        $this->assertDatabaseHas('si_members', ['id' => $member->id, 'name' => 'After']);
    }

    public function test_member_can_be_updated(): void
    {
        $member = SiMember::factory()->create();
        $caregiver = Participant::factory()->create();

        $response = $this->actingAs($this->user)
            ->put(route('si.members.update', $member), [
                'caregiver_id' => $caregiver->id,
                'name' => 'Updated Name',
                'sex' => SiMemberSex::Male->value,
                'status' => SiMemberStatus::Active->value,
                'enrolled_at' => '2025-07-01',
            ]);

        $response->assertRedirect(route('si.members.index'));

        $this->assertDatabaseHas('si_members', [
            'id' => $member->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_member_can_be_deleted(): void
    {
        $member = SiMember::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete(route('si.members.destroy', $member));

        $response->assertRedirect(route('si.members.index'));

        $this->assertDatabaseMissing('si_members', ['id' => $member->id]);
    }

    public function test_guest_cannot_access_members(): void
    {
        $this->get(route('si.members.index'))->assertRedirect();
    }
}
