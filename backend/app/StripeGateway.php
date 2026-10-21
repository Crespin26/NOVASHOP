<?php

namespace App;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;

class StripeGateway
{
    public function session(Order $order): array
    {
        abort_unless(config('commerce.stripe_secret') && in_array($order->currency, config('commerce.payment_currencies'), true), 503, 'Stripe is not configured.');
        $response = Http::asForm()->withToken(config('commerce.stripe_secret'))->withHeaders(['Idempotency-Key' => 'checkout-'.$order->reference])->connectTimeout(5)->timeout(15)->post('https://api.stripe.com/v1/checkout/sessions', [
            'mode' => 'payment', 'client_reference_id' => $order->reference, 'metadata' => ['reference' => $order->reference],
            'success_url' => rtrim(config('commerce.frontend_url'), '/').'/commande/retour', 'cancel_url' => rtrim(config('commerce.frontend_url'), '/').'/commande/paiement-interrompu',
            'expires_at' => $order->expires_at->timestamp, 'line_items' => [['price_data' => ['currency' => strtolower($order->currency), 'product_data' => ['name' => 'NovaShop '.$order->reference], 'unit_amount' => $order->total], 'quantity' => 1]],
        ])->throw()->json();
        abort_unless(is_string($response['id'] ?? null) && is_string($response['url'] ?? null) && parse_url($response['url'], PHP_URL_SCHEME) === 'https' && parse_url($response['url'], PHP_URL_HOST) === 'checkout.stripe.com', 502, 'Invalid payment provider response.');

        return $response;
    }

    public function refund(Payment $payment, int $amount, string $key): array
    {
        abort_unless(config('commerce.stripe_secret') && $payment->payment_intent, 503, 'Refund gateway unavailable.');
        $data = Http::asForm()->withToken(config('commerce.stripe_secret'))->withHeaders(['Idempotency-Key' => $key])->connectTimeout(5)->timeout(15)->post('https://api.stripe.com/v1/refunds', ['payment_intent' => $payment->payment_intent, 'amount' => $amount])->throw()->json();
        abort_unless(is_string($data['id'] ?? null) && in_array($data['status'] ?? null, ['succeeded', 'pending', 'requires_action'], true), 409, 'Refund requires manual review.');

        return $data;
    }

    public function retrieve(string $type, string $id): array
    {
        abort_unless(config('commerce.stripe_secret') && in_array($type, ['checkout/sessions', 'refunds'], true) && preg_match('/^[a-zA-Z0-9_]+$/', $id), 503, 'Gateway unavailable.');
        $result = Http::withToken(config('commerce.stripe_secret'))->connectTimeout(5)->timeout(15)->get('https://api.stripe.com/v1/'.$type.'/'.$id)->throw()->json();
        abort_unless(($result['id'] ?? null) === $id, 502, 'Provider reference mismatch.');

        return $result;
    }
}
