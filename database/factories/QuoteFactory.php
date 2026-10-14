<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Shipping\Enums\QuoteStatus;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quote>
 */
class QuoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'store_id'              => Store::factory(),
            'origin_zipcode'        => $this->fakeZipcode(),
            'destination_zipcode'   => $this->fakeZipcode(),
            'weight_kg'             => fake()->randomFloat(3, 0.1, 30),
            'dimensions'            => [
                'length_cm' => fake()->numberBetween(10, 100),
                'width_cm'  => fake()->numberBetween(10, 100),
                'height_cm' => fake()->numberBetween(5, 60),
            ],
            'declared_value_cents'  => fake()->numberBetween(1000, 100000),
            'status'                => QuoteStatus::Pending,
            'expires_at'            => now()->addHour(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status'     => QuoteStatus::Pending,
            'expires_at' => now()->addHour(),
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => QuoteStatus::Confirmed]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status'     => QuoteStatus::Expired,
            'expires_at' => now()->subHour(),
        ]);
    }

    public function expiredAt(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subMinutes(5)]);
    }

    private function fakeZipcode(): string
    {
        return str_pad((string) fake()->numberBetween(1000000, 99999999), 8, '0', STR_PAD_LEFT);
    }
}
