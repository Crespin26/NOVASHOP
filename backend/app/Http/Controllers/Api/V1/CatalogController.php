<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommerceResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CatalogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate(['q' => 'sometimes|string|max:120', 'category' => 'sometimes|integer|min:1', 'min' => 'sometimes|integer|min:0|max:100000000', 'max' => 'sometimes|integer|min:0|max:100000000', 'stock' => 'sometimes|in:available,unavailable', 'sort' => 'sometimes|in:newest,price-up,price-down', 'page' => 'sometimes|integer|min:1|max:10000', 'per_page' => 'sometimes|integer|min:1|max:50']);
        $query = Product::where('status', 'published')->with(['variants' => fn (Builder|Relation $q): Builder|Relation => $q->where('active', true), 'images']);
        if (isset($data['q'])) {
            $query->whereRaw('(name->>\'fr\' ILIKE ? OR name->>\'en\' ILIKE ?)', array_fill(0, 2, '%'.addcslashes($data['q'], '%_\\').'%'));
        }
        if (isset($data['category'])) {
            $query->where('category_id', $data['category']);
        }
        $query->whereHas('variants', function (Builder $q) use ($data): void {
            $q->where('active', true);
            if (isset($data['min'])) {
                $q->where('price', '>=', $data['min']);
            }
            if (isset($data['max'])) {
                $q->where('price', '<=', $data['max']);
            }
            if (isset($data['stock'])) {
                $q->where('stock', ($data['stock'] === 'available' ? '>' : '='), 0);
            }
        });
        if (in_array($data['sort'] ?? '', ['price-up', 'price-down'], true)) {
            $query->withMin(['variants' => fn (Builder|Relation $q): Builder|Relation => $q->where('active', true)], 'price')->orderBy('variants_min_price', $data['sort'] === 'price-up' ? 'asc' : 'desc');
        } else {
            $query->orderByDesc('published_at');
        }

        return CommerceResource::collection($query->orderBy('id')->paginate($data['per_page'] ?? 12)->withQueryString());
    }

    public function show(string $slug): CommerceResource
    {
        return new CommerceResource(Product::where('slug', $slug)->where('status', 'published')->with(['variants' => fn (Builder|Relation $q): Builder|Relation => $q->where('active', true), 'images'])->firstOrFail());
    }

    public function categories(): AnonymousResourceCollection
    {
        return CommerceResource::collection(Category::orderBy('position')->orderBy('id')->paginate(50));
    }

    public function image(ProductImage $image): StreamedResponse
    {
        abort_unless($image->product->status === 'published' || request()->user()?->is_admin, 404);
        abort_unless(Storage::disk('local')->exists($image->path), 404);

        return Storage::disk('local')->response($image->path, null, ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, max-age=300']);
    }
}
