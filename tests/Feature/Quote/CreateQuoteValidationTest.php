<?php

declare(strict_types=1);

describe('POST /api/v1/quotes — validation', function (): void {

    beforeEach(function (): void {
        [$this->store, $this->token] = createAuthenticatedStore();
    });

    it('requires all fields', function (): void {
        $this->withToken($this->token)
            ->postJson('/api/v1/quotes', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'origin_zipcode',
                'destination_zipcode',
                'weight_kg',
                'length_cm',
                'width_cm',
                'height_cm',
                'declared_value',
            ]);
    });

    it('rejects invalid zipcode format', function (): void {
        $this->withToken($this->token)
            ->postJson('/api/v1/quotes', [
                'origin_zipcode'      => 'INVALID',
                'destination_zipcode' => '20040-020',
                'weight_kg'           => 2.0,
                'length_cm'           => 20,
                'width_cm'            => 15,
                'height_cm'           => 10,
                'declared_value'      => 100,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['origin_zipcode']);
    });

    it('rejects weight above 30kg', function (): void {
        $this->withToken($this->token)
            ->postJson('/api/v1/quotes', [
                'origin_zipcode'      => '01310-100',
                'destination_zipcode' => '20040-020',
                'weight_kg'           => 31.0,
                'length_cm'           => 20,
                'width_cm'            => 15,
                'height_cm'           => 10,
                'declared_value'      => 100,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['weight_kg']);
    });

    it('rejects zero or negative dimensions', function (): void {
        $this->withToken($this->token)
            ->postJson('/api/v1/quotes', [
                'origin_zipcode'      => '01310-100',
                'destination_zipcode' => '20040-020',
                'weight_kg'           => 2.0,
                'length_cm'           => 0,
                'width_cm'            => -5,
                'height_cm'           => 10,
                'declared_value'      => 100,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['length_cm', 'width_cm']);
    });
});
