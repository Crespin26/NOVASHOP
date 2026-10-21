<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalogue_hides_drafts_and_supports_pagination_and_filters(): void
    {
        $public = Product::factory()->create(['status' => 'published']);
        ProductVariant::factory()->create(['product_id' => $public->id]);
        $draft = Product::factory()->create();
        ProductVariant::factory()->create(['product_id' => $draft->id]);
        $this->getJson('/api/v1/products?min=8000&stock=available')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $public->id);
        $this->getJson('/api/v1/products/'.$draft->slug)->assertNotFound();
        $this->getJson('/api/v1/products?sort=invalid')->assertUnprocessable();
        $this->getJson('/api/v1/products?per_page=500')->assertUnprocessable();
    }
}
