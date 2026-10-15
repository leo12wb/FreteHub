<?php

declare(strict_types=1);

namespace App\Application\UseCases\CreateQuote;

use App\Domain\Shipping\Contracts\CarrierGateway;
use App\Domain\Shipping\Contracts\QuoteRepository;
use App\Domain\Shipping\Entities\Quote;
use App\Domain\Shipping\Enums\QuoteStatus;
use App\Domain\Shipping\Events\QuoteCreated;
use App\Domain\Shipping\Exceptions\CarrierUnavailableException;
use App\Domain\Shipping\ValueObjects\Address;
use App\Domain\Shipping\ValueObjects\CarrierRateResult;
use App\Domain\Shipping\ValueObjects\Dimensions;
use App\Domain\Shipping\ValueObjects\Money;
use App\Domain\Shipping\ValueObjects\Weight;
use DateTimeImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class CreateQuoteUseCase
{
    private const CACHE_TTL_SECONDS = 3600;

    /** @param iterable<CarrierGateway> $carriers */
    public function __construct(
        private readonly QuoteRepository $quoteRepository,
        private readonly Dispatcher      $dispatcher,
        private readonly iterable        $carriers,
    ) {}

    public function execute(CreateQuoteDTO $dto): CreateQuoteResponse
    {
        $origin      = new Address($dto->originZipcode);
        $destination = new Address($dto->destinationZipcode);
        $weight      = new Weight($dto->weightKg);
        $dimensions  = new Dimensions($dto->lengthCm, $dto->widthCm, $dto->heightCm);

        $cached = Cache::get($dto->cacheKey());

        if ($cached !== null) {
            /** @var Quote $cachedQuote */
            $cachedQuote = $cached;

            return new CreateQuoteResponse(quote: $cachedQuote, fromCache: true);
        }

        $options = $this->fetchRates($origin, $destination, $weight, $dimensions);

        $quote = new Quote(
            id:            (string) Str::uuid(),
            storeId:       $dto->storeId,
            origin:        $origin,
            destination:   $destination,
            weight:        $weight,
            dimensions:    $dimensions,
            declaredValue: Money::fromCents($dto->declaredValueCents),
            status:        QuoteStatus::Pending,
            expiresAt:     new DateTimeImmutable('+1 hour'),
            options:       $options,
        );

        $this->quoteRepository->save($quote);
        Cache::put($dto->cacheKey(), $quote, self::CACHE_TTL_SECONDS);
        $this->dispatcher->dispatch(new QuoteCreated($quote));

        return new CreateQuoteResponse(quote: $quote, fromCache: false);
    }

    /** @return CarrierRateResult[] */
    private function fetchRates(
        Address    $origin,
        Address    $destination,
        Weight     $weight,
        Dimensions $dimensions,
    ): array {
        $options = [];

        foreach ($this->carriers as $carrier) {
            try {
                $options[] = $carrier->calculateRate($origin, $destination, $weight, $dimensions);
            } catch (CarrierUnavailableException) {
                // carrier indisponível — seguimos sem ele
            }
        }

        return $options;
    }
}
