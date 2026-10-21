<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_unsigned_webhooks_cannot_mark_an_order_paid(): void
    {
        config(['commerce.stripe_webhook_secret' => 'secret']);
        $order = Order::factory()->create();
        $this->postJson('/api/v1/stripe/webhook', ['id' => 'evt_bad'])->assertBadRequest();
        $this->assertSame('awaiting_payment', $order->fresh()->status);
    }

    public function test_verified_payment_events_are_idempotent_and_check_amounts(): void
    {
        Notification::fake();
        config(['commerce.stripe_webhook_secret' => 'secret']);
        $order = Order::factory()->create();
        Payment::factory()->create(['order_id' => $order->id, 'session_id' => 'cs_test_123']);
        $event = ['id' => 'evt_paid', 'type' => 'checkout.session.completed', 'data' => ['object' => ['id' => 'cs_test_123', 'metadata' => ['reference' => $order->reference], 'payment_status' => 'paid', 'amount_total' => 9390, 'currency' => 'eur', 'payment_intent' => 'pi_test']]];
        $raw = json_encode($event, JSON_THROW_ON_ERROR);
        $signature = 't='.time().',v1='.hash_hmac('sha256', time().'.'.$raw, 'secret');
        $this->call('POST', '/api/v1/stripe/webhook', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $signature], $raw)->assertOk();
        $this->call('POST', '/api/v1/stripe/webhook', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $signature], $raw)->assertOk();
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertDatabaseCount('stripe_events', 1);
        Notification::assertCount(1);
    }

    public function test_session_creation_uses_server_amount_and_provider_idempotency(): void
    {
        Http::preventStrayRequests();
        config(['commerce.stripe_secret' => 'sk_test_fake', 'commerce.payment_currencies' => ['EUR']]);
        Http::fake(['api.stripe.com/*' => Http::response(['id' => 'cs_safe', 'url' => 'https://checkout.stripe.com/c/pay/cs_safe'])]);
        $order = Order::factory()->create();
        $this->actingAs($order->user)->postJson('/api/v1/orders/'.$order->reference.'/payment', ['amount' => 1])->assertOk();
        $this->postJson('/api/v1/orders/'.$order->reference.'/payment')->assertOk();
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['line_items'][0]['price_data']['unit_amount'] === 9390 && $request->hasHeader('Idempotency-Key', 'checkout-'.$order->reference));
    }

    public function test_signed_wrong_amount_rolls_back(): void
    {
        config(['commerce.stripe_webhook_secret' => 'secret']);
        $order = Order::factory()->create();
        Payment::factory()->create(['order_id' => $order->id, 'session_id' => 'cs_wrong']);
        $raw = json_encode(['id' => 'evt_wrong', 'type' => 'checkout.session.completed', 'data' => ['object' => ['id' => 'cs_wrong', 'metadata' => ['reference' => $order->reference], 'payment_status' => 'paid', 'amount_total' => 1, 'currency' => 'eur']]], JSON_THROW_ON_ERROR);
        $timestamp = time();
        $signature = 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$raw, 'secret');
        $this->call('POST', '/api/v1/stripe/webhook', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $signature], $raw)->assertConflict();
        $this->assertSame('awaiting_payment', $order->fresh()->status);
        $this->assertDatabaseCount('stripe_events', 0);
    }
}
