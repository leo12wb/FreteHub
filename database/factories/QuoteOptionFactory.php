<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Quote;
use App\Models\QuoteOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuoteOption>
 */
class QuoteOptionFactory extends Factory
{
    private static array $carriers = [
        ['carrier' => 'Correios', 'service' => 'PAC'],
        ['carrier' => 'Correios', 'service' => 'SEDEX'],
        ['carrier' => 'JadLog',   'service' => 'Econômico'],
        ['carrier' => 'Total Express', 'service' => 'Standard'],
    ];

    public function definition(): array
    {
        $carrier = fake()->randomElement(self::$carriers);

        return [
            'quote_id' => Quote::factory(),
            'carrier' => $carrier['carrier'],
            'service' => $carrier['service'],
            'price_cents' => fake()->numberBetween(1000, 8000),
            'delivery_days' => fake()->numberBetween(1, 10),
        ];
    }
}
