<?php

declare(strict_types=1);

namespace App\Application\UseCases\GetTracking;

use App\Domain\Shipping\Contracts\ShipmentRepository;
use App\Domain\Shipping\Entities\Shipment;
use App\Domain\Shipping\Exceptions\ShipmentNotFoundException;
use App\Models\TrackingEvent;

final class GetTrackingUseCase
{
    public function __construct(
        private readonly ShipmentRepository $shipmentRepository,
    ) {}

    /**
     * @return array{shipment: Shipment, events: TrackingEvent[]}
     */
    public function execute(string $shipmentId, string $storeId): array
    {
        $shipment = $this->shipmentRepository->findByIdAndStore($shipmentId, $storeId);

        if ($shipment === null) {
            throw new ShipmentNotFoundException("Envio \"{$shipmentId}\" não encontrado.");
        }

        /** @var TrackingEvent[] $events */
        $events = TrackingEvent::where('shipment_id', $shipmentId)
            ->orderBy('occurred_at')
            ->get()
            ->all();

        return compact('shipment', 'events');
    }
}
