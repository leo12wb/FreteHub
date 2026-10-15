<?php

declare(strict_types=1);

use App\Domain\Shipping\Entities\Shipment as ShipmentEntity;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Events\TrackingUpdated;
use App\Listeners\SendWebhookOnTrackingUpdated;
use App\Models\Shipment;
use App\Models\Store;
use App\Models\WebhookLog;
use DateTimeImmutable;
use Illuminate\Support\Facades\Http;

describe('SendWebhookOnTrackingUpdated', function (): void {

    it('dispatches SendWebhookJob when store has a webhook configured', function (): void {
        Http::fake(['*' => Http::response([], 200)]);

        $store = Store::factory()->withWebhook()->create();
        $shipmentModel = Shipment::factory()->create(['store_id' => $store->id]);

        $shipmentEntity = new ShipmentEntity(
            id: $shipmentModel->id,
            quoteId: (string) $shipmentModel->quote_id,
            storeId: (string) $store->id,
            carrier: $shipmentModel->carrier,
            service: $shipmentModel->service,
            trackingCode: $shipmentModel->tracking_code,
            status: ShipmentStatus::InTransit,
            labelUrl: null,
            createdAt: new DateTimeImmutable(),
        );

        $event = new TrackingUpdated(
            shipment: $shipmentEntity,
            previousStatus: ShipmentStatus::Created,
            description: 'Objeto em trânsito.',
            location: 'São Paulo, SP',
        );

        (new SendWebhookOnTrackingUpdated())->handle($event);

        expect(WebhookLog::where('store_id', $store->id)->count())->toBe(1);
    });

    it('does nothing when store has no webhook url', function (): void {
        Http::fake();

        $store = Store::factory()->create(['webhook_url' => null]);
        $shipmentModel = Shipment::factory()->create(['store_id' => $store->id]);

        $shipmentEntity = new ShipmentEntity(
            id: $shipmentModel->id,
            quoteId: (string) $shipmentModel->quote_id,
            storeId: (string) $store->id,
            carrier: $shipmentModel->carrier,
            service: $shipmentModel->service,
            trackingCode: $shipmentModel->tracking_code,
            status: ShipmentStatus::InTransit,
            labelUrl: null,
            createdAt: new DateTimeImmutable(),
        );

        $event = new TrackingUpdated(
            shipment: $shipmentEntity,
            previousStatus: ShipmentStatus::Created,
            description: 'Objeto em trânsito.',
        );

        (new SendWebhookOnTrackingUpdated())->handle($event);

        Http::assertNothingSent();
        expect(WebhookLog::count())->toBe(0);
    });
});
