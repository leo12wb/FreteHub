<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Events;

use App\Domain\Shipping\Entities\Shipment;
use App\Domain\Shipping\Enums\ShipmentStatus;

final readonly class TrackingUpdated
{
    public function __construct(
        public Shipment $shipment,
        public ShipmentStatus $previousStatus,
        public string $description,
        public ?string $location = null,
    ) {}
}
