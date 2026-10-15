<?php

declare(strict_types=1);

namespace App\Application\UseCases\ConfirmShipment;

final readonly class ConfirmShipmentDTO
{
    public function __construct(
        public string $quoteId,
        public string $carrier,
        public string $service,
        public string $storeId,
    ) {}
}
