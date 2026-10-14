<?php

declare(strict_types=1);

namespace App\Domain\Shipping\ValueObjects;

use App\Domain\Shipping\Exceptions\InvalidWeightException;

final readonly class Weight
{
    public const MAX_KG = 30.0;

    public float $kg;

    public function __construct(float $kg)
    {
        if ($kg <= 0) {
            throw new InvalidWeightException("Peso deve ser maior que zero. Recebido: {$kg}kg.");
        }

        if ($kg > self::MAX_KG) {
            throw new InvalidWeightException("Peso máximo permitido é " . self::MAX_KG . "kg. Recebido: {$kg}kg.");
        }

        $this->kg = round($kg, 3);
    }

    public function inGrams(): int
    {
        return (int) round($this->kg * 1000);
    }

    public function equals(self $other): bool
    {
        return $this->kg === $other->kg;
    }

    public function __toString(): string
    {
        return "{$this->kg}kg";
    }
}
