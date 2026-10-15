<?php

declare(strict_types=1);

use App\Domain\Shipping\Entities\Quote;
use App\Domain\Shipping\Enums\QuoteStatus;
use App\Domain\Shipping\ValueObjects\Address;
use App\Domain\Shipping\ValueObjects\CarrierRateResult;
use App\Domain\Shipping\ValueObjects\Dimensions;
use App\Domain\Shipping\ValueObjects\Money;
use App\Domain\Shipping\ValueObjects\Weight;
use DateTimeImmutable;

function makeQuote(QuoteStatus $status = QuoteStatus::Pending, ?DateTimeImmutable $expiresAt = null, array $options = []): Quote
{
    return new Quote(
        id: 'quote-uuid',
        storeId: 'store-uuid',
        origin: new Address('01310100'),
        destination: new Address('20040020'),
        weight: new Weight(2.0),
        dimensions: new Dimensions(20, 15, 10),
        declaredValue: Money::fromCents(5000),
        status: $status,
        expiresAt: $expiresAt ?? new DateTimeImmutable('+1 hour'),
        options: $options,
    );
}

function makeOption(string $carrier, string $service, int $priceCents, int $days): CarrierRateResult
{
    return new CarrierRateResult(
        carrier: $carrier,
        service: $service,
        price: Money::fromCents($priceCents),
        deliveryDays: $days,
    );
}

describe('Quote', function (): void {

    it('is not expired when expires_at is in the future', function (): void {
        $quote = makeQuote(expiresAt: new DateTimeImmutable('+1 hour'));

        expect($quote->isExpired())->toBeFalse();
    });

    it('is expired when expires_at is in the past', function (): void {
        $quote = makeQuote(expiresAt: new DateTimeImmutable('-1 second'));

        expect($quote->isExpired())->toBeTrue();
    });

    it('returns pending status correctly', function (): void {
        $quote = makeQuote(QuoteStatus::Pending);

        expect($quote->isPending())->toBeTrue()
            ->and($quote->isConfirmed())->toBeFalse();
    });

    it('confirm returns new quote with confirmed status', function (): void {
        $quote = makeQuote(QuoteStatus::Pending);
        $confirmed = $quote->confirm();

        expect($confirmed->status)->toBe(QuoteStatus::Confirmed)
            ->and($quote->status)->toBe(QuoteStatus::Pending); // original unchanged
    });

    it('expire returns new quote with expired status', function (): void {
        $quote = makeQuote(QuoteStatus::Pending);
        $expired = $quote->expire();

        expect($expired->status)->toBe(QuoteStatus::Expired)
            ->and($quote->status)->toBe(QuoteStatus::Pending);
    });

    it('sorts options by price ascending', function (): void {
        $options = [
            makeOption('Correios', 'SEDEX', 3000, 2),
            makeOption('JadLog', 'Expresso', 1500, 5),
            makeOption('Correios', 'PAC', 2000, 7),
        ];

        $quote = makeQuote(options: $options);
        $sorted = $quote->optionsSortedByPrice();

        expect($sorted[0]->price->amountInCents)->toBe(1500)
            ->and($sorted[1]->price->amountInCents)->toBe(2000)
            ->and($sorted[2]->price->amountInCents)->toBe(3000);
    });

    it('finds option by carrier and service', function (): void {
        $options = [makeOption('Correios', 'PAC', 2000, 7)];
        $quote = makeQuote(options: $options);

        $found = $quote->findOption('Correios', 'PAC');

        expect($found)->not->toBeNull()
            ->and($found?->service)->toBe('PAC');
    });

    it('returns null when option not found', function (): void {
        $quote = makeQuote();

        expect($quote->findOption('Unknown', 'X'))->toBeNull();
    });
});
