<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Sample data is restricted to local and testing environments.');
        }
        if (Category::exists()) {
            return;
        }
        $category = Category::factory()->create(['slug' => 'high-tech', 'name' => ['fr' => 'High-tech', 'en' => 'Tech']]);
        Product::factory()->count(3)->create(['category_id' => $category->id])->each(fn (Product $product) => ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => 0]));
    }
}
