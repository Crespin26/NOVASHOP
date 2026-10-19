<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    public function test_api_liveness_returns_a_versioned_json_contract(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertExactJson(['data' => [
                'service' => 'NovaShop API',
                'version' => 'v1',
                'status' => 'ok',
            ]])
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_unknown_api_routes_return_json_even_without_an_accept_header(): void
    {
        $this->get('/api/v1/does-not-exist')
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure(['message']);
    }

    public function test_anonymous_clients_cannot_access_the_current_user(): void
    {
        $this->get('/api/v1/me')
            ->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    public function test_a_database_outage_returns_a_safe_unavailable_response(): void
    {
        DB::shouldReceive('select')->once()->with('SELECT 1')
            ->andThrow(new RuntimeException('Sensitive connection details'));

        $this->getJson('/api/v1/ready')
            ->assertStatus(503)
            ->assertExactJson([
                'message' => 'Service temporairement indisponible.',
                'data' => ['status' => 'unavailable'],
            ]);
    }

    public function test_api_requests_are_rate_limited(): void
    {
        for ($request = 0; $request < 120; $request++) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.120'])
                ->getJson('/api/v1/health')->assertOk();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.120'])
            ->getJson('/api/v1/health')->assertStatus(429)->assertHeader('Retry-After');
    }
}
