<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Shipping\Contracts\CarrierGateway;
use App\Domain\Shipping\Contracts\QuoteRepository;
use App\Domain\Shipping\Contracts\ShipmentRepository;
use App\Infrastructure\Carriers\CorreiosPacGateway;
use App\Infrastructure\Carriers\CorreiosSedexGateway;
use App\Infrastructure\Carriers\JadLogGateway;
use App\Infrastructure\Carriers\TotalExpressGateway;
use App\Infrastructure\Persistence\EloquentQuoteRepository;
use App\Infrastructure\Persistence\EloquentShipmentRepository;
use Illuminate\Support\ServiceProvider;

class ShippingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(QuoteRepository::class, EloquentQuoteRepository::class);
        $this->app->bind(ShipmentRepository::class, EloquentShipmentRepository::class);

        $this->app->tag([
            CorreiosPacGateway::class,
            CorreiosSedexGateway::class,
            JadLogGateway::class,
            TotalExpressGateway::class,
        ], CarrierGateway::class);

        $this->app->bind('carriers', fn ($app) => $app->tagged(CarrierGateway::class));
    }
}
