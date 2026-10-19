<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_foundation_schema_is_available_on_postgresql(): void
    {
        $this->assertSame('pgsql', DB::connection()->getDriverName());
        $this->assertSame('novashop_testing', DB::connection()->getDatabaseName());

        foreach (['users', 'sessions', 'jobs', 'failed_jobs', 'cache', 'personal_access_tokens'] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
        }
    }

    public function test_readiness_checks_the_real_database_connection(): void
    {
        $this->getJson('/api/v1/ready')->assertOk()->assertExactJson([
            'data' => ['status' => 'ready', 'database' => 'connected'],
        ]);
    }

    public function test_current_user_is_read_from_the_session_without_exposing_credentials(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.remember_token');
    }
}
