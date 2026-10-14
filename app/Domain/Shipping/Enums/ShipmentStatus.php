<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Enums;

enum ShipmentStatus: string
{
    case Created    = 'created';
    case InTransit  = 'in_transit';
    case Delivered  = 'delivered';
    case Failed     = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Created   => 'Criado',
            self::InTransit => 'Em trânsito',
            self::Delivered => 'Entregue',
            self::Failed    => 'Falhou',
        };
    }

    public function isTerminal(): bool
    {
        return $this === self::Delivered || $this === self::Failed;
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Created   => $next === self::InTransit || $next === self::Failed,
            self::InTransit => $next === self::Delivered || $next === self::Failed,
            self::Delivered,
            self::Failed    => false,
        };
    }
}
