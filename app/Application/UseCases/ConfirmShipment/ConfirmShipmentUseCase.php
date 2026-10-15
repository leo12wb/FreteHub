<?php

declare(strict_types=1);

namespace App\Application\UseCases\ConfirmShipment;

use App\Domain\Shipping\Contracts\QuoteRepository;
use App\Domain\Shipping\Contracts\ShipmentRepository;
use App\Domain\Shipping\Entities\Shipment;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Events\ShipmentCreated;
use App\Domain\Shipping\Exceptions\CarrierUnavailableException;
use App\Domain\Shipping\Exceptions\QuoteExpiredException;
use App\Domain\Shipping\Exceptions\QuoteNotFoundException;
use DateTimeImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;

final class ConfirmShipmentUseCase
{
    public function __construct(
        private readonly QuoteRepository $quoteRepository,
        private readonly ShipmentRepository $shipmentRepository,
        private readonly Dispatcher $dispatcher,
    ) {}

    public function execute(ConfirmShipmentDTO $dto): ConfirmShipmentResponse
    {
        $quote = $this->quoteRepository->findByIdAndStore($dto->quoteId, $dto->storeId);

        if ($quote === null) {
            throw new QuoteNotFoundException("Cotação \"{$dto->quoteId}\" não encontrada.");
        }

        if ($quote->isExpired()) {
            throw new QuoteExpiredException($dto->quoteId);
        }

        $option = $quote->findOption($dto->carrier, $dto->service);

        if ($option === null) {
            throw new CarrierUnavailableException(
                "Opção {$dto->carrier}/{$dto->service} não disponível nesta cotação."
            );
        }

        $shipment = new Shipment(
            id: (string) Str::uuid(),
            quoteId: $quote->id,
            storeId: $quote->storeId,
            carrier: $option->carrier,
            service: $option->service,
            trackingCode: $this->generateTrackingCode($option->carrier),
            status: ShipmentStatus::Created,
            labelUrl: null,
            createdAt: new DateTimeImmutable(),
        );

        $confirmedQuote = $quote->confirm();
        $this->quoteRepository->save($confirmedQuote);
        $this->shipmentRepository->save($shipment);
        $this->dispatcher->dispatch(new ShipmentCreated($shipment));

        return new ConfirmShipmentResponse(shipment: $shipment);
    }

    private function generateTrackingCode(string $carrier): string
    {
        $prefix = match (strtolower($carrier)) {
            'correios' => 'BR',
            'jadlog' => 'JD',
            'totalexpress' => 'TX',
            default => strtoupper(substr($carrier, 0, 2)),
        };

        return $prefix.strtoupper(Str::random(10)).'BR';
    }
}
