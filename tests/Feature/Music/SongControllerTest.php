<?php

namespace Tests\Feature\Music;

use App\Enums\Module;
use App\Enums\PermissionAction;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SongControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get(route('music.songs.index'));

        $response->assertRedirect();
        $response->assertStatus(302);
    }

    public function test_user_without_songs_permission_gets_403(): void
    {
        $user = User::factory()->create(['permissions' => null]);

        $this->actingAs($user)
            ->get(route('music.songs.index'))
            ->assertForbidden();
    }

    public function test_user_with_songs_permission_can_view_index(): void
    {
        $user = User::factory()->withPermissions([
            Module::Songs->value => [PermissionAction::Read->value],
        ])->create();

        Song::factory(3)->create();

        $response = $this->actingAs($user)
            ->get(route('music.songs.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('music/songs/Index')
            );
    }

    public function test_superadmin_can_view_songs_index(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)
            ->get(route('music.songs.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('music/songs/Index')
            );
    }

    public function test_can_create_a_song(): void
    {
        $user = User::factory()->withPermissions([
            Module::Songs->value => [PermissionAction::Read->value, PermissionAction::Create->value],
        ])->create();

        $response = $this->actingAs($user)
            ->post(route('music.songs.store'), [
                'title' => 'Amazing Grace',
                'artist' => 'John Newton',
                'original_key' => 'G',
                'tempo' => 72,
                'time_signature' => '3/4',
                'lyrics_with_chords' => '[G]Amazing grace',
                'is_active' => true,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('songs', [
            'title' => 'Amazing Grace',
            'artist' => 'John Newton',
            'original_key' => 'G',
        ]);
    }

    public function test_validation_fails_with_missing_required_fields(): void
    {
        $user = User::factory()->withPermissions([
            Module::Songs->value => [PermissionAction::Read->value, PermissionAction::Create->value],
        ])->create();

        $response = $this->actingAs($user)
            ->post(route('music.songs.store'), [
                'title' => '',
            ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_can_update_a_song(): void
    {
        $user = User::factory()->withPermissions([
            Module::Songs->value => [PermissionAction::Read->value, PermissionAction::Update->value],
        ])->create();

        $song = Song::factory()->create(['title' => 'Old Title']);

        $response = $this->actingAs($user)
            ->put(route('music.songs.update', $song), [
                'title' => 'New Title',
                'original_key' => 'D',
                'lyrics_with_chords' => '[D]New lyrics here',
                'is_active' => true,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('songs', [
            'id' => $song->id,
            'title' => 'New Title',
        ]);
    }

    public function test_can_delete_a_song(): void
    {
        $user = User::factory()->withPermissions([
            Module::Songs->value => [PermissionAction::Read->value, PermissionAction::Delete->value],
        ])->create();

        $song = Song::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('music.songs.destroy', $song));

        $response->assertRedirect();

        $this->assertDatabaseMissing('songs', [
            'id' => $song->id,
        ]);
    }
}
