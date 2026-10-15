<?php

use App\Domain\Shipping\Exceptions\CarrierUnavailableException;
use App\Domain\Shipping\Exceptions\QuoteExpiredException;
use App\Domain\Shipping\Exceptions\QuoteNotFoundException;
use App\Domain\Shipping\Exceptions\ShipmentNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (QuoteNotFoundException $e): JsonResponse {
            return response()->json(['message' => $e->getMessage()], 404);
        });

        $exceptions->render(function (ShipmentNotFoundException $e): JsonResponse {
            return response()->json(['message' => $e->getMessage()], 404);
        });

        $exceptions->render(function (QuoteExpiredException $e): JsonResponse {
            return response()->json(['message' => $e->getMessage()], 422);
        });

        $exceptions->render(function (CarrierUnavailableException $e): JsonResponse {
            return response()->json(['message' => $e->getMessage()], 422);
        });
    })->create();
