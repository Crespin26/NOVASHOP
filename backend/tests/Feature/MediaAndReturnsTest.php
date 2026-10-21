<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaAndReturnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_upload_refuses_executable_content_and_requires_admin(): void
    {
        Storage::fake('local');
        $product = Product::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/api/v1/admin/products/'.$product->id.'/images', [])->assertForbidden();
        $user->forceFill(['is_admin' => true])->save();
        $this->actingAs($user)->postJson('/api/v1/admin/products/'.$product->id.'/images', ['image' => UploadedFile::fake()->createWithContent('payload.svg', '<svg onload="alert(1)"></svg>'), 'alt' => ['fr' => 'Image', 'en' => 'Image']])->assertUnprocessable();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jwqkAAAAASUVORK5CYII=');
        $this->postJson('/api/v1/admin/products/'.$product->id.'/images', ['image' => UploadedFile::fake()->createWithContent('pixel.png', $png), 'alt' => ['fr' => 'Image', 'en' => 'Image']])->assertCreated();
        $this->assertSame(1, $product->images()->count());
    }

    public function test_return_workflow_restock_and_refund_are_idempotent(): void
    {
        Notification::fake();
        Http::preventStrayRequests();
        config(['commerce.returns_days' => 30, 'commerce.stripe_secret' => 'sk_test_fake']);
        Http::fake(['api.stripe.com/*' => Http::response(['id' => 're_test', 'status' => 'succeeded'])]);
        $customer = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => 'delivered', 'delivered_at' => now(), 'total' => 9390]);
        $variant = ProductVariant::factory()->create(['stock' => 7]);
        $item = OrderItem::factory()->create(['order_id' => $order->id, 'product_variant_id' => $variant->id]);
        Payment::factory()->create(['order_id' => $order->id, 'status' => 'paid', 'payment_intent' => 'pi_test']);
        $response = $this->actingAs($customer)->postJson('/api/v1/returns', ['order_item_id' => $item->id, 'quantity' => 1, 'reason' => 'Produit retourné'])->assertCreated();
        $reference = $response->json('data.reference');
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin)->patchJson('/api/v1/admin/returns/'.$reference, ['status' => 'approved', 'reason' => 'Accord'])->assertOk();
        $this->patchJson('/api/v1/admin/returns/'.$reference, ['status' => 'received', 'reason' => 'Reçu', 'restock' => true])->assertOk();
        $this->patchJson('/api/v1/admin/returns/'.$reference, ['status' => 'received', 'reason' => 'Reçu', 'restock' => true])->assertOk();
        $this->assertSame(8, $variant->fresh()->stock);
        $this->patchJson('/api/v1/admin/returns/'.$reference, ['status' => 'refunded', 'reason' => 'Remboursement'])->assertOk();
        $this->patchJson('/api/v1/admin/returns/'.$reference, ['status' => 'refunded', 'reason' => 'Remboursement'])->assertOk();
        Http::assertSentCount(1);
        $this->assertSame('refunded', ReturnRequest::firstOrFail()->status);
    }
}
