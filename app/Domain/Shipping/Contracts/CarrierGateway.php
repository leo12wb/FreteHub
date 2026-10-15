<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Contracts;

use App\Domain\Shipping\ValueObjects\Address;
use App\Domain\Shipping\ValueObjects\CarrierRateResult;
use App\Domain\Shipping\ValueObjects\Dimensions;
use App\Domain\Shipping\ValueObjects\Weight;

interface CarrierGateway
{
    public function calculateRate(
        Address $origin,
        Address $destination,
        Weight $weight,
        Dimensions $dimensions,
    ): CarrierRateResult;

    public function getCarrierName(): string;

    public function getServiceName(): string;
}
