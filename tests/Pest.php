<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Custom Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeUuid', function () {
    return $this->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});

expect()->extend('toBeValidTrackingCode', function () {
    return $this->toMatch('/^[A-Z]{2}\d{8}[A-Z]{2}$/');
});

/*
|--------------------------------------------------------------------------
| Global Helpers
|--------------------------------------------------------------------------
*/

function createAuthenticatedStore(array $attributes = []): array
{
    $store = \App\Models\Store::factory()->create($attributes);
    $token = $store->createToken('test-token')->plainTextToken;

    return [$store, $token];
}
