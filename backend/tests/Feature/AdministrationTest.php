<?php

namespace Tests\Feature;

use App\Models\AuditEntry;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_updates_are_locked_audited_and_validated(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $variant = ProductVariant::factory()->create();
        $this->actingAs($admin)->postJson('/api/v1/admin/variants/'.$variant->id.'/stock', ['stock' => 3, 'reason' => 'Inventaire'])->assertCreated();
        $this->assertSame(3, $variant->fresh()->stock);
        $this->assertSame(-5, StockMovement::firstOrFail()->delta);
        $this->assertSame(1, AuditEntry::count());
        $this->postJson('/api/v1/admin/variants/'.$variant->id.'/stock', ['stock' => -1, 'reason' => 'bad'])->assertUnprocessable();
    }

    public function test_admin_settings_never_expose_provider_keys(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        config(['commerce.stripe_secret' => 'test-private']);
        $this->actingAs($admin)->getJson('/api/v1/admin/settings')->assertOk()->assertJsonMissingPath('data.stripe_secret');
    }
}
