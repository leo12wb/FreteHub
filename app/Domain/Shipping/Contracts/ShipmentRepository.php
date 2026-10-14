<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Contracts;

use App\Domain\Shipping\Entities\Shipment;

interface ShipmentRepository
{
    public function save(Shipment $shipment): void;

    public function findById(string $id): ?Shipment;

    public function findByIdAndStore(string $id, string $storeId): ?Shipment;

    public function findByTrackingCode(string $trackingCode): ?Shipment;

    /** @return Shipment[] */
    public function findActiveShipments(): array;
}
