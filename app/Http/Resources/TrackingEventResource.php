<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\TrackingEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TrackingEvent */
class TrackingEventResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->status,
            'description' => $this->description,
            'location' => $this->location,
            'occurred_at' => $this->getRawOriginal('occurred_at'),
        ];
    }
}
