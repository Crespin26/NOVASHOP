<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderItem> */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return ['order_id' => Order::factory(), 'product_variant_id' => ProductVariant::factory(), 'sku' => fake()->uuid(), 'name' => ['fr' => 'Produit', 'en' => 'Product'], 'quantity' => 1, 'unit_price' => 8900, 'total' => 8900];
    }
}
