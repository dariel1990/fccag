<?php

namespace Tests\Feature\Music;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\MusicMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MusicMemberControllerTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->withPermissions([
            Module::MusicMembers->value => [
                PermissionAction::Read->value,
                PermissionAction::Create->value,
                PermissionAction::Update->value,
                PermissionAction::Delete->value,
            ],
        ])->create();
    }

    public function test_index_renders(): void
    {
        $user = $this->user();
        MusicMember::factory(2)->create();

        $this->actingAs($user)
            ->get(route('music.music-members.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('music/music-members/Index')->has('members', 2));
    }

    public function test_store_creates_member(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->post(route('music.music-members.store'), [
                'name' => 'Algene',
                'instruments' => 'Keys',
                'is_active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('music_members', ['name' => 'Algene']);
    }

    public function test_update_updates_member(): void
    {
        $user = $this->user();
        $member = MusicMember::factory()->create(['name' => 'Old Name']);

        $this->actingAs($user)
            ->put(route('music.music-members.update', $member), [
                'name' => 'New Name',
                'is_active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('music_members', ['id' => $member->id, 'name' => 'New Name']);
    }

    public function test_destroy_deletes_member(): void
    {
        $user = $this->user();
        $member = MusicMember::factory()->create();

        $this->actingAs($user)
            ->delete(route('music.music-members.destroy', $member))
            ->assertRedirect();

        $this->assertDatabaseMissing('music_members', ['id' => $member->id]);
    }
}
