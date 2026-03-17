<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_redirects_unauthenticated_guest(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect();
    }

    public function test_api_login_returns_token_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'name', 'email'],
            ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'mobile-app',
        ]);
    }

    public function test_api_login_does_not_create_web_session(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();

        // The API login should NOT authenticate the web session
        $this->assertGuest('web');
    }

    public function test_api_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_api_login_fails_with_missing_fields(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_api_logout_revokes_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile-app')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Logout successful']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'mobile-app',
        ]);
    }

    public function test_api_user_returns_authenticated_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile-app')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/user');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_api_user_returns_unauthenticated_without_token(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized();
    }

    public function test_mobile_detected_guest_is_redirected_from_dashboard(): void
    {
        $response = $this->withUnencryptedCookie('nativephp_mobile', 'true')
            ->get(route('dashboard'));

        $response->assertRedirect();
    }

    public function test_non_mobile_guest_is_redirected_from_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect();
    }

    public function test_home_does_not_redirect_desktop_guest(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_api_dashboard_returns_stats_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile-app')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'stats' => [
                    'total_participants',
                    'total_activities_this_quarter',
                    'activities_recorded_this_month',
                ],
                'recent_activities',
                'current_quarter',
            ]);
    }

    public function test_api_dashboard_requires_authentication(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertUnauthorized();
    }
}
