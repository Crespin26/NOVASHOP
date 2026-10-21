<?php

namespace App\Http\Controllers\Api\V1;

use App\CommerceService;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\PaymentProcessor;
use App\StripeGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function session(Request $request, string $reference, StripeGateway $stripe): JsonResponse
    {
        return DB::transaction(function () use ($request, $reference, $stripe): JsonResponse {
            $order = Order::where('user_id', $request->user()->id)->where('reference', $reference)->lockForUpdate()->firstOrFail();
            abort_unless($order->status === 'awaiting_payment' && $order->expires_at->isFuture(), 409, 'Order is no longer payable.');
            $payment = Payment::firstOrCreate(['order_id' => $order->id], ['amount' => $order->total, 'currency' => $order->currency]);
            if (! $payment->session_id) {
                abort_if($order->expires_at->lt(now()->addMinutes(31)), 409, 'Cancel this reservation and create a new order before starting payment.');
                $session = $stripe->session($order);
                $payment->update(['session_id' => $session['id'], 'checkout_url' => $session['url']]);
            }

            return response()->json(['data' => ['url' => $payment->checkout_url, 'reference' => $order->reference]]);
        });
    }

    public function webhook(Request $request, CommerceService $commerce): JsonResponse
    {
        $secret = config('commerce.stripe_webhook_secret');
        abort_unless(is_string($secret) && $secret !== '', 503);
        $raw = $request->getContent();
        abort_if(strlen($raw) > 1048576, 413);
        $parts = [];
        foreach (explode(',', $request->header('Stripe-Signature', '')) as $part) {
            $pair = explode('=', $part, 2);
            if (count($pair) === 2) {
                $parts[$pair[0]][] = $pair[1];
            }
        }
        $timestamp = $parts['t'][0] ?? '';
        abort_unless(ctype_digit($timestamp) && abs(time() - (int) $timestamp) <= 300, 400, 'Invalid webhook timestamp.');
        $expected = hash_hmac('sha256', $timestamp.'.'.$raw, $secret);
        $valid = false;
        foreach ($parts['v1'] ?? [] as $signature) {
            if (hash_equals($expected, $signature)) {
                $valid = true;
            }
        }abort_unless($valid, 400, 'Invalid signature.');
        try {
            $event = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            abort(400, 'Malformed webhook.');
        }
        abort_unless(is_string($event['id'] ?? null) && strlen($event['id']) <= 255 && is_string($event['type'] ?? null), 400);
        app(PaymentProcessor::class)->handle($event);

        return response()->json(['received' => true]);
    }
}
