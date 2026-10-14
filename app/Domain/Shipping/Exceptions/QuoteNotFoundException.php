<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Exceptions;

use RuntimeException;

final class QuoteNotFoundException extends RuntimeException
{
    public function __construct(string $quoteId)
    {
        parent::__construct("Cotação \"{$quoteId}\" não encontrada.");
    }
}
