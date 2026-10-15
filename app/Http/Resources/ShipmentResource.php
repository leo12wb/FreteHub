<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Shipping\Entities\Shipment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Shipment */
class ShipmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'quote_id'      => $this->quoteId,
            'store_id'      => $this->storeId,
            'carrier'       => $this->carrier,
            'service'       => $this->service,
            'tracking_code' => $this->trackingCode,
            'status'        => $this->status->value,
            'label_url'     => $this->labelUrl,
            'created_at'    => $this->createdAt->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
