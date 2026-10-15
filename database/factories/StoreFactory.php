<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'webhook_url' => null,
            'webhook_secret' => null,
        ];
    }

    public function withWebhook(): static
    {
        return $this->state(fn () => [
            'webhook_url' => fake()->url(),
            'webhook_secret' => Str::random(32),
        ]);
    }
}
