<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\UseCases\CreateQuote\CreateQuoteDTO;
use App\Application\UseCases\CreateQuote\CreateQuoteUseCase;
use App\Http\Requests\CreateQuoteRequest;
use App\Http\Resources\QuoteResource;
use App\Models\Store;
use Illuminate\Http\JsonResponse;

class QuoteController extends Controller
{
    public function __construct(
        private readonly CreateQuoteUseCase $createQuote,
    ) {}

    public function store(CreateQuoteRequest $request): JsonResponse
    {
        /** @var Store $store */
        $store = $request->user();

        $dto = new CreateQuoteDTO(
            storeId:            (string) $store->id,
            originZipcode:      $request->string('origin_zipcode')->toString(),
            destinationZipcode: $request->string('destination_zipcode')->toString(),
            weightKg:           (float) $request->input('weight_kg'),
            lengthCm:           (float) $request->input('length_cm'),
            widthCm:            (float) $request->input('width_cm'),
            heightCm:           (float) $request->input('height_cm'),
            declaredValueCents: (int) round((float) $request->input('declared_value') * 100),
        );

        $response = $this->createQuote->execute($dto);

        return (new QuoteResource($response->quote))
            ->response()
            ->setStatusCode(201)
            ->header('X-From-Cache', $response->fromCache ? 'true' : 'false');
    }
}
