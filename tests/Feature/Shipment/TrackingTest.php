<?php

declare(strict_types=1);

use App\Models\Shipment;
use App\Models\TrackingEvent;

describe('GET /api/v1/shipments/{id}/tracking', function (): void {

    beforeEach(function (): void {
        [$this->store, $this->token] = createAuthenticatedStore();
    });

    it('returns shipment with empty events', function (): void {
        $shipment = Shipment::factory()->create(['store_id' => $this->store->id]);

        $this->withToken($this->token)
            ->getJson("/api/v1/shipments/{$shipment->id}/tracking")
            ->assertStatus(200)
            ->assertJsonStructure(['shipment', 'events'])
            ->assertJsonPath('events', []);
    });

    it('returns shipment with tracking events in order', function (): void {
        $shipment = Shipment::factory()->inTransit()->create(['store_id' => $this->store->id]);

        TrackingEvent::factory()->create([
            'shipment_id' => $shipment->id,
            'status'      => 'created',
            'description' => 'Objeto postado.',
            'occurred_at' => now()->subHours(2),
        ]);

        TrackingEvent::factory()->create([
            'shipment_id' => $shipment->id,
            'status'      => 'in_transit',
            'description' => 'Em trânsito.',
            'occurred_at' => now()->subHour(),
        ]);

        $this->withToken($this->token)
            ->getJson("/api/v1/shipments/{$shipment->id}/tracking")
            ->assertStatus(200)
            ->assertJsonCount(2, 'events')
            ->assertJsonPath('events.0.status', 'created')
            ->assertJsonPath('events.1.status', 'in_transit');
    });

    it('returns 404 for shipment from another store', function (): void {
        $otherShipment = Shipment::factory()->create();

        $this->withToken($this->token)
            ->getJson("/api/v1/shipments/{$otherShipment->id}/tracking")
            ->assertStatus(404);
    });

    it('returns 401 without authentication', function (): void {
        $this->getJson('/api/v1/shipments/fake-id/tracking')->assertStatus(401);
    });
});
