<?php

declare(strict_types=1);

namespace App\Application\UseCases\CreateQuote;

final readonly class CreateQuoteDTO
{
    public function __construct(
        public string $storeId,
        public string $originZipcode,
        public string $destinationZipcode,
        public float $weightKg,
        public float $lengthCm,
        public float $widthCm,
        public float $heightCm,
        public int $declaredValueCents,
    ) {}

    public function cacheKey(): string
    {
        return 'quote:'.md5(implode('|', [
            $this->storeId,
            $this->originZipcode,
            $this->destinationZipcode,
            $this->weightKg,
            $this->lengthCm,
            $this->widthCm,
            $this->heightCm,
            $this->declaredValueCents,
        ]));
    }
}
