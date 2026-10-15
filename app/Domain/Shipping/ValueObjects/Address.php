<?php

declare(strict_types=1);

namespace App\Domain\Shipping\ValueObjects;

use App\Domain\Shipping\Exceptions\InvalidZipcodeException;

final readonly class Address
{
    public string $zipcode;

    public function __construct(string $zipcode)
    {
        $normalized = preg_replace('/\D/', '', $zipcode);

        if ($normalized === null || strlen($normalized) !== 8) {
            throw new InvalidZipcodeException("CEP inválido: \"{$zipcode}\". Formato esperado: 00000-000 ou 00000000.");
        }

        $this->zipcode = $normalized;
    }

    public function formatted(): string
    {
        return substr($this->zipcode, 0, 5).'-'.substr($this->zipcode, 5, 3);
    }

    public function equals(self $other): bool
    {
        return $this->zipcode === $other->zipcode;
    }

    public function __toString(): string
    {
        return $this->formatted();
    }
}
