<?php

declare(strict_types=1);

namespace App\Application\UseCases\CreateQuote;

use App\Domain\Shipping\Entities\Quote;

final readonly class CreateQuoteResponse
{
    public function __construct(
        public Quote $quote,
        public bool $fromCache,
    ) {}
}
