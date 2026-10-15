<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — FreteHub v1
|--------------------------------------------------------------------------
*/

Route::get('/health', [HealthController::class, 'index']);

Route::prefix('v1')->group(function (): void {

    // Auth (public)
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Authenticated endpoints — rate limit: 60 req/min
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function (): void {

        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Quotes
        Route::post('/quotes', [QuoteController::class, 'store']);

        // Shipments
        Route::post('/shipments', [ShipmentController::class, 'store']);
        Route::get('/shipments/{shipment}/tracking', [ShipmentController::class, 'tracking']);

        // Webhook config
        Route::post('/webhooks', [WebhookController::class, 'store']);
        Route::delete('/webhooks', [WebhookController::class, 'destroy']);
    });
});
