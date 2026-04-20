<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): User
    {
        $user = User::factory()->create(['is_superadmin' => true]);
        $this->actingAs($user);

        return $user;
    }

    public function test_index_renders_inertia_page(): void
    {
        $this->actingAsUser();

        $this->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('users/Index')
                ->has('users')
                ->has('modules')
                ->has('actions')
            );
    }

    public function test_store_creates_user_and_redirects(): void
    {
        $this->actingAsUser();

        $this->post(route('users.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_superadmin' => false,
            'full_access' => false,
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_store_grants_full_access_when_flag_set(): void
    {
        $this->actingAsUser();

        $this->post(route('users.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_superadmin' => false,
            'full_access' => true,
        ])->assertRedirect(route('users.index'));

        $user = User::where('email', 'jane@example.com')->firstOrFail();
        $this->assertEquals(['*' => true], $user->permissions);
    }

    public function test_update_modifies_user_and_redirects(): void
    {
        $this->actingAsUser();
        $target = User::factory()->create();

        $this->put(route('users.update', $target), [
            'name' => 'Updated Name',
            'email' => $target->email,
            'is_superadmin' => false,
            'full_access' => false,
            'permissions' => [],
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'Updated Name']);
    }

    public function test_destroy_deletes_user_and_redirects(): void
    {
        $this->actingAsUser();
        $target = User::factory()->create();

        $this->delete(route('users.destroy', $target))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_destroy_cannot_delete_own_account(): void
    {
        $user = $this->actingAsUser();

        $this->delete(route('users.destroy', $user))
            ->assertForbidden();
    }

    public function test_store_validation_requires_name_and_email(): void
    {
        $this->actingAsUser();

        $this->post(route('users.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }
}
