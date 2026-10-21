<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommerceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof Product) {
            return ['id' => $this->id, 'slug' => $this->slug, 'category_id' => $this->category_id, 'name' => $this->name, 'description' => $this->description, 'specifications' => $this->specifications, 'brand' => $this->brand, 'prices_include_tax' => true, 'published_at' => $this->published_at, 'variants' => $this->whenLoaded('variants'), 'images' => $this->whenLoaded('images', fn () => $this->images->sortBy('position')->map(fn ($image) => ['id' => $image->id, 'url' => route('api.v1.images.show', $image), 'alt' => $image->alt, 'position' => $image->position])->values())];
        }
        if ($this->resource instanceof Order) {
            return ['reference' => $this->reference, 'status' => $this->status, 'currency' => $this->currency, 'subtotal' => $this->subtotal, 'shipping' => $this->shipping, 'total' => $this->total, 'address' => $this->address_snapshot, 'expires_at' => $this->expires_at, 'paid_at' => $this->paid_at, 'carrier' => $this->carrier, 'tracking_number' => $this->tracking_number, 'items' => $this->whenLoaded('items'), 'created_at' => $this->created_at];
        }

        return collect(parent::toArray($request))->except(['user_id', 'token_hash', 'request_hash', 'idempotency_key', 'password', 'remember_token', 'checkout_url'])->all();
    }
}
