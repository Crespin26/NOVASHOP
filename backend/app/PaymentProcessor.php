<?php

namespace App;

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Notifications\CommerceNotification;
use Illuminate\Support\Facades\DB;

class PaymentProcessor
{
    public function handle(array $event): void
    {
        $commerce = app(CommerceService::class);
        DB::transaction(function () use ($event, $commerce): void {
            if (DB::table('stripe_events')->where('id', $event['id'])->exists()) {
                return;
            }
            $object = $event['data']['object'] ?? [];
            if (in_array($event['type'], ['refund.updated', 'refund.created', 'refund.failed'], true)) {
                $return = ReturnRequest::where('refund_id', $object['id'] ?? null)->lockForUpdate()->first();
                abort_unless($return, 409, 'Unknown refund.');
                abort_unless(($object['amount'] ?? null) === $return->quantity * $return->item->unit_price && strtoupper($object['currency'] ?? '') === $return->item->order->currency, 409, 'Refund amount mismatch.');
                if ($return->status === 'refund_pending') {
                    $status = match ($object['status'] ?? '') {
                        'succeeded' => 'refunded','failed','canceled' => 'refund_failed',default => 'refund_pending'
                    };
                    $return->update(['status' => $status]);
                    $commerce->audit(null, 'refund.'.$status, 'return', $return->id);
                }
            }

            $reference = $object['metadata']['reference'] ?? null;
            if (in_array($event['type'], ['checkout.session.completed', 'checkout.session.async_payment_succeeded', 'checkout.session.expired', 'checkout.session.async_payment_failed'], true)) {
                $order = Order::where('reference', $reference)->lockForUpdate()->first();
                abort_unless($order, 409, 'Unknown payment reference.');
                $payment = $order->payment()->lockForUpdate()->first();
                abort_unless($payment && hash_equals($payment->session_id ?? '', (string) ($object['id'] ?? '')), 409, 'Unknown session.');
                if (in_array($event['type'], ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true) && ($object['payment_status'] ?? '') === 'paid') {
                    abort_unless(($object['amount_total'] ?? null) === $order->total && strtoupper($object['currency'] ?? '') === $order->currency, 409, 'Payment amount mismatch.');
                    if ($payment->status !== 'paid') {
                        $payment->update(['status' => 'paid', 'payment_intent' => $object['payment_intent'] ?? null]);
                        if ($order->status === 'awaiting_payment') {
                            $order->update(['status' => 'paid', 'paid_at' => now()]);
                            $commerce->audit(null, 'payment.confirmed', 'order', $order->id);
                            $order->user->notify((new CommerceNotification($order->locale === 'en' ? 'Payment confirmed' : 'Paiement confirmé', $order->reference))->locale($order->locale));
                        } elseif (in_array($order->status, ['expired', 'cancelled'], true)) {
                            $order->update(['status' => 'payment_review']);
                            $commerce->audit(null, 'payment.late_review', 'order', $order->id);
                        }
                    }
                } elseif (in_array($event['type'], ['checkout.session.expired', 'checkout.session.async_payment_failed'], true) && $order->status === 'awaiting_payment') {
                    $commerce->release($order, 'expired');
                    $payment->update(['status' => 'failed']);
                }
            }
            DB::table('stripe_events')->insertOrIgnore(['id' => $event['id'], 'type' => $event['type'], 'processed_at' => now()]);
        }, 3);
    }
}
