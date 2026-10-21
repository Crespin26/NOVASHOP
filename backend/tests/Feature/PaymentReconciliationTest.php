<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_missed_webhook_is_recovered_without_double_confirmation(): void
    {
        Notification::fake();
        Http::preventStrayRequests();
        config(['commerce.stripe_secret' => 'sk_test_fake']);
        $order = Order::factory()->create();
        Payment::factory()->create(['order_id' => $order->id, 'session_id' => 'cs_reconcile']);
        Http::fake(['api.stripe.com/*' => Http::response(['id' => 'cs_reconcile', 'metadata' => ['reference' => $order->reference], 'payment_status' => 'paid', 'amount_total' => 9390, 'currency' => 'eur', 'payment_intent' => 'pi_reconcile'])]);
        $this->artisan('commerce:reconcile-payments')->assertExitCode(0);
        $this->artisan('commerce:reconcile-payments')->assertExitCode(0);
        $this->assertSame('paid', $order->fresh()->status);
        Notification::assertCount(1);
        Http::assertSentCount(1);
    }
}
