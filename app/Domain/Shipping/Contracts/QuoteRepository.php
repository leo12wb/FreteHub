<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Contracts;

use App\Domain\Shipping\Entities\Quote;

interface QuoteRepository
{
    public function save(Quote $quote): void;

    public function findById(string $id): ?Quote;

    public function findByIdAndStore(string $id, string $storeId): ?Quote;

    /** @return Quote[] */
    public function findByStoreId(string $storeId): array;

    public function delete(string $id): void;
}
