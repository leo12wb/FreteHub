<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Exceptions;

use RuntimeException;

final class QuoteExpiredException extends RuntimeException
{
    public function __construct(string $quoteId)
    {
        parent::__construct("A cotação \"{$quoteId}\" expirou e não pode ser confirmada.");
    }
}
