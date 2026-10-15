<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Shipping\Events\TrackingUpdated;
use App\Jobs\SendWebhookJob;
use App\Models\Store;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendWebhookOnTrackingUpdated implements ShouldQueue
{
    public function handle(TrackingUpdated $event): void
    {
        $store = Store::find($event->shipment->storeId);

        if ($store === null || $store->webhook_url === null) {
            return;
        }

        $payload = [
            'event'          => 'tracking.updated',
            'shipment_id'    => $event->shipment->id,
            'tracking_code'  => $event->shipment->trackingCode,
            'carrier'        => $event->shipment->carrier,
            'service'        => $event->shipment->service,
            'status'         => $event->shipment->status->value,
            'previous_status'=> $event->previousStatus->value,
            'description'    => $event->description,
            'location'       => $event->location,
            'occurred_at'    => now()->toIso8601String(),
        ];

        SendWebhookJob::dispatch(
            storeId:    (int) $event->shipment->storeId,
            shipmentId: $event->shipment->id,
            payload:    $payload,
        );
    }
}
