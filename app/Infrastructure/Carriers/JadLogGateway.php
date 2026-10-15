<?php

declare(strict_types=1);

namespace App\Infrastructure\Carriers;

use App\Domain\Shipping\Contracts\CarrierGateway;
use App\Domain\Shipping\Exceptions\CarrierUnavailableException;
use App\Domain\Shipping\ValueObjects\Address;
use App\Domain\Shipping\ValueObjects\CarrierRateResult;
use App\Domain\Shipping\ValueObjects\Dimensions;
use App\Domain\Shipping\ValueObjects\Money;
use App\Domain\Shipping\ValueObjects\Weight;

final class JadLogGateway implements CarrierGateway
{
    private const BASE_PRICE_PER_KG = 4.20;

    private const BASE_FIXED_FEE = 6.50;

    private const BASE_DELIVERY_DAYS = 5;

    private const VARIATION_PERCENT = 0.10;

    public function calculateRate(
        Address $origin,
        Address $destination,
        Weight $weight,
        Dimensions $dimensions,
    ): CarrierRateResult {
        $this->simulateFailure();

        $basePrice = ($weight->kg * self::BASE_PRICE_PER_KG) + self::BASE_FIXED_FEE;
        $price = $basePrice * $this->priceVariation();
        $days = (int) round(self::BASE_DELIVERY_DAYS * $this->daysVariation());

        return new CarrierRateResult(
            carrier: $this->getCarrierName(),
            service: $this->getServiceName(),
            price: Money::fromFloat(round($price, 2)),
            deliveryDays: max(1, $days),
        );
    }

    public function getCarrierName(): string
    {
        return 'JadLog';
    }

    public function getServiceName(): string
    {
        return 'Expresso';
    }

    private function simulateFailure(): void
    {
        $rate = (int) config('shipping.carrier_failure_rate', 5);
        if ($rate > 0 && mt_rand(1, 100) <= $rate) {
            throw new CarrierUnavailableException('JadLog indisponível no momento.');
        }
    }

    private function priceVariation(): float
    {
        return 1 + (mt_rand(-10, 10) / 100);
    }

    private function daysVariation(): float
    {
        return 1 + (mt_rand(
            (int) (-self::VARIATION_PERCENT * 100),
            (int) (self::VARIATION_PERCENT * 100),
        ) / 100);
    }
}
