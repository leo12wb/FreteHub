<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;

describe('POST /api/v1/quotes', function (): void {

    beforeEach(function (): void {
        Cache::flush();
        [$this->store, $this->token] = createAuthenticatedStore();
    });

    it('creates a quote and returns options', function (): void {
        $this->withToken($this->token)
            ->postJson('/api/v1/quotes', [
                'origin_zipcode' => '01310-100',
                'destination_zipcode' => '20040-020',
                'weight_kg' => 2.0,
                'length_cm' => 20,
                'width_cm' => 15,
                'height_cm' => 10,
                'declared_value' => 100.00,
            ])
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'status', 'expires_at',
                    'options' => [['carrier', 'service', 'price', 'delivery_days']],
                ],
            ])
            ->assertJsonPath('data.status', 'pending');
    });

    it('returns X-From-Cache false on first request', function (): void {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/quotes', [
                'origin_zipcode' => '01310-100',
                'destination_zipcode' => '20040-020',
                'weight_kg' => 1.0,
                'length_cm' => 10,
                'width_cm' => 10,
                'height_cm' => 10,
                'declared_value' => 50.00,
            ]);

        expect($response->headers->get('X-From-Cache'))->toBe('false');
    });

    it('returns X-From-Cache true on second identical request', function (): void {
        $payload = [
            'origin_zipcode' => '01310-100',
            'destination_zipcode' => '20040-020',
            'weight_kg' => 1.5,
            'length_cm' => 12,
            'width_cm' => 12,
            'height_cm' => 12,
            'declared_value' => 75.00,
        ];

        $this->withToken($this->token)->postJson('/api/v1/quotes', $payload);
        $second = $this->withToken($this->token)->postJson('/api/v1/quotes', $payload);

        expect($second->headers->get('X-From-Cache'))->toBe('true');
    });

    it('returns 401 without authentication', function (): void {
        $this->postJson('/api/v1/quotes', [])->assertStatus(401);
    });

    it('persists quote in the database', function (): void {
        $this->withToken($this->token)
            ->postJson('/api/v1/quotes', [
                'origin_zipcode' => '01310-100',
                'destination_zipcode' => '20040-020',
                'weight_kg' => 2.0,
                'length_cm' => 20,
                'width_cm' => 15,
                'height_cm' => 10,
                'declared_value' => 100.00,
            ]);

        $this->assertDatabaseHas('quotes', [
            'store_id' => $this->store->id,
            'origin_zipcode' => '01310100',
            'destination_zipcode' => '20040020',
        ]);
    });
});
