<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Entities;

use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Exceptions\InvalidShipmentTransitionException;
use DateTimeImmutable;

final class Shipment
{
    public function __construct(
        public readonly string $id,
        public readonly string $quoteId,
        public readonly string $storeId,
        public readonly string $carrier,
        public readonly string $service,
        public readonly string $trackingCode,
        public readonly ShipmentStatus $status,
        public readonly ?string $labelUrl,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public function isDelivered(): bool
    {
        return $this->status === ShipmentStatus::Delivered;
    }

    public function isFailed(): bool
    {
        return $this->status === ShipmentStatus::Failed;
    }

    public function isInTransit(): bool
    {
        return $this->status === ShipmentStatus::InTransit;
    }

    public function transitionTo(ShipmentStatus $newStatus): self
    {
        if (! $this->status->canTransitionTo($newStatus)) {
            throw new InvalidShipmentTransitionException(
                "Transição inválida: {$this->status->value} → {$newStatus->value}."
            );
        }

        return new self(
            id: $this->id,
            quoteId: $this->quoteId,
            storeId: $this->storeId,
            carrier: $this->carrier,
            service: $this->service,
            trackingCode: $this->trackingCode,
            status: $newStatus,
            labelUrl: $this->labelUrl,
            createdAt: $this->createdAt,
        );
    }
}
