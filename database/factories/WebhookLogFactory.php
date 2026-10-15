<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Store;
use App\Models\WebhookLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebhookLog>
 */
class WebhookLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'shipment_id' => null,
            'payload' => ['event' => 'tracking.updated', 'data' => []],
            'response_status' => 200,
            'attempt' => 1,
            'success' => true,
        ];
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'response_status' => 500,
            'attempt' => 3,
            'success' => false,
        ]);
    }
}
