<?php

namespace App\Http\Controllers\Api\V1;

use App\CommerceService;
use App\CommerceSettings;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommerceResource;
use App\Http\Resources\UserResource;
use App\Models\AuditEntry;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\StockMovement;
use App\Models\User;
use App\Notifications\CommerceNotification;
use App\StripeGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        return response()->json(['data' => ['products' => Product::count(), 'orders' => Order::count(), 'low_stock' => ProductVariant::where('stock', '<=', 3)->count(), 'new_messages' => ContactMessage::where('status', 'new')->count()]]);
    }

    public function index(Request $request, string $kind): AnonymousResourceCollection
    {
        $models = ['products' => Product::class, 'categories' => Category::class, 'stocks' => StockMovement::class, 'orders' => Order::class, 'returns' => ReturnRequest::class, 'messages' => ContactMessage::class, 'activity' => AuditEntry::class, 'customers' => User::class];
        abort_unless(isset($models[$kind]), 404);
        $data = $request->validate(['q' => 'sometimes|string|max:120', 'status' => 'sometimes|alpha_dash|max:30', 'page' => 'sometimes|integer|min:1|max:10000']);
        $query = $models[$kind]::query();
        if (isset($data['status']) && in_array($kind, ['products', 'orders', 'returns', 'messages'], true)) {
            $query->where('status', $data['status']);
        }
        if (isset($data['q'])) {
            $column = match ($kind) {
                'orders','returns' => 'reference','customers' => 'email','messages' => 'subject','products' => 'slug',default => null
            };
            if ($column) {
                $query->where($column, 'like', '%'.addcslashes($data['q'], '%_\\').'%');
            }
        }

        return $kind === 'customers' ? UserResource::collection($query->latest()->paginate(20)) : CommerceResource::collection($query->latest()->paginate(20));
    }

    public function category(Request $request, CommerceService $commerce, ?Category $category = null): CommerceResource
    {
        $data = $request->validate(['slug' => ['required', 'alpha_dash:ascii', 'max:100', Rule::unique('categories', 'slug')->ignore($category?->id)], 'name' => 'required|array:fr,en', 'name.fr' => 'required|string|max:100', 'name.en' => 'required|string|max:100', 'position' => 'sometimes|integer|min:0|max:10000']);
        $category ??= new Category;
        $category->fill($data)->save();
        $commerce->audit($request->user()->id, 'category.saved', 'category', $category->id);

        return new CommerceResource($category);
    }

    public function deleteCategory(Request $request, Category $category, CommerceService $commerce): JsonResponse
    {
        $data = $request->validate(['replacement_id' => ['nullable', 'integer', Rule::exists('categories', 'id'), Rule::notIn([$category->id])]]);
        DB::transaction(function () use ($request, $category, $data, $commerce): void {
            $category->lockForUpdate()->findOrFail($category->id);
            if ($category->products()->exists()) {
                abort_unless(isset($data['replacement_id']), 422, 'Reassignment required.');
                $category->products()->update(['category_id' => $data['replacement_id']]);
            }
            $category->delete();
            $commerce->audit($request->user()->id, 'category.deleted', 'category', $category->id);
        });

        return response()->json(null, 204);
    }

    public function product(Request $request, CommerceService $commerce, ?Product $product = null): CommerceResource
    {
        $data = $request->validate(['category_id' => 'required|integer|exists:categories,id', 'slug' => ['required', 'alpha_dash:ascii', 'max:100', Rule::unique('products', 'slug')->ignore($product?->id)], 'brand' => 'required|string|max:100', 'name' => 'required|array:fr,en', 'name.fr' => 'required|string|max:100', 'name.en' => 'required|string|max:100', 'description' => 'required|array:fr,en', 'description.fr' => 'required|string|max:10000', 'description.en' => 'required|string|max:10000', 'specifications' => 'nullable|array|max:50', 'status' => 'required|in:draft,published,archived']);
        $product ??= new Product;
        if ($data['status'] === 'published') {
            abort_unless($product->exists && $product->variants()->where('active', true)->exists() && $product->images()->exists(), 422, 'Add an active variant and an image before publication.');
            $data['published_at'] = $product->published_at ?? now();
        }
        $product->fill($data)->save();
        $commerce->audit($request->user()->id, 'product.saved', 'product', $product->id);

        return new CommerceResource($product->load(['variants', 'images']));
    }

    public function variant(Request $request, Product $product, CommerceService $commerce, ?ProductVariant $variant = null): CommerceResource
    {
        abort_if($variant && $variant->product_id !== $product->id, 404);
        $data = $request->validate(['sku' => ['required', 'string', 'max:100', Rule::unique('product_variants', 'sku')->ignore($variant?->id)], 'options' => 'nullable|array|max:10', 'price' => 'required|integer|between:0,100000000', 'compare_at_price' => 'nullable|integer|gt:price|max:100000000', 'currency' => ['required', Rule::in(config('markets.currencies'))], 'active' => 'required|boolean']);
        $variant ??= new ProductVariant;
        $variant->fill($data);
        $variant->product_id = $product->id;
        $variant->save();
        $commerce->audit($request->user()->id, 'variant.saved', 'variant', $variant->id);

        return new CommerceResource($variant);
    }

    public function stock(Request $request, ProductVariant $variant, CommerceService $commerce): CommerceResource
    {
        $data = $request->validate(['stock' => 'required|integer|between:0,999999', 'reason' => 'required|string|max:200']);

        return DB::transaction(function () use ($request, $variant, $data, $commerce): CommerceResource {
            $variant = ProductVariant::whereKey($variant->id)->lockForUpdate()->firstOrFail();
            $delta = $data['stock'] - $variant->stock;
            $variant->update(['stock' => $data['stock']]);
            $movement = StockMovement::create(['product_variant_id' => $variant->id, 'actor_id' => $request->user()->id, 'delta' => $delta, 'balance' => $variant->stock, 'reason' => $data['reason']]);
            $commerce->audit($request->user()->id, 'stock.adjusted', 'variant', $variant->id, ['movement_id' => $movement->id]);

            return new CommerceResource($movement);
        });
    }

    public function image(Request $request, Product $product, CommerceService $commerce): CommerceResource
    {
        $data = $request->validate(['image' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:8192|dimensions:max_width=5000,max_height=5000', 'alt' => 'required|array:fr,en', 'alt.fr' => 'required|string|max:200', 'alt.en' => 'required|string|max:200', 'position' => 'sometimes|integer|between:0,20']);
        abort_if($product->images()->count() >= 8, 422, 'Image limit reached.');
        $path = $request->file('image')->store('products', 'local');
        try {
            $image = $product->images()->create(['path' => $path, 'alt' => $data['alt'], 'position' => $data['position'] ?? 0]);
        } catch (\Throwable $error) {
            Storage::disk('local')->delete($path);
            throw $error;
        }
        $commerce->audit($request->user()->id, 'image.uploaded', 'image', $image->id);

        return new CommerceResource($image);
    }

    public function deleteImage(Request $request, ProductImage $image, CommerceService $commerce): JsonResponse
    {
        abort_if($image->product->status === 'published' && $image->product->images()->count() <= 1, 409, 'Unpublish before removing the last image.');
        $path = $image->path;
        $image->delete();
        Storage::disk('local')->delete($path);
        $commerce->audit($request->user()->id, 'image.deleted', 'image', $image->id);

        return response()->json(null, 204);
    }

    public function order(Request $request, string $reference): CommerceResource
    {
        return new CommerceResource(Order::where('reference', $reference)->with('items')->firstOrFail());
    }

    public function transition(Request $request, string $reference, CommerceService $commerce): CommerceResource
    {
        $data = $request->validate(['status' => 'required|in:preparing,shipped,delivered', 'carrier' => 'required_if:status,shipped|string|max:100', 'tracking_number' => 'required_if:status,shipped|string|max:120']);

        return DB::transaction(function () use ($request, $reference, $data, $commerce): CommerceResource {
            $order = Order::where('reference', $reference)->lockForUpdate()->firstOrFail();
            $transitions = ['paid' => 'preparing', 'preparing' => 'shipped', 'shipped' => 'delivered'];
            abort_unless(($transitions[$order->status] ?? null) === $data['status'], 409, 'Invalid order transition.');
            $order->fill(collect($data)->only(['status', 'carrier', 'tracking_number'])->all());
            if ($data['status'] === 'shipped') {
                $order->shipped_at = now();
            }
            if ($data['status'] === 'delivered') {
                $order->delivered_at = now();
            }
            $order->save();
            $commerce->audit($request->user()->id, 'order.'.$data['status'], 'order', $order->id);
            $order->user->notify((new CommerceNotification($order->locale === 'en' ? 'Order update' : 'Mise à jour de commande', $order->reference))->locale($order->locale));

            return new CommerceResource($order);
        });
    }

    public function returned(Request $request, string $reference, CommerceService $commerce, StripeGateway $stripe): CommerceResource
    {
        $data = $request->validate(['status' => 'required|in:approved,rejected,received,refunded', 'reason' => 'required|string|max:500', 'restock' => 'sometimes|boolean']);

        return DB::transaction(function () use ($request, $reference, $data, $commerce, $stripe): CommerceResource {
            $return = ReturnRequest::where('reference', $reference)->lockForUpdate()->firstOrFail();
            $allowed = ['requested' => ['approved', 'rejected'], 'approved' => ['received'], 'received' => ['refunded']];
            if ($return->status === $data['status']) {
                return new CommerceResource($return);
            }abort_unless(in_array($data['status'], $allowed[$return->status] ?? [], true), 409);
            if ($data['status'] === 'refunded') {
                $payment = $return->item->order->payment;
                abort_unless($payment && $payment->status === 'paid', 409);
                $refund = $stripe->refund($payment, $return->quantity * $return->item->unit_price, 'refund-'.$return->reference);
                $return->refund_id = $refund['id'];
                if ($refund['status'] !== 'succeeded') {
                    $data['status'] = 'refund_pending';
                }
            }
            if ($data['status'] === 'received' && ($data['restock'] ?? false) && ! $return->restocked) {
                $variant = ProductVariant::whereKey($return->item->product_variant_id)->lockForUpdate()->firstOrFail();
                $variant->increment('stock', $return->quantity);
                StockMovement::create(['product_variant_id' => $variant->id, 'actor_id' => $request->user()->id, 'delta' => $return->quantity, 'balance' => $variant->fresh()->stock, 'reason' => 'return:'.$return->reference]);
                $return->restocked = true;
            }
            $return->status = $data['status'];
            $return->decision_reason = $data['reason'];
            $return->save();
            $commerce->audit($request->user()->id, 'return.'.$data['status'], 'return', $return->id);

            return new CommerceResource($return);
        });
    }

    public function message(Request $request, ContactMessage $message, CommerceService $commerce): CommerceResource
    {
        $data = $request->validate(['status' => 'required|in:new,handled']);
        $message->update($data);
        $commerce->audit($request->user()->id, 'message.'.$data['status'], 'message', $message->id);

        return new CommerceResource($message);
    }

    public function settings(): JsonResponse
    {
        return response()->json(['data' => app(CommerceSettings::class)->values()]);
    }

    public function detail(Request $request, string $kind, int $id): CommerceResource|UserResource
    {
        $models = ['products' => Product::class, 'returns' => ReturnRequest::class, 'messages' => ContactMessage::class, 'customers' => User::class];
        abort_unless(isset($models[$kind]), 404);
        $item = $models[$kind]::findOrFail($id);
        if ($item instanceof User) {
            return new UserResource($item);
        }
        if ($item instanceof Product) {
            $item->load(['variants', 'images']);
        }

        return new CommerceResource($item);
    }

    public function updateSettings(Request $request, CommerceService $commerce): JsonResponse
    {
        $request->validate(['current_password' => 'required|current_password:web']);
        $data = $request->validate(['enabled' => 'required|boolean', 'currency' => ['required', Rule::in(config('markets.currencies'))], 'countries' => 'required|array|min:1|max:249', 'countries.*' => ['required', Rule::in(array_merge(config('markets.countries'), ['*']))], 'taxes_configured' => 'required|boolean', 'shipping' => 'required|integer|between:0,100000000', 'free_shipping_threshold' => 'required|integer|between:0,100000000', 'returns_days' => 'required|integer|between:0,365']);
        abort_if($data['enabled'] && ! $data['taxes_configured'], 422, 'Validate tax configuration before enabling checkout.');
        $data['prices_include_tax'] = true;
        DB::transaction(function () use ($request, $data, $commerce): void {
            DB::table('commerce_settings')->upsert([['key' => 'commercial', 'value' => json_encode($data, JSON_THROW_ON_ERROR), 'created_at' => now(), 'updated_at' => now()]], ['key'], ['value', 'updated_at']);
            $commerce->audit($request->user()->id, 'settings.updated', 'settings', 'commercial');
        });

        return response()->json(['data' => $data]);
    }
}
