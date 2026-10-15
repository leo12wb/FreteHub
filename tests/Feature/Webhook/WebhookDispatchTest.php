<?php

declare(strict_types=1);

use App\Models\Store;

describe('Webhook endpoints', function (): void {

    beforeEach(function (): void {
        [$this->store, $this->token] = createAuthenticatedStore();
    });

    describe('POST /api/v1/webhooks', function (): void {

        it('configures webhook url and secret', function (): void {
            $this->withToken($this->token)
                ->postJson('/api/v1/webhooks', [
                    'webhook_url'    => 'https://example.com/webhook',
                    'webhook_secret' => 'my-super-secret-key-1234567890',
                ])
                ->assertStatus(200)
                ->assertJsonPath('webhook_url', 'https://example.com/webhook');

            $this->assertDatabaseHas('stores', [
                'id'          => $this->store->id,
                'webhook_url' => 'https://example.com/webhook',
            ]);
        });

        it('rejects invalid url', function (): void {
            $this->withToken($this->token)
                ->postJson('/api/v1/webhooks', [
                    'webhook_url'    => 'not-a-url',
                    'webhook_secret' => 'my-super-secret-key-1234567890',
                ])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['webhook_url']);
        });

        it('rejects secret shorter than 16 chars', function (): void {
            $this->withToken($this->token)
                ->postJson('/api/v1/webhooks', [
                    'webhook_url'    => 'https://example.com/webhook',
                    'webhook_secret' => 'short',
                ])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['webhook_secret']);
        });

        it('returns 401 without authentication', function (): void {
            $this->postJson('/api/v1/webhooks', [])->assertStatus(401);
        });
    });

    describe('DELETE /api/v1/webhooks', function (): void {

        it('removes webhook configuration', function (): void {
            $store = Store::factory()->withWebhook()->create();
            $token = $store->createToken('test')->plainTextToken;

            $this->withToken($token)
                ->deleteJson('/api/v1/webhooks')
                ->assertStatus(200)
                ->assertJson(['message' => 'Webhook removido com sucesso.']);

            $this->assertDatabaseHas('stores', [
                'id'          => $store->id,
                'webhook_url' => null,
            ]);
        });

        it('returns 401 without authentication', function (): void {
            $this->deleteJson('/api/v1/webhooks')->assertStatus(401);
        });
    });
});
