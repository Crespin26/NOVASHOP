<?php

namespace Tests\Feature;

use App\CommerceSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommercialSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_require_reauthentication_and_keep_prices_tax_inclusive(): void
    {
        $user = User::factory()->create(['password' => 'test-password-123']);
        $user->forceFill(['is_admin' => true])->save();
        $data = ['enabled' => true, 'currency' => 'EUR', 'countries' => ['*'], 'taxes_configured' => true, 'shipping' => 490, 'free_shipping_threshold' => 10000, 'returns_days' => 14, 'prices_include_tax' => false];
        $this->actingAs($user)->putJson('/api/v1/admin/settings', $data)->assertUnprocessable();
        $this->putJson('/api/v1/admin/settings', $data + ['current_password' => 'test-password-123'])->assertOk()->assertJsonPath('data.prices_include_tax', true);
        $values = app(CommerceSettings::class)->values();
        $this->assertSame(['*'], $values['countries']);
        $this->assertTrue($values['prices_include_tax']);
        $this->assertArrayNotHasKey('current_password', $values);
    }
}
