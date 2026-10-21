<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ReturnRequest> */
class ReturnRequestFactory extends Factory
{
    protected $model = ReturnRequest::class;

    public function definition(): array
    {
        return ['user_id' => User::factory(), 'order_item_id' => OrderItem::factory(), 'reference' => (string) Str::uuid(), 'quantity' => 1, 'reason' => 'Retour de test'];
    }
}
