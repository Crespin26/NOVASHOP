<?php

namespace App\Console\Commands;

use App\CommerceService;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExpireReservations extends Command
{
    protected $signature = 'commerce:expire-reservations';

    protected $description = 'Release expired reservations without a provider session; provider sessions expire through verified webhooks.';

    public function handle(CommerceService $commerce): int
    {
        Order::where('status', 'awaiting_payment')->where('expires_at', '<=', now())->whereDoesntHave('payment', fn (Builder $q) => $q->whereNotNull('session_id'))->select('id')->chunkById(100, function (Collection $orders) use ($commerce): void {
            foreach ($orders as $candidate) {
                DB::transaction(function () use ($candidate, $commerce): void {
                    $order = Order::whereKey($candidate->id)->lockForUpdate()->firstOrFail();
                    if ($order->status === 'awaiting_payment' && $order->expires_at->isPast() && ! $order->payment()->whereNotNull('session_id')->exists()) {
                        $commerce->release($order, 'expired');
                        $commerce->audit(null, 'order.expired', 'order', $order->id);
                    }
                });
            }
        });

        return self::SUCCESS;
    }
}
