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

    /**
     * @OA\Post(
     *     path="/quotes",
     *     tags={"Quotes"},
     *     summary="Criar cotação de frete",
     *     description="Consulta todas as transportadoras e retorna opções de frete ordenadas por preço. Resultados são cacheados por 1 hora (verificar header X-From-Cache).",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"origin_zipcode","destination_zipcode","weight_kg","length_cm","width_cm","height_cm","declared_value"},
     *
     *             @OA\Property(property="origin_zipcode",      type="string",  example="01310-100", description="CEP de origem"),
     *             @OA\Property(property="destination_zipcode", type="string",  example="20040-020", description="CEP de destino"),
     *             @OA\Property(property="weight_kg",           type="number",  format="float", example=2.5, description="Peso em kg (máx 30)"),
     *             @OA\Property(property="length_cm",           type="number",  format="float", example=30.0),
     *             @OA\Property(property="width_cm",            type="number",  format="float", example=20.0),
     *             @OA\Property(property="height_cm",           type="number",  format="float", example=15.0),
     *             @OA\Property(property="declared_value",      type="number",  format="float", example=150.00, description="Valor declarado em R$")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Cotação criada",
     *         headers={
     *
     *             @OA\Header(header="X-From-Cache", description="true se resultado veio do cache Redis", @OA\Schema(type="string", enum={"true","false"}))
     *         },
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id",                  type="string", format="uuid"),
     *                 @OA\Property(property="status",              type="string", example="pending"),
     *                 @OA\Property(property="expires_at",          type="string", format="date-time"),
     *                 @OA\Property(property="origin_zipcode",      type="string", example="01310-100"),
     *                 @OA\Property(property="destination_zipcode", type="string", example="20040-020"),
     *                 @OA\Property(property="options", type="array",
     *
     *                     @OA\Items(
     *
     *                         @OA\Property(property="carrier",       type="string", example="Correios"),
     *                         @OA\Property(property="service",       type="string", example="PAC"),
     *                         @OA\Property(property="price",         type="number", example=18.50),
     *                         @OA\Property(property="delivery_days", type="integer", example=7)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(CreateQuoteRequest $request): JsonResponse
    {
        /** @var Store $store */
        $store = $request->user();

        $dto = new CreateQuoteDTO(
            storeId: (string) $store->id,
            originZipcode: $request->string('origin_zipcode')->toString(),
            destinationZipcode: $request->string('destination_zipcode')->toString(),
            weightKg: (float) $request->input('weight_kg'),
            lengthCm: (float) $request->input('length_cm'),
            widthCm: (float) $request->input('width_cm'),
            heightCm: (float) $request->input('height_cm'),
            declaredValueCents: (int) round((float) $request->input('declared_value') * 100),
        );

        $response = $this->createQuote->execute($dto);

        return (new QuoteResource($response->quote))
            ->response()
            ->setStatusCode(201)
            ->header('X-From-Cache', $response->fromCache ? 'true' : 'false');
    }
}
