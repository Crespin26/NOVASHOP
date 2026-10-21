<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommerceTest extends TestCase
{
    use RefreshDatabase;

    public function test_foreign_addresses_and_orders_are_not_exposed(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create();
        $order = Order::factory()->create();
        $this->actingAs($user)->putJson('/api/v1/addresses/'.$address->id, [])->assertNotFound();
        $this->getJson('/api/v1/orders/'.$order->reference)->assertNotFound();
    }

    public function test_default_addresses_remain_unique(): void
    {
        $user = User::factory()->create();
        $data = ['name' => 'Client', 'label' => 'Maison', 'street' => '1 rue', 'city' => 'Ville', 'postal' => '00000', 'country' => 'BJ', 'is_default' => true];
        $this->actingAs($user)->postJson('/api/v1/addresses', $data)->assertCreated();
        $this->postJson('/api/v1/addresses', $data)->assertCreated();
        $this->assertSame(1, Address::where('user_id', $user->id)->where('is_default', true)->count());
    }

    public function test_order_is_canonical_idempotent_and_releases_stock_only_once(): void
    {
        config(['commerce.enabled' => true, 'commerce.taxes_configured' => true, 'commerce.countries' => ['BJ']]);
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['status' => 'published']);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $this->actingAs($user)->putJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 2, 'price' => 1])->assertCreated();
        $payload = ['address_id' => $address->id, 'idempotency_key' => (string) Str::uuid(), 'total' => 1];
        $response = $this->postJson('/api/v1/orders', $payload)->assertCreated()->assertJsonPath('data.total', 17800);
        $reference = $response->json('data.reference');
        $this->postJson('/api/v1/orders', $payload)->assertOk()->assertJsonPath('data.reference', $reference);
        $this->assertSame(1, Order::count());
        $this->assertSame(6, $variant->fresh()->stock);
        $this->postJson('/api/v1/orders/'.$reference.'/cancel')->assertOk();
        $this->assertSame(8, $variant->fresh()->stock);
        $this->postJson('/api/v1/orders/'.$reference.'/cancel')->assertConflict();
        $this->assertSame(8, $variant->fresh()->stock);
    }

    public function test_checkout_is_disabled_without_validated_commercial_settings(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user)->postJson('/api/v1/orders', ['address_id' => $address->id, 'idempotency_key' => (string) Str::uuid()])->assertStatus(503);
    }

    public function test_cart_rejects_stock_excess_and_private_products(): void
    {
        $variant = ProductVariant::factory()->create();
        $this->actingAs(User::factory()->create())->putJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 1])->assertNotFound();
        $variant->product->update(['status' => 'published']);
        $this->putJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 99])->assertConflict();
    }
}
