<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Shipping\Contracts\ShipmentRepository;
use App\Domain\Shipping\Entities\Shipment;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Models\Shipment as ShipmentModel;
use DateTimeImmutable;

final class EloquentShipmentRepository implements ShipmentRepository
{
    public function save(Shipment $shipment): void
    {
        ShipmentModel::updateOrCreate(
            ['id' => $shipment->id],
            [
                'quote_id' => $shipment->quoteId,
                'store_id' => $shipment->storeId,
                'carrier' => $shipment->carrier,
                'service' => $shipment->service,
                'tracking_code' => $shipment->trackingCode,
                'status' => $shipment->status->value,
                'label_url' => $shipment->labelUrl,
            ]
        );
    }

    public function findById(string $id): ?Shipment
    {
        $model = ShipmentModel::find($id);

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findByIdAndStore(string $id, string $storeId): ?Shipment
    {
        $model = ShipmentModel::where('id', $id)
            ->where('store_id', $storeId)
            ->first();

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findByTrackingCode(string $trackingCode): ?Shipment
    {
        $model = ShipmentModel::where('tracking_code', $trackingCode)->first();

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    /** @return Shipment[] */
    public function findActiveShipments(): array
    {
        return ShipmentModel::whereNotIn('status', [
            ShipmentStatus::Delivered->value,
            ShipmentStatus::Failed->value,
        ])
            ->get()
            ->map(fn (ShipmentModel $m) => $this->toDomain($m))
            ->all();
    }

    private function toDomain(ShipmentModel $model): Shipment
    {
        return new Shipment(
            id: $model->id,
            quoteId: (string) $model->quote_id,
            storeId: (string) $model->store_id,
            carrier: $model->carrier,
            service: $model->service,
            trackingCode: $model->tracking_code,
            status: ShipmentStatus::from((string) $model->getRawOriginal('status')),
            labelUrl: $model->label_url,
            createdAt: new DateTimeImmutable($model->created_at?->toDateTimeString() ?? now()->toDateTimeString()),
        );
    }
}
