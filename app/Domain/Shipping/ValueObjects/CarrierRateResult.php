<?php

declare(strict_types=1);

namespace App\Domain\Shipping\ValueObjects;

final readonly class CarrierRateResult
{
    public function __construct(
        public string $carrier,
        public string $service,
        public Money  $price,
        public int    $deliveryDays,
    ) {}

    public function isCheaperThan(self $other): bool
    {
        return $this->price->isGreaterThan($other->price) === false
            && $this->price->equals($other->price) === false;
    }

    /** @return array<string, string|int|float> */
    public function toArray(): array
    {
        return [
            'carrier'       => $this->carrier,
            'service'       => $this->service,
            'price'         => $this->price->toFloat(),
            'delivery_days' => $this->deliveryDays,
        ];
    }
}
