<?php

declare(strict_types=1);

use App\Domain\Shipping\Contracts\QuoteRepository;
use App\Domain\Shipping\Contracts\ShipmentRepository;
use App\Models\Quote;
use App\Models\Shipment;
use App\Models\Store;

describe('EloquentQuoteRepository', function (): void {

    it('findByStoreId returns all quotes for a store', function (): void {
        $store = Store::factory()->create();
        Quote::factory()->pending()->count(3)->create(['store_id' => $store->id]);
        Quote::factory()->pending()->create(); // belongs to another store

        $quotes = app(QuoteRepository::class)->findByStoreId((string) $store->id);

        expect($quotes)->toHaveCount(3);
    });

    it('findByStoreId returns empty array when store has no quotes', function (): void {
        $store = Store::factory()->create();

        $quotes = app(QuoteRepository::class)->findByStoreId((string) $store->id);

        expect($quotes)->toHaveCount(0);
    });

    it('delete removes the quote from the database', function (): void {
        $store = Store::factory()->create();
        $quote = Quote::factory()->pending()->create(['store_id' => $store->id]);

        app(QuoteRepository::class)->delete($quote->id);

        $this->assertDatabaseMissing('quotes', ['id' => $quote->id]);
    });
});

describe('EloquentShipmentRepository', function (): void {

    it('findByTrackingCode returns the matching shipment', function (): void {
        $shipment = Shipment::factory()->create();

        $found = app(ShipmentRepository::class)->findByTrackingCode($shipment->tracking_code);

        expect($found)->not->toBeNull()
            ->and($found?->id)->toBe($shipment->id);
    });

    it('findByTrackingCode returns null for unknown tracking code', function (): void {
        $result = app(ShipmentRepository::class)->findByTrackingCode('XX00000000XX');

        expect($result)->toBeNull();
    });

    it('findActiveShipments returns only non-terminal shipments', function (): void {
        Shipment::factory()->create(); // Created (active)
        Shipment::factory()->inTransit()->create(); // InTransit (active)
        Shipment::factory()->delivered()->create(); // terminal
        Shipment::factory()->failed()->create(); // terminal

        $active = app(ShipmentRepository::class)->findActiveShipments();

        expect($active)->toHaveCount(2);

        foreach ($active as $shipment) {
            expect($shipment->status->isTerminal())->toBeFalse();
        }
    });

    it('findActiveShipments returns empty array when all shipments are terminal', function (): void {
        Shipment::factory()->delivered()->create();
        Shipment::factory()->failed()->create();

        $active = app(ShipmentRepository::class)->findActiveShipments();

        expect($active)->toHaveCount(0);
    });
});
