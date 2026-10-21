<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpirationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_expiration_releases_stock_once_and_does_not_race_provider_sessions(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 6]);
        $order = Order::factory()->create(['expires_at' => now()->subMinute()]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_variant_id' => $variant->id, 'quantity' => 2]);
        $provider = Order::factory()->create(['expires_at' => now()->subMinute()]);
        Payment::factory()->create(['order_id' => $provider->id, 'session_id' => 'cs_existing']);
        $this->artisan('commerce:expire-reservations')->assertExitCode(0);
        $this->artisan('commerce:expire-reservations')->assertExitCode(0);
        $this->assertSame(8, $variant->fresh()->stock);
        $this->assertSame('expired', $order->fresh()->status);
        $this->assertSame('awaiting_payment', $provider->fresh()->status);
    }
}
