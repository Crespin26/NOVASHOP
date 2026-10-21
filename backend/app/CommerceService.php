<?php

namespace App;

use App\Models\Address;
use App\Models\AuditEntry;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommerceService
{
    public function audit(?int $actor, string $action, string $type, int|string $id, array $metadata = []): void
    {
        AuditEntry::create(['actor_id' => $actor, 'action' => $action, 'subject_type' => $type, 'subject_id' => (string) $id, 'metadata' => $metadata]);
    }

    public function createOrder(User $user, Address $address, string $key, ?string $currency = null): Order
    {
        app(CommerceSettings::class)->apply();
        abort_unless(config('commerce.enabled') && config('commerce.taxes_configured') && count(config('commerce.countries')) > 0, 503, 'Commercial settings are not validated.');
        abort_unless(in_array(config('commerce.currency'), config('markets.currencies'), true), 503, 'Unsupported currency.');

        $currency ??= config('commerce.currency');

        return DB::transaction(function () use ($user, $address, $key, $currency): Order {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $hash = hash('sha256', $address->id.'|'.$currency);
            $existing = Order::where('user_id', $user->id)->where('idempotency_key', $key)->first();
            if ($existing) {
                abort_unless(hash_equals($existing->request_hash, $hash), 409, 'Idempotency key already used for another address.');

                return $existing->load('items');
            }
            abort_unless($address->user_id === $user->id && (in_array($address->country, config('commerce.countries'), true) || in_array('*', config('commerce.countries'), true)), 422, 'Delivery address unavailable.');
            $lines = CartItem::where('user_id', $user->id)->orderBy('product_variant_id')->get();
            abort_if($lines->isEmpty(), 422, 'Cart is empty.');
            $subtotal = 0;
            $snapshots = [];
            foreach ($lines as $line) {
                $variant = ProductVariant::whereKey($line->product_variant_id)->lockForUpdate()->firstOrFail();
                abort_unless($variant->active && $variant->product->status === 'published' && $variant->currency === config('commerce.currency') && $variant->stock >= $line->quantity, 409, 'Cart price or availability must be reviewed.');
                $subtotal += $variant->price * $line->quantity;
                $snapshots[] = ['product_variant_id' => $variant->id, 'sku' => $variant->sku, 'name' => $variant->product->name, 'options' => $variant->options, 'quantity' => $line->quantity, 'unit_price' => $variant->price, 'total' => $variant->price * $line->quantity];
            }
            abort_if($subtotal > 100000000, 422, 'Order limit exceeded.');
            $shipping = $subtotal >= config('commerce.free_shipping_threshold') ? 0 : config('commerce.shipping');
            $market = app(Market::class);
            $shipping = $market->convert($shipping, $currency);
            $subtotal = 0;
            foreach ($snapshots as &$snapshot) {
                $snapshot['unit_price'] = $market->convert($snapshot['unit_price'], $currency);
                $snapshot['total'] = $snapshot['unit_price'] * $snapshot['quantity'];
                $subtotal += $snapshot['total'];
            }unset($snapshot);
            abort_if($subtotal + $shipping > 100000000, 422, 'Converted order limit exceeded.');
            $order = Order::create(['user_id' => $user->id, 'reference' => (string) Str::uuid(), 'idempotency_key' => $key, 'request_hash' => $hash, 'currency' => $currency, 'subtotal' => $subtotal, 'shipping' => $shipping, 'total' => $subtotal + $shipping, 'address_snapshot' => $address->only(['name', 'street', 'city', 'postal', 'country']), 'locale' => $user->locale, 'expires_at' => now()->addMinutes(45)]);
            foreach ($snapshots as $snapshot) {
                $order->items()->create($snapshot);
                $variant = ProductVariant::findOrFail($snapshot['product_variant_id']);
                $variant->decrement('stock', $snapshot['quantity']);
                StockMovement::create(['product_variant_id' => $variant->id, 'actor_id' => $user->id, 'delta' => -$snapshot['quantity'], 'balance' => $variant->fresh()->stock, 'reason' => 'reserve:'.$order->reference]);
            }
            $this->audit($user->id, 'order.created', 'order', $order->id);

            return $order->load('items');
        }, 3);
    }

    public function release(Order $order, string $status): void
    {
        abort_unless(in_array($status, ['expired', 'cancelled'], true), 422);
        abort_unless($order->status === 'awaiting_payment', 409, 'Order cannot be released.');
        foreach ($order->items()->orderBy('product_variant_id')->get() as $item) {
            $variant = ProductVariant::whereKey($item->product_variant_id)->lockForUpdate()->firstOrFail();
            $variant->increment('stock', $item->quantity);
            StockMovement::create(['product_variant_id' => $variant->id, 'delta' => $item->quantity, 'balance' => $variant->fresh()->stock, 'reason' => $status.':'.$order->reference]);
        }
        $order->update(['status' => $status]);
    }
}
