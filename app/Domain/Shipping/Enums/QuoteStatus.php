<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Enums;

enum QuoteStatus: string
{
    case Pending   = 'pending';
    case Confirmed = 'confirmed';
    case Expired   = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Aguardando confirmação',
            self::Confirmed => 'Confirmada',
            self::Expired   => 'Expirada',
        };
    }

    public function isTerminal(): bool
    {
        return $this === self::Confirmed || $this === self::Expired;
    }
}
