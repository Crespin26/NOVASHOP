<?php

namespace App\Http\Controllers\Api\V1;

use App\CommerceService;
use App\CommerceSettings;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommerceResource;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return CommerceResource::collection(Order::where('user_id', $request->user()->id)->latest()->paginate(20));
    }

    public function store(Request $request, CommerceService $commerce): CommerceResource
    {
        $data = $request->validate(['address_id' => 'required|integer', 'idempotency_key' => 'required|uuid', 'currency' => ['sometimes', Rule::in(config('markets.currencies'))]]);
        $address = Address::where('user_id', $request->user()->id)->findOrFail($data['address_id']);

        return new CommerceResource($commerce->createOrder($request->user(), $address, $data['idempotency_key'], $data['currency'] ?? null));
    }

    public function show(Request $request, string $reference): CommerceResource
    {
        return new CommerceResource(Order::where('user_id', $request->user()->id)->where('reference', $reference)->with('items')->firstOrFail());
    }

    public function cancel(Request $request, string $reference, CommerceService $commerce): CommerceResource
    {
        return DB::transaction(function () use ($request, $reference, $commerce): CommerceResource {
            $order = Order::where('user_id', $request->user()->id)->where('reference', $reference)->lockForUpdate()->firstOrFail();
            abort_if($order->payment()->whereNotNull('session_id')->exists(), 409, 'A payment session exists; wait for its expiry.');
            $commerce->release($order, 'cancelled');

            return new CommerceResource($order);
        });
    }

    public function returns(Request $request): AnonymousResourceCollection
    {
        return CommerceResource::collection(ReturnRequest::where('user_id', $request->user()->id)->latest()->paginate(20));
    }

    public function requestReturn(Request $request): CommerceResource
    {
        app(CommerceSettings::class)->apply();
        $data = $request->validate(['order_item_id' => 'required|integer', 'quantity' => 'required|integer|min:1|max:99', 'reason' => 'required|string|max:2000']);

        return DB::transaction(function () use ($request, $data): CommerceResource {
            $item = OrderItem::whereHas('order', fn (Builder $q) => $q->where('user_id', $request->user()->id))->lockForUpdate()->findOrFail($data['order_item_id']);
            abort_unless(config('commerce.returns_days') > 0 && $item->order->status === 'delivered' && $item->order->delivered_at && $item->order->delivered_at->gte(now()->subDays(config('commerce.returns_days'))) && $data['quantity'] <= $item->quantity, 422, 'Return not eligible.');
            abort_if(ReturnRequest::where('order_item_id', $item->id)->exists(), 409, 'A return already exists.');

            return new CommerceResource(ReturnRequest::create($data + ['user_id' => $request->user()->id, 'reference' => (string) Str::uuid()]));
        });
    }
}
