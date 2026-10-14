<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Shipping\Enums\QuoteStatus;
use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quote extends Model
{
    /** @use HasFactory<QuoteFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'origin_zipcode',
        'destination_zipcode',
        'weight_kg',
        'dimensions',
        'declared_value_cents',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'dimensions'  => 'array',
            'status'      => QuoteStatus::class,
            'expires_at'  => 'datetime',
            'weight_kg'   => 'float',
        ];
    }

    /** @return BelongsTo<Store, $this> */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /** @return HasMany<QuoteOption, $this> */
    public function options(): HasMany
    {
        return $this->hasMany(QuoteOption::class);
    }

    /** @return HasOne<Shipment, $this> */
    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }
}
