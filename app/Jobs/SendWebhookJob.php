<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Store;
use App\Models\WebhookLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

final class SendWebhookJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $backoff = 60;

    /** @param array<string, mixed> $payload */
    public function __construct(
        public readonly int   $storeId,
        public readonly string $shipmentId,
        public readonly array  $payload,
    ) {}

    public function handle(): void
    {
        $store = Store::find($this->storeId);

        if ($store === null || $store->webhook_url === null) {
            return;
        }

        $body      = json_encode($this->payload) ?: '';
        $signature = hash_hmac('sha256', $body, (string) $store->webhook_secret);

        $attempt        = $this->attempts();
        $responseStatus = null;
        $success        = false;

        try {
            $response = Http::withHeaders([
                'Content-Type'         => 'application/json',
                'X-FreteHub-Signature' => "sha256={$signature}",
            ])
                ->timeout(10)
                ->post((string) $store->webhook_url, $this->payload);

            $responseStatus = $response->status();
            $success        = $response->successful();

            if (! $success) {
                $response->throw();
            }
        } catch (RequestException $e) {
            $responseStatus = $e->response->status();
        } finally {
            WebhookLog::create([
                'store_id'        => $this->storeId,
                'shipment_id'     => $this->shipmentId,
                'payload'         => $this->payload,
                'response_status' => $responseStatus,
                'attempt'         => $attempt,
                'success'         => $success,
            ]);
        }

        if (! $success) {
            $this->release($this->backoff);
        }
    }
}
