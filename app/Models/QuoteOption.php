<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\QuoteOptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteOption extends Model
{
    /** @use HasFactory<QuoteOptionFactory> */
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'carrier',
        'service',
        'price_cents',
        'delivery_days',
    ];

    protected function casts(): array
    {
        return [
            'price_cents'   => 'integer',
            'delivery_days' => 'integer',
        ];
    }

    public function priceAsFloat(): float
    {
        return round($this->price_cents / 100, 2);
    }

    /** @return BelongsTo<Quote, $this> */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
