<?php

namespace Database\Factories;

use App\Models\NewsletterSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NewsletterSubscription> */
class NewsletterSubscriptionFactory extends Factory
{
    protected $model = NewsletterSubscription::class;

    public function definition(): array
    {
        return ['email' => fake()->unique()->safeEmail(), 'locale' => 'fr', 'token_hash' => hash('sha256', fake()->uuid()), 'token_expires_at' => now()->addHour()];
    }
}
