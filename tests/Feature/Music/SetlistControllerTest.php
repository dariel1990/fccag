<?php

namespace Tests\Feature\Music;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\Setlist;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetlistControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get(route('music.setlists.index'));

        $response->assertRedirect();
        $response->assertStatus(302);
    }

    public function test_user_without_setlists_permission_gets_403(): void
    {
        $user = User::factory()->create(['permissions' => null]);

        $this->actingAs($user)
            ->get(route('music.setlists.index'))
            ->assertForbidden();
    }

    public function test_user_with_setlists_permission_can_view_index(): void
    {
        $user = User::factory()->withPermissions([
            Module::Setlists->value => [PermissionAction::Read->value],
        ])->create();

        Setlist::factory(3)->create();

        $response = $this->actingAs($user)
            ->get(route('music.setlists.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('music/setlists/Index')
            );
    }

    public function test_superadmin_can_view_setlists_index(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)
            ->get(route('music.setlists.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('music/setlists/Index')
            );
    }

    public function test_can_create_a_setlist(): void
    {
        $user = User::factory()->withPermissions([
            Module::Setlists->value => [PermissionAction::Read->value, PermissionAction::Create->value],
        ])->create();

        $response = $this->actingAs($user)
            ->post(route('music.setlists.store'), [
                'title' => 'Sunday Morning Service',
                'service_date' => '2026-04-27',
                'status' => 'draft',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('setlists', [
            'title' => 'Sunday Morning Service',
            'status' => 'draft',
        ]);
    }

    public function test_can_view_a_setlist(): void
    {
        $user = User::factory()->withPermissions([
            Module::Setlists->value => [PermissionAction::Read->value],
        ])->create();

        $setlist = Setlist::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('music.setlists.show', $setlist));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('music/setlists/Show')
            );
    }

    public function test_can_add_a_song_to_a_setlist(): void
    {
        $user = User::factory()->withPermissions([
            Module::Setlists->value => [PermissionAction::Read->value, PermissionAction::Update->value],
        ])->create();

        $setlist = Setlist::factory()->create();
        $song = Song::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('music.setlist-songs.store', $setlist), [
                'song_id' => $song->id,
                'order' => 1,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('setlist_songs', [
            'setlist_id' => $setlist->id,
            'song_id' => $song->id,
        ]);
    }

    public function test_can_remove_a_song_from_a_setlist(): void
    {
        $user = User::factory()->withPermissions([
            Module::Setlists->value => [PermissionAction::Read->value, PermissionAction::Update->value],
        ])->create();

        $setlist = Setlist::factory()->create();
        $song = Song::factory()->create();
        $setlist->songs()->attach($song, ['order' => 1]);

        $response = $this->actingAs($user)
            ->delete(route('music.setlist-songs.destroy', [$setlist, $song]));

        $response->assertRedirect();

        $this->assertDatabaseMissing('setlist_songs', [
            'setlist_id' => $setlist->id,
            'song_id' => $song->id,
        ]);
    }

    public function test_can_access_setlist_live_page(): void
    {
        $user = User::factory()->withPermissions([
            Module::Setlists->value => [PermissionAction::Read->value],
        ])->create();

        $setlist = Setlist::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('music.setlists.live', $setlist));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('music/setlists/Live')
            );
    }
}
