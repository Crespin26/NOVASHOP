<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Address> */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return ['user_id' => User::factory(), 'label' => 'Maison', 'name' => 'Client Test', 'street' => '1 rue de test', 'city' => 'Cotonou', 'postal' => '00000', 'country' => 'BJ', 'is_default' => true];
    }
}
