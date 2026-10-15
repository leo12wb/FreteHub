<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Shipping\Contracts\QuoteRepository;
use App\Domain\Shipping\Entities\Quote;
use App\Domain\Shipping\Enums\QuoteStatus;
use App\Domain\Shipping\ValueObjects\Address;
use App\Domain\Shipping\ValueObjects\CarrierRateResult;
use App\Domain\Shipping\ValueObjects\Dimensions;
use App\Domain\Shipping\ValueObjects\Money;
use App\Domain\Shipping\ValueObjects\Weight;
use App\Models\Quote as QuoteModel;
use App\Models\QuoteOption;
use DateTimeImmutable;

final class EloquentQuoteRepository implements QuoteRepository
{
    public function save(Quote $quote): void
    {
        $model = QuoteModel::findOrNew($quote->id);
        $model->id = $quote->id;
        $model->fill([
            'store_id' => $quote->storeId,
            'origin_zipcode' => $quote->origin->zipcode,
            'destination_zipcode' => $quote->destination->zipcode,
            'weight_kg' => $quote->weight->kg,
            'dimensions' => $quote->dimensions->toArray(),
            'declared_value_cents' => $quote->declaredValue->amountInCents,
            'status' => $quote->status->value,
            'expires_at' => $quote->expiresAt->format('Y-m-d H:i:s'),
        ]);
        $model->save();

        $model->options()->delete();

        foreach ($quote->options as $option) {
            QuoteOption::create([
                'quote_id' => $quote->id,
                'carrier' => $option->carrier,
                'service' => $option->service,
                'price_cents' => $option->price->amountInCents,
                'delivery_days' => $option->deliveryDays,
            ]);
        }
    }

    public function findById(string $id): ?Quote
    {
        $model = QuoteModel::with('options')->find($id);

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findByIdAndStore(string $id, string $storeId): ?Quote
    {
        $model = QuoteModel::with('options')
            ->where('id', $id)
            ->where('store_id', $storeId)
            ->first();

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    /** @return Quote[] */
    public function findByStoreId(string $storeId): array
    {
        return QuoteModel::with('options')
            ->where('store_id', $storeId)
            ->get()
            ->map(fn (QuoteModel $m) => $this->toDomain($m))
            ->all();
    }

    public function delete(string $id): void
    {
        QuoteModel::destroy($id);
    }

    private function toDomain(QuoteModel $model): Quote
    {
        /** @var array<string, float> $dims */
        $dims = json_decode((string) $model->getRawOriginal('dimensions'), true) ?? [];

        $options = $model->options
            ->map(fn (QuoteOption $opt) => new CarrierRateResult(
                carrier: $opt->carrier,
                service: $opt->service,
                price: Money::fromCents($opt->price_cents),
                deliveryDays: $opt->delivery_days,
            ))
            ->all();

        return new Quote(
            id: $model->id,
            storeId: (string) $model->store_id,
            origin: new Address((string) $model->origin_zipcode),
            destination: new Address((string) $model->destination_zipcode),
            weight: new Weight((float) $model->weight_kg),
            dimensions: new Dimensions(
                lengthCm: (float) ($dims['length_cm'] ?? 1),
                widthCm: (float) ($dims['width_cm'] ?? 1),
                heightCm: (float) ($dims['height_cm'] ?? 1),
            ),
            declaredValue: Money::fromCents((int) $model->declared_value_cents),
            status: QuoteStatus::from((string) $model->getRawOriginal('status')),
            expiresAt: new DateTimeImmutable((string) $model->getRawOriginal('expires_at')),
            options: $options,
        );
    }
}
