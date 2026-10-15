<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shipment;
use App\Models\TrackingEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrackingEvent>
 */
class TrackingEventFactory extends Factory
{
    private static array $descriptions = [
        'created' => 'Objeto postado',
        'in_transit' => 'Objeto em trânsito para destino',
        'delivered' => 'Objeto entregue ao destinatário',
        'failed' => 'Tentativa de entrega não realizada',
    ];

    public function definition(): array
    {
        $status = fake()->randomElement(array_keys(self::$descriptions));

        return [
            'shipment_id' => Shipment::factory(),
            'status' => $status,
            'description' => self::$descriptions[$status],
            'location' => fake()->city().' - '.fake()->stateAbbr(),
            'occurred_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
