<?php

declare(strict_types=1);

namespace App\Application\UseCases\ConfirmShipment;

use App\Domain\Shipping\Entities\Shipment;

final readonly class ConfirmShipmentResponse
{
    public function __construct(
        public Shipment $shipment,
    ) {}
}
