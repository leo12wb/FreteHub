<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\UseCases\ConfirmShipment\ConfirmShipmentDTO;
use App\Application\UseCases\ConfirmShipment\ConfirmShipmentUseCase;
use App\Application\UseCases\GetTracking\GetTrackingUseCase;
use App\Http\Requests\ConfirmShipmentRequest;
use App\Http\Resources\ShipmentResource;
use App\Http\Resources\TrackingEventResource;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function __construct(
        private readonly ConfirmShipmentUseCase $confirmShipment,
        private readonly GetTrackingUseCase $getTracking,
    ) {}

    /**
     * @OA\Post(
     *     path="/shipments",
     *     tags={"Shipments"},
     *     summary="Confirmar envio a partir de uma cotação",
     *     description="Seleciona uma opção da cotação, gera código de rastreamento e cria o envio.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"quote_id","carrier","service"},
     *
     *             @OA\Property(property="quote_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *             @OA\Property(property="carrier",  type="string", example="Correios"),
     *             @OA\Property(property="service",  type="string", example="PAC")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Envio criado",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id",            type="string", format="uuid"),
     *                 @OA\Property(property="quote_id",      type="string", format="uuid"),
     *                 @OA\Property(property="carrier",       type="string", example="Correios"),
     *                 @OA\Property(property="service",       type="string", example="PAC"),
     *                 @OA\Property(property="tracking_code", type="string", example="BR1A2B3C4D5EBR"),
     *                 @OA\Property(property="status",        type="string", example="created"),
     *                 @OA\Property(property="created_at",    type="string", format="date-time")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=404, description="Cotação não encontrada"),
     *     @OA\Response(response=422, description="Cotação expirada ou opção inválida")
     * )
     */
    public function store(ConfirmShipmentRequest $request): JsonResponse
    {
        /** @var Store $store */
        $store = $request->user();

        $dto = new ConfirmShipmentDTO(
            quoteId: $request->string('quote_id')->toString(),
            carrier: $request->string('carrier')->toString(),
            service: $request->string('service')->toString(),
            storeId: (string) $store->id,
        );

        $response = $this->confirmShipment->execute($dto);

        return (new ShipmentResource($response->shipment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/shipments/{shipment}/tracking",
     *     tags={"Shipments"},
     *     summary="Consultar rastreamento de um envio",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="shipment",
     *         in="path",
     *         required=true,
     *         description="UUID do envio",
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Dados de rastreamento",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="shipment", type="object",
     *                 @OA\Property(property="id",            type="string", format="uuid"),
     *                 @OA\Property(property="tracking_code", type="string"),
     *                 @OA\Property(property="status",        type="string", example="in_transit")
     *             ),
     *             @OA\Property(property="events", type="array",
     *
     *                 @OA\Items(
     *
     *                     @OA\Property(property="status",      type="string", example="in_transit"),
     *                     @OA\Property(property="description", type="string", example="Objeto em trânsito"),
     *                     @OA\Property(property="location",    type="string", example="São Paulo, SP"),
     *                     @OA\Property(property="occurred_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=404, description="Envio não encontrado")
     * )
     */
    public function tracking(Request $request, string $shipmentId): JsonResponse
    {
        /** @var Store $store */
        $store = $request->user();

        $result = $this->getTracking->execute($shipmentId, (string) $store->id);

        return response()->json([
            'shipment' => new ShipmentResource($result['shipment']),
            'events' => TrackingEventResource::collection($result['events']),
        ]);
    }
}
