<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StockMovement> */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return ['product_variant_id' => ProductVariant::factory(), 'delta' => 1, 'balance' => 1, 'reason' => 'Test adjustment'];
    }
}
