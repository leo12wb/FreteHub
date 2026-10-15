<?php

declare(strict_types=1);

use App\Models\Store;

describe('Auth', function (): void {

    describe('register', function (): void {

        it('creates a store and returns a token', function (): void {
            $this->postJson('/api/v1/auth/register', [
                'name'                  => 'Loja Teste',
                'email'                 => 'loja@teste.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ])
                ->assertStatus(201)
                ->assertJsonStructure(['token', 'store' => ['id', 'name', 'email']]);

            $this->assertDatabaseHas('stores', ['email' => 'loja@teste.com']);
        });

        it('fails with duplicate email', function (): void {
            Store::factory()->create(['email' => 'duplicate@teste.com']);

            $this->postJson('/api/v1/auth/register', [
                'name'                  => 'Outra Loja',
                'email'                 => 'duplicate@teste.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ])->assertStatus(422);
        });

        it('fails without required fields', function (): void {
            $this->postJson('/api/v1/auth/register', [])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password']);
        });
    });

    describe('login', function (): void {

        it('returns token with valid credentials', function (): void {
            Store::factory()->create(['email' => 'login@teste.com']);

            $this->postJson('/api/v1/auth/login', [
                'email'    => 'login@teste.com',
                'password' => 'password',
            ])
                ->assertStatus(200)
                ->assertJsonStructure(['token']);
        });

        it('rejects invalid credentials', function (): void {
            Store::factory()->create(['email' => 'login@teste.com']);

            $this->postJson('/api/v1/auth/login', [
                'email'    => 'login@teste.com',
                'password' => 'wrong-password',
            ])->assertStatus(422);
        });
    });

    describe('logout', function (): void {

        it('revokes current token', function (): void {
            [$store, $token] = createAuthenticatedStore();

            $this->withToken($token)
                ->postJson('/api/v1/auth/logout')
                ->assertStatus(200)
                ->assertJson(['message' => 'Logout realizado com sucesso.']);
        });

        it('returns 401 without token', function (): void {
            $this->postJson('/api/v1/auth/logout')->assertStatus(401);
        });
    });
});
