<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Shipping\Contracts\ShipmentRepository;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Events\TrackingUpdated;
use App\Models\TrackingEvent;
use DateTimeImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class TrackShipmentJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly string $shipmentId,
    ) {}

    public function handle(ShipmentRepository $shipmentRepository, Dispatcher $dispatcher): void
    {
        $shipment = $shipmentRepository->findById($this->shipmentId);

        if ($shipment === null || $shipment->status->isTerminal()) {
            return;
        }

        $nextStatus = $this->resolveNextStatus($shipment->status);
        $description = $this->descriptionFor($nextStatus);
        $location = $this->randomLocation();

        TrackingEvent::create([
            'shipment_id' => $shipment->id,
            'status' => $nextStatus->value,
            'description' => $description,
            'location' => $location,
            'occurred_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        $updated = $shipment->transitionTo($nextStatus);
        $previousStatus = $shipment->status;

        $shipmentRepository->save($updated);
        $dispatcher->dispatch(new TrackingUpdated(
            shipment: $updated,
            previousStatus: $previousStatus,
            description: $description,
            location: $location,
        ));
    }

    private function resolveNextStatus(ShipmentStatus $current): ShipmentStatus
    {
        return match ($current) {
            ShipmentStatus::Created => mt_rand(1, 10) <= 9
                ? ShipmentStatus::InTransit
                : ShipmentStatus::Failed,
            ShipmentStatus::InTransit => mt_rand(1, 10) <= 8
                ? ShipmentStatus::Delivered
                : ShipmentStatus::Failed,
            default => $current,
        };
    }

    private function descriptionFor(ShipmentStatus $status): string
    {
        return match ($status) {
            ShipmentStatus::InTransit => 'Objeto postado e em trânsito.',
            ShipmentStatus::Delivered => 'Objeto entregue ao destinatário.',
            ShipmentStatus::Failed => 'Tentativa de entrega malsucedida.',
            default => 'Status atualizado.',
        };
    }

    private function randomLocation(): string
    {
        $locations = [
            'São Paulo, SP',
            'Rio de Janeiro, RJ',
            'Curitiba, PR',
            'Belo Horizonte, MG',
            'Salvador, BA',
            'Fortaleza, CE',
            'Manaus, AM',
        ];

        return $locations[array_rand($locations)];
    }
}
