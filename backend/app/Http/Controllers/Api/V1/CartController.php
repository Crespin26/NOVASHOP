<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommerceResource;
use App\Models\CartItem;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return CommerceResource::collection(CartItem::where('user_id', $request->user()->id)->with('variant.product')->orderBy('id')->paginate(100));
    }

    public function put(Request $request): CommerceResource
    {
        $data = $request->validate(['variant_id' => 'required|integer', 'quantity' => 'required|integer|between:1,99']);

        return DB::transaction(function () use ($request, $data): CommerceResource {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $variant = ProductVariant::whereKey($data['variant_id'])->where('active', true)->whereHas('product', fn (Builder $q) => $q->where('status', 'published'))->firstOrFail();
            abort_if($data['quantity'] > $variant->stock, 409, 'Insufficient stock.');
            abort_if(CartItem::where('user_id', $request->user()->id)->count() >= 100 && ! CartItem::where('user_id', $request->user()->id)->where('product_variant_id', $variant->id)->exists(), 422, 'Cart limit reached.');

            return new CommerceResource(CartItem::updateOrCreate(['user_id' => $request->user()->id, 'product_variant_id' => $variant->id], ['quantity' => $data['quantity']])->load('variant.product'));
        });
    }

    public function remove(Request $request, int $variant): JsonResponse
    {
        DB::transaction(function () use ($request, $variant): void {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            CartItem::where('user_id', $request->user()->id)->where('product_variant_id', $variant)->delete();
        });

        return response()->json(null, 204);
    }

    public function clear(Request $request): JsonResponse
    {
        DB::transaction(function () use ($request): void {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            CartItem::where('user_id', $request->user()->id)->delete();
        });

        return response()->json(null, 204);
    }

    public function favorites(Request $request): AnonymousResourceCollection
    {
        return CommerceResource::collection(Favorite::where('user_id', $request->user()->id)->whereHas('product', fn (Builder $q) => $q->where('status', 'published'))->with('product')->paginate(50));
    }

    public function favorite(Request $request, Product $product): JsonResponse
    {
        abort_unless($product->status === 'published', 404);
        Favorite::firstOrCreate(['user_id' => $request->user()->id, 'product_id' => $product->id]);

        return response()->json(['data' => ['saved' => true]]);
    }

    public function unfavorite(Request $request, int $product): JsonResponse
    {
        Favorite::where('user_id', $request->user()->id)->where('product_id', $product)->delete();

        return response()->json(null, 204);
    }
}
