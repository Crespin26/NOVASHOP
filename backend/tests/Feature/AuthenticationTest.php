<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_does_not_accept_administrator_role(): void
    {
        Notification::fake();
        $this->postJson('/api/v1/auth/register', ['name' => 'Client', 'email' => 'CLIENT@example.test', 'password' => 'Test-password-123', 'password_confirmation' => 'Test-password-123', 'is_admin' => true])->assertCreated()->assertJsonMissingPath('data.password');
        $user = User::firstOrFail();
        $this->assertFalse($user->is_admin);
        $this->assertSame('client@example.test', $user->email);
        $this->assertTrue(Hash::check('Test-password-123', $user->password));
        $this->assertNull($user->email_verified_at);
    }

    public function test_login_and_logout_are_session_based(): void
    {
        $user = User::factory()->create(['password' => 'Test-password-123']);
        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'wrong'])->assertUnprocessable();
        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'Test-password-123'])->assertOk();
        $this->getJson('/api/v1/me')->assertOk();
        $this->postJson('/api/v1/auth/logout')->assertOk();
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_unknown_reset_addresses_receive_a_generic_response(): void
    {
        Notification::fake();
        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'missing@example.test'])->assertOk()->assertJsonPath('data.message', 'If an account matches this address, a reset link will be sent.');
    }

    public function test_customer_cannot_enter_admin_and_profile_cannot_change_role(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->getJson('/api/v1/admin/dashboard')->assertForbidden();
        $this->patchJson('/api/v1/profile', ['name' => 'Updated', 'is_admin' => true])->assertOk();
        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_spa_mutations_require_csrf_outside_the_testing_bypass(): void
    {
        $this->app->instance('env', 'local');
        $this->withHeader('Origin', 'http://localhost:5173')->postJson('/api/v1/auth/login', ['email' => 'user@example.test', 'password' => 'invalid'])->assertStatus(419);
    }
}
