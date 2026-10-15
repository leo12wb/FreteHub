<?php

declare(strict_types=1);

namespace App\Domain\Shipping\ValueObjects;

use App\Domain\Shipping\Exceptions\InvalidDimensionsException;

final readonly class Dimensions
{
    public float $lengthCm;

    public float $widthCm;

    public float $heightCm;

    public function __construct(float $lengthCm, float $widthCm, float $heightCm)
    {
        if ($lengthCm <= 0 || $widthCm <= 0 || $heightCm <= 0) {
            throw new InvalidDimensionsException(
                'Todas as dimensões devem ser maiores que zero. '.
                "Recebido: {$lengthCm}x{$widthCm}x{$heightCm}cm."
            );
        }

        $this->lengthCm = round($lengthCm, 1);
        $this->widthCm = round($widthCm, 1);
        $this->heightCm = round($heightCm, 1);
    }

    public function cubicCm(): float
    {
        return $this->lengthCm * $this->widthCm * $this->heightCm;
    }

    /** Peso cúbico em kg (fator padrão logístico: 6000 cm³/kg) */
    public function cubicWeightKg(int $factor = 6000): float
    {
        return round($this->cubicCm() / $factor, 3);
    }

    /** @return array<string, float> */
    public function toArray(): array
    {
        return [
            'length_cm' => $this->lengthCm,
            'width_cm' => $this->widthCm,
            'height_cm' => $this->heightCm,
        ];
    }

    public function __toString(): string
    {
        return "{$this->lengthCm}x{$this->widthCm}x{$this->heightCm}cm";
    }
}
