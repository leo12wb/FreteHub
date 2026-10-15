<?php

declare(strict_types=1);

use App\Jobs\SendWebhookJob;
use App\Models\Shipment;
use App\Models\Store;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\Http;

describe('SendWebhookJob', function (): void {

    it('sends an HTTP request with HMAC signature to the webhook url', function (): void {
        Http::fake(['*' => Http::response(['received' => true], 200)]);

        $store = Store::factory()->withWebhook()->create();
        $shipment = Shipment::factory()->create(['store_id' => $store->id]);

        (new SendWebhookJob(
            storeId: $store->id,
            shipmentId: $shipment->id,
            payload: ['event' => 'tracking.updated', 'status' => 'in_transit'],
        ))->handle();

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_contains($request->url(), (string) $store->webhook_url));
    });

    it('logs a successful webhook delivery', function (): void {
        Http::fake(['*' => Http::response([], 200)]);

        $store = Store::factory()->withWebhook()->create();
        $shipment = Shipment::factory()->create(['store_id' => $store->id]);

        (new SendWebhookJob(
            storeId: $store->id,
            shipmentId: $shipment->id,
            payload: ['event' => 'tracking.updated'],
        ))->handle();

        $this->assertDatabaseHas('webhook_logs', [
            'store_id' => $store->id,
            'shipment_id' => $shipment->id,
            'response_status' => 200,
            'success' => true,
        ]);
    });

    it('logs a failed webhook delivery on non-2xx response', function (): void {
        Http::fake(['*' => Http::response(['error' => 'not found'], 404)]);

        $store = Store::factory()->withWebhook()->create();
        $shipment = Shipment::factory()->create(['store_id' => $store->id]);

        (new SendWebhookJob(
            storeId: $store->id,
            shipmentId: $shipment->id,
            payload: ['event' => 'tracking.updated'],
        ))->handle();

        $this->assertDatabaseHas('webhook_logs', [
            'store_id' => $store->id,
            'response_status' => 404,
            'success' => false,
        ]);
    });

    it('does nothing when store has no webhook url', function (): void {
        Http::fake();

        $store = Store::factory()->create(['webhook_url' => null]);
        $shipment = Shipment::factory()->create(['store_id' => $store->id]);

        (new SendWebhookJob(
            storeId: $store->id,
            shipmentId: $shipment->id,
            payload: [],
        ))->handle();

        Http::assertNothingSent();
        expect(WebhookLog::count())->toBe(0);
    });

    it('does nothing when store does not exist', function (): void {
        Http::fake();

        $shipment = Shipment::factory()->create();

        (new SendWebhookJob(
            storeId: 999999,
            shipmentId: $shipment->id,
            payload: [],
        ))->handle();

        Http::assertNothingSent();
        expect(WebhookLog::count())->toBe(0);
    });
});
