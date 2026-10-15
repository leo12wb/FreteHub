<?php

declare(strict_types=1);

use App\Models\Quote;
use App\Models\QuoteOption;
use Illuminate\Support\Facades\Cache;

describe('POST /api/v1/shipments', function (): void {

    beforeEach(function (): void {
        Cache::flush();
        [$this->store, $this->token] = createAuthenticatedStore();
    });

    it('confirms a shipment from a valid quote', function (): void {
        $quote = Quote::factory()->pending()->create(['store_id' => $this->store->id]);
        QuoteOption::factory()->create([
            'quote_id' => $quote->id,
            'carrier'  => 'Correios',
            'service'  => 'PAC',
        ]);

        $this->withToken($this->token)
            ->postJson('/api/v1/shipments', [
                'quote_id' => $quote->id,
                'carrier'  => 'Correios',
                'service'  => 'PAC',
            ])
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'quote_id', 'carrier', 'service', 'tracking_code', 'status'],
            ])
            ->assertJsonPath('data.status', 'created')
            ->assertJsonPath('data.carrier', 'Correios');
    });

    it('persists shipment in the database', function (): void {
        $quote = Quote::factory()->pending()->create(['store_id' => $this->store->id]);
        QuoteOption::factory()->create([
            'quote_id' => $quote->id,
            'carrier'  => 'JadLog',
            'service'  => 'Expresso',
        ]);

        $this->withToken($this->token)
            ->postJson('/api/v1/shipments', [
                'quote_id' => $quote->id,
                'carrier'  => 'JadLog',
                'service'  => 'Expresso',
            ]);

        $this->assertDatabaseHas('shipments', [
            'quote_id' => $quote->id,
            'carrier'  => 'JadLog',
            'service'  => 'Expresso',
        ]);

        $this->assertDatabaseHas('quotes', [
            'id'     => $quote->id,
            'status' => 'confirmed',
        ]);
    });

    it('returns 422 for expired quote', function (): void {
        $quote = Quote::factory()->expiredAt()->create(['store_id' => $this->store->id]);
        QuoteOption::factory()->create([
            'quote_id' => $quote->id,
            'carrier'  => 'Correios',
            'service'  => 'PAC',
        ]);

        $this->withToken($this->token)
            ->postJson('/api/v1/shipments', [
                'quote_id' => $quote->id,
                'carrier'  => 'Correios',
                'service'  => 'PAC',
            ])
            ->assertStatus(422);
    });

    it('returns 404 for quote from another store', function (): void {
        $otherQuote = Quote::factory()->pending()->create();
        QuoteOption::factory()->create([
            'quote_id' => $otherQuote->id,
            'carrier'  => 'Correios',
            'service'  => 'PAC',
        ]);

        $this->withToken($this->token)
            ->postJson('/api/v1/shipments', [
                'quote_id' => $otherQuote->id,
                'carrier'  => 'Correios',
                'service'  => 'PAC',
            ])
            ->assertStatus(404);
    });

    it('returns 401 without authentication', function (): void {
        $this->postJson('/api/v1/shipments', [])->assertStatus(401);
    });
});
