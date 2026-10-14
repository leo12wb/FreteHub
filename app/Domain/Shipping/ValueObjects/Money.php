<?php

declare(strict_types=1);

namespace App\Domain\Shipping\ValueObjects;

use InvalidArgumentException;

final readonly class Money
{
    public int $amountInCents;

    public function __construct(int $amountInCents)
    {
        if ($amountInCents < 0) {
            throw new InvalidArgumentException("Valor monetário não pode ser negativo. Recebido: {$amountInCents} centavos.");
        }

        $this->amountInCents = $amountInCents;
    }

    public static function fromFloat(float $amount): self
    {
        return new self((int) round($amount * 100));
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public function toFloat(): float
    {
        return round($this->amountInCents / 100, 2);
    }

    public function formatted(): string
    {
        return 'R$ ' . number_format($this->toFloat(), 2, ',', '.');
    }

    public function add(self $other): self
    {
        return new self($this->amountInCents + $other->amountInCents);
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->amountInCents > $other->amountInCents;
    }

    public function equals(self $other): bool
    {
        return $this->amountInCents === $other->amountInCents;
    }

    public function __toString(): string
    {
        return $this->formatted();
    }
}
