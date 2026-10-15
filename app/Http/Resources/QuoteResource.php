<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Shipping\Entities\Quote;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Quote */
class QuoteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->storeId,
            'origin_zipcode' => $this->origin->formatted(),
            'destination_zipcode' => $this->destination->formatted(),
            'weight_kg' => $this->weight->kg,
            'dimensions' => $this->dimensions->toArray(),
            'declared_value' => $this->declaredValue->toFloat(),
            'status' => $this->status->value,
            'expires_at' => $this->expiresAt->format('Y-m-d\TH:i:s\Z'),
            'options' => array_map(
                fn ($opt) => [
                    'carrier' => $opt->carrier,
                    'service' => $opt->service,
                    'price' => $opt->price->toFloat(),
                    'delivery_days' => $opt->deliveryDays,
                ],
                $this->optionsSortedByPrice(),
            ),
        ];
    }
}
