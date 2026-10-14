<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Models\Quote;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shipment>
 */
class ShipmentFactory extends Factory
{
    public function definition(): array
    {
        $store = Store::factory()->create();
        $quote = Quote::factory()->confirmed()->create(['store_id' => $store->id]);

        return [
            'quote_id'      => $quote->id,
            'store_id'      => $store->id,
            'carrier'       => fake()->randomElement(['Correios', 'JadLog', 'Total Express']),
            'service'       => fake()->randomElement(['PAC', 'SEDEX', 'Econômico', 'Standard']),
            'tracking_code' => $this->generateTrackingCode(),
            'status'        => ShipmentStatus::Created,
            'label_url'     => null,
        ];
    }

    public function inTransit(): static
    {
        return $this->state(fn () => ['status' => ShipmentStatus::InTransit]);
    }

    public function delivered(): static
    {
        return $this->state(fn () => ['status' => ShipmentStatus::Delivered]);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => ShipmentStatus::Failed]);
    }

    private function generateTrackingCode(): string
    {
        return strtoupper(Str::random(2))
            . str_pad((string) rand(10000000, 99999999), 8, '0')
            . strtoupper(Str::random(2));
    }
}
