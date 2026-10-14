<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Entities;

use App\Domain\Shipping\Enums\QuoteStatus;
use App\Domain\Shipping\ValueObjects\Address;
use App\Domain\Shipping\ValueObjects\CarrierRateResult;
use App\Domain\Shipping\ValueObjects\Dimensions;
use App\Domain\Shipping\ValueObjects\Money;
use App\Domain\Shipping\ValueObjects\Weight;
use DateTimeImmutable;

final class Quote
{
    /** @param CarrierRateResult[] $options */
    public function __construct(
        public readonly string            $id,
        public readonly string            $storeId,
        public readonly Address           $origin,
        public readonly Address           $destination,
        public readonly Weight            $weight,
        public readonly Dimensions        $dimensions,
        public readonly Money             $declaredValue,
        public readonly QuoteStatus       $status,
        public readonly DateTimeImmutable $expiresAt,
        public readonly array             $options = [],
    ) {}

    public function isExpired(): bool
    {
        return $this->expiresAt < new DateTimeImmutable();
    }

    public function isPending(): bool
    {
        return $this->status === QuoteStatus::Pending;
    }

    public function isConfirmed(): bool
    {
        return $this->status === QuoteStatus::Confirmed;
    }

    public function confirm(): self
    {
        return new self(
            id:            $this->id,
            storeId:       $this->storeId,
            origin:        $this->origin,
            destination:   $this->destination,
            weight:        $this->weight,
            dimensions:    $this->dimensions,
            declaredValue: $this->declaredValue,
            status:        QuoteStatus::Confirmed,
            expiresAt:     $this->expiresAt,
            options:       $this->options,
        );
    }

    public function expire(): self
    {
        return new self(
            id:            $this->id,
            storeId:       $this->storeId,
            origin:        $this->origin,
            destination:   $this->destination,
            weight:        $this->weight,
            dimensions:    $this->dimensions,
            declaredValue: $this->declaredValue,
            status:        QuoteStatus::Expired,
            expiresAt:     $this->expiresAt,
            options:       $this->options,
        );
    }

    /** @return CarrierRateResult[] */
    public function optionsSortedByPrice(): array
    {
        $sorted = $this->options;
        usort($sorted, fn (CarrierRateResult $a, CarrierRateResult $b) =>
            $a->price->amountInCents <=> $b->price->amountInCents
        );

        return $sorted;
    }

    public function findOption(string $carrier, string $service): ?CarrierRateResult
    {
        foreach ($this->options as $option) {
            if ($option->carrier === $carrier && $option->service === $service) {
                return $option;
            }
        }

        return null;
    }
}
