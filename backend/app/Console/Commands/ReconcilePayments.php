<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\ReturnRequest;
use App\PaymentProcessor;
use App\StripeGateway;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ReconcilePayments extends Command
{
    protected $signature = 'commerce:reconcile-payments';

    protected $description = 'Reconcile unresolved sessions and refunds against the provider API.';

    public function handle(StripeGateway $stripe, PaymentProcessor $processor): int
    {
        if (! config('commerce.stripe_secret')) {
            $this->warn('Stripe is not configured.');

            return self::SUCCESS;
        }
        $failures = 0;
        Payment::where('status', 'pending')->whereNotNull('session_id')->chunkById(100, function (Collection $payments) use ($stripe, $processor, &$failures): void {
            foreach ($payments as $payment) {
                try {
                    $session = $stripe->retrieve('checkout/sessions', $payment->session_id);
                    $type = ($session['payment_status'] ?? null) === 'paid' ? 'checkout.session.completed' : (($session['status'] ?? null) === 'expired' ? 'checkout.session.expired' : null);
                    if ($type) {
                        $processor->handle(['id' => 'reconcile-'.$session['id'].'-'.str_replace('.', '-', $type), 'type' => $type, 'data' => ['object' => $session]]);
                    }
                } catch (\Throwable $error) {
                    report($error);
                    $failures++;
                }
            }
        });
        ReturnRequest::where('status', 'refund_pending')->whereNotNull('refund_id')->chunkById(100, function (Collection $returns) use ($stripe, $processor, &$failures): void {
            foreach ($returns as $return) {
                try {
                    $refund = $stripe->retrieve('refunds', $return->refund_id);
                    $processor->handle(['id' => 'reconcile-'.$refund['id'].'-'.($refund['status'] ?? 'unknown'), 'type' => 'refund.updated', 'data' => ['object' => $refund]]);
                } catch (\Throwable $error) {
                    report($error);
                    $failures++;
                }
            }
        });

        return $failures ? self::FAILURE : self::SUCCESS;
    }
}
