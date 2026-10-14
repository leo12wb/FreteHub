<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Events;

use App\Domain\Shipping\Entities\Shipment;

final readonly class ShipmentCreated
{
    public function __construct(
        public Shipment $shipment,
    ) {}
}
