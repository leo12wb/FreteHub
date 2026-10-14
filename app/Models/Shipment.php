<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Shipping\Enums\ShipmentStatus;
use Database\Factories\ShipmentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    /** @use HasFactory<ShipmentFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'quote_id',
        'store_id',
        'carrier',
        'service',
        'tracking_code',
        'status',
        'label_url',
    ];

    protected function casts(): array
    {
        return [
            'status' => ShipmentStatus::class,
        ];
    }

    /** @return BelongsTo<Store, $this> */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /** @return BelongsTo<Quote, $this> */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /** @return HasMany<TrackingEvent, $this> */
    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class)->orderBy('occurred_at');
    }

    /** @return HasMany<WebhookLog, $this> */
    public function webhookLogs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }
}
