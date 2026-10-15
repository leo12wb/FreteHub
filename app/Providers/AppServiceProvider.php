<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Shipping\Events\TrackingUpdated;
use App\Listeners\SendWebhookOnTrackingUpdated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(TrackingUpdated::class, SendWebhookOnTrackingUpdated::class);
    }
}
