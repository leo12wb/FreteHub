<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Exceptions;

use RuntimeException;

final class ShipmentNotFoundException extends RuntimeException
{
    public function __construct(string $shipmentId)
    {
        parent::__construct("Envio \"{$shipmentId}\" não encontrado.");
    }
}
