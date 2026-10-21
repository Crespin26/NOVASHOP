<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ContactMessage> */
class ContactMessageFactory extends Factory
{
    protected $model = ContactMessage::class;

    public function definition(): array
    {
        return ['name' => 'Client Test', 'email' => fake()->safeEmail(), 'subject' => 'other', 'message' => 'Message de test', 'locale' => 'fr'];
    }
}
