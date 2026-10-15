<?php

declare(strict_types=1);

use App\Domain\Shipping\Contracts\ShipmentRepository;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Jobs\TrackShipmentJob;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use Illuminate\Contracts\Events\Dispatcher;

describe('TrackShipmentJob', function (): void {

    it('creates a tracking event and transitions the shipment status', function (): void {
        $shipmentModel = Shipment::factory()->create();

        (new TrackShipmentJob($shipmentModel->id))->handle(
            app(ShipmentRepository::class),
            app(Dispatcher::class),
        );

        expect(TrackingEvent::where('shipment_id', $shipmentModel->id)->count())
            ->toBe(1);
    });

    it('transitions to in_transit or failed from created status', function (): void {
        $shipmentModel = Shipment::factory()->create();

        (new TrackShipmentJob($shipmentModel->id))->handle(
            app(ShipmentRepository::class),
            app(Dispatcher::class),
        );

        $shipmentModel->refresh();
        expect($shipmentModel->status->value)->toBeIn([
            ShipmentStatus::InTransit->value,
            ShipmentStatus::Failed->value,
        ]);
    });

    it('does nothing when shipment does not exist', function (): void {
        (new TrackShipmentJob('00000000-0000-0000-0000-000000000000'))->handle(
            app(ShipmentRepository::class),
            app(Dispatcher::class),
        );

        expect(TrackingEvent::count())->toBe(0);
    });

    it('does nothing when shipment status is terminal', function (): void {
        $deliveredShipment = Shipment::factory()->delivered()->create();

        (new TrackShipmentJob($deliveredShipment->id))->handle(
            app(ShipmentRepository::class),
            app(Dispatcher::class),
        );

        expect(TrackingEvent::where('shipment_id', $deliveredShipment->id)->count())->toBe(0);

        $failedShipment = Shipment::factory()->failed()->create();

        (new TrackShipmentJob($failedShipment->id))->handle(
            app(ShipmentRepository::class),
            app(Dispatcher::class),
        );

        expect(TrackingEvent::where('shipment_id', $failedShipment->id)->count())->toBe(0);
    });
});
