<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return ['user_id' => User::factory(), 'reference' => (string) Str::uuid(), 'idempotency_key' => (string) Str::uuid(), 'request_hash' => hash('sha256', 'test'), 'currency' => 'EUR', 'subtotal' => 8900, 'shipping' => 490, 'total' => 9390, 'address_snapshot' => ['country' => 'BJ'], 'locale' => 'fr', 'expires_at' => now()->addMinutes(45)];
    }
}
