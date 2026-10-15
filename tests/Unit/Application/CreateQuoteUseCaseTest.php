<?php

declare(strict_types=1);

use App\Application\UseCases\CreateQuote\CreateQuoteDTO;
use App\Application\UseCases\CreateQuote\CreateQuoteUseCase;
use App\Domain\Shipping\Contracts\CarrierGateway;
use App\Domain\Shipping\Contracts\QuoteRepository;
use App\Domain\Shipping\Entities\Quote;
use App\Domain\Shipping\Events\QuoteCreated;
use App\Domain\Shipping\Exceptions\CarrierUnavailableException;
use App\Domain\Shipping\ValueObjects\CarrierRateResult;
use App\Domain\Shipping\ValueObjects\Money;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Cache;

function makeCreateQuoteDTO(): CreateQuoteDTO
{
    return new CreateQuoteDTO(
        storeId: 'store-uuid',
        originZipcode: '01310100',
        destinationZipcode: '20040020',
        weightKg: 2.0,
        lengthCm: 20.0,
        widthCm: 15.0,
        heightCm: 10.0,
        declaredValueCents: 5000,
    );
}

function makeCarrierMock(string $carrier, string $service, int $priceCents, int $days): CarrierGateway
{
    $mock = Mockery::mock(CarrierGateway::class);
    $mock->shouldReceive('calculateRate')->andReturn(new CarrierRateResult(
        carrier: $carrier,
        service: $service,
        price: Money::fromCents($priceCents),
        deliveryDays: $days,
    ));

    return $mock;
}

describe('CreateQuoteUseCase', function (): void {

    beforeEach(function (): void {
        $this->repository = Mockery::mock(QuoteRepository::class);
        $this->dispatcher = Mockery::mock(Dispatcher::class);
        Cache::flush();
    });

    it('creates a quote with options from all carriers', function (): void {
        $carriers = [
            makeCarrierMock('Correios', 'PAC', 2000, 7),
            makeCarrierMock('Correios', 'SEDEX', 3500, 2),
        ];

        $this->repository->shouldReceive('save')->once();
        $this->dispatcher->shouldReceive('dispatch')->once()->with(Mockery::type(QuoteCreated::class));

        $useCase = new CreateQuoteUseCase($this->repository, $this->dispatcher, $carriers);
        $response = $useCase->execute(makeCreateQuoteDTO());

        expect($response->quote)->toBeInstanceOf(Quote::class)
            ->and($response->fromCache)->toBeFalse()
            ->and($response->quote->options)->toHaveCount(2);
    });

    it('skips unavailable carriers without failing', function (): void {
        $unavailable = Mockery::mock(CarrierGateway::class);
        $unavailable->shouldReceive('calculateRate')->andThrow(CarrierUnavailableException::class);

        $available = makeCarrierMock('Correios', 'PAC', 2000, 7);

        $this->repository->shouldReceive('save')->once();
        $this->dispatcher->shouldReceive('dispatch')->once();

        $useCase = new CreateQuoteUseCase($this->repository, $this->dispatcher, [$unavailable, $available]);
        $response = $useCase->execute(makeCreateQuoteDTO());

        expect($response->quote->options)->toHaveCount(1);
    });

    it('returns from cache on second call', function (): void {
        $carrier = makeCarrierMock('Correios', 'PAC', 2000, 7);

        $this->repository->shouldReceive('save')->once(); // only once
        $this->dispatcher->shouldReceive('dispatch')->once();

        $useCase = new CreateQuoteUseCase($this->repository, $this->dispatcher, [$carrier]);
        $dto = makeCreateQuoteDTO();

        $first = $useCase->execute($dto);
        $second = $useCase->execute($dto);

        expect($first->fromCache)->toBeFalse()
            ->and($second->fromCache)->toBeTrue();
    });

    it('dispatches QuoteCreated event', function (): void {
        $carrier = makeCarrierMock('Correios', 'PAC', 2000, 7);

        $this->repository->shouldReceive('save')->once();
        $this->dispatcher->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::on(fn ($event) => $event instanceof QuoteCreated));

        $useCase = new CreateQuoteUseCase($this->repository, $this->dispatcher, [$carrier]);
        $useCase->execute(makeCreateQuoteDTO());
    });
});
