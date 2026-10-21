<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return ['category_id' => Category::factory(), 'slug' => fake()->unique()->slug(3), 'name' => ['fr' => 'Produit', 'en' => 'Product'], 'description' => ['fr' => 'Description produit', 'en' => 'Product description'], 'brand' => 'NovaShop', 'status' => 'draft'];
    }
}
