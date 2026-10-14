<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Events;

use App\Domain\Shipping\Entities\Quote;

final readonly class QuoteCreated
{
    public function __construct(
        public Quote $quote,
    ) {}
}
